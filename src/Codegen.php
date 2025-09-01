<?php

declare(strict_types=1);

namespace Typographos;

use FilesystemIterator;
use InvalidArgumentException;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use ReflectionClass;
use ReflectionIntersectionType;
use ReflectionNamedType;
use ReflectionProperty;
use ReflectionType;
use ReflectionUnionType;
use RuntimeException;
use SplFileInfo;
use Typographos\Attributes\TypeScript;
use Typographos\Dto\ArrayType;
use Typographos\Dto\RawType;
use Typographos\Dto\RecordType;
use Typographos\Dto\ReferenceType;
use Typographos\Dto\RenderCtx;
use Typographos\Dto\ScalarType;
use Typographos\Dto\UnionType;
use Typographos\Interfaces\TypeScriptType;

class Codegen
{
    public function __construct(private Config $config) {}

    public function generate(string ...$classNames)
    {
        if ($this->config->autoDiscoverDirectory) {
            $classNames = [...$classNames, ...$this->autoDiscoverClasses()];
        }

        if (count($classNames) === 0) {
            throw new InvalidArgumentException('No classes to generate');
        }

        $queue = new Queue($classNames);
        $records = [];

        while ($className = $queue->shift()) {
            $records[] = self::generateTsRecord($className, $queue);
        }

        $nsTree = $this->groupByNamespace(...$records);

        $ctx = new RenderCtx(
            indent: $this->config->indent,
            depth: 0
        );

        $ts = $this->renderNamespaceTree($ctx, $nsTree);

        if (
            (file_exists($this->config->filePath) && ! is_writable($this->config->filePath)) ||
            @file_put_contents($this->config->filePath, $ts) === false
        ) {
            throw new RuntimeException('Failed to write generated types to file '.$this->config->filePath);
        }
    }

    private function autoDiscoverClasses(): array
    {
        $dir = $this->config->autoDiscoverDirectory;

        if (! $dir || ! is_dir($dir)) {
            throw new RuntimeException('Auto discover directory not found: '.$dir);
        }

        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($dir, FilesystemIterator::SKIP_DOTS)
        );
        /** @var SplFileInfo $file */
        foreach ($iterator as $file) {
            if ($file->isFile() && $file->getExtension() === 'php') {
                require_once $file->getPathname();
            }
        }

        $classes = [];
        foreach (get_declared_classes() as $class) {
            $ref = new ReflectionClass($class);
            if ($ref->getAttributes(TypeScript::class)) {
                $classes[] = $class;
            }
        }

        return $classes;
    }

    private function renderNamespaceTree(RenderCtx $ctx, array $tree): string
    {
        $ts = '';

        // stable order helps with deterministic diffs
        ksort($tree);

        foreach ($tree as $segment => $node) {
            $idt = str_repeat($ctx->indent, $ctx->depth);

            if ($node instanceof RecordType) {
                $ts .= $idt.'export interface '.$segment." {\n".$node->render($ctx->increaseDepth()).$idt."}\n";

                continue;
            }

            $ts .= $idt.($ctx->depth === 0 ? 'declare namespace ' : 'namespace ').Utils::tsIdent($segment)." {\n";
            $ts .= $this->renderNamespaceTree($ctx->increaseDepth(), $node);
            $ts .= $idt."}\n";
        }

        return $ts;
    }

    private function groupByNamespace(RecordType ...$records)
    {
        $namespaces = [];

        foreach ($records as $r) {
            $parts = array_filter(explode('\\', $r->name));

            $ptr = &$namespaces;
            foreach ($parts as $p) {
                $ptr[$p] ??= [];
                $ptr = &$ptr[$p];
            }
            $ptr = $r;
            unset($ptr); // safety if we forget to reassign in the future
        }

        return $namespaces;
    }

    private function generateTsRecord(string $className, Queue $queue)
    {
        $ref = new ReflectionClass($className);

        $ts = new RecordType($className);

        foreach ($ref->getProperties(ReflectionProperty::IS_PUBLIC) as $prop) {
            $type = $prop->getType();

            $propName = $prop->getName();

            if ($type === null) {
                $ts->addProperty($propName, ScalarType::unknown);

                continue;
            }

            $tsType = $this->mapType($type, $prop, $queue);
            $ts->addProperty($propName, $tsType);
        }

        return $ts;
    }

    private function mapType(ReflectionType $type, ReflectionProperty $prop, Queue $queue): TypeScriptType
    {
        if ($type instanceof ReflectionUnionType) {
            $parts = [];
            foreach ($type->getTypes() as $t) {
                $parts[] = $this->mapType($t, $prop, $queue);
            }

            return new UnionType($parts);
        }

        if ($type instanceof ReflectionIntersectionType) {
            throw new InvalidArgumentException('Intersection types are not supported');
        }

        assert($type instanceof ReflectionNamedType);

        $name = $type->getName();

        if (isset($this->config->typeReplacements[$name])) {
            return new RawType($this->config->typeReplacements[$name]);
        }

        if ($type->isBuiltin()) {
            if ($name === 'array') {
                return ArrayType::from($prop, $queue);
            }

            $ts = ScalarType::from($name);

            if ($type->allowsNull() && $name !== 'null' && $name !== 'mixed') {
                return new UnionType([$ts, ScalarType::null]);
            }

            return $ts;
        }

        // for class-like names we need to resolve self/parent if necessary
        if ($name === 'self') {
            $name = $prop->getDeclaringClass()->getName();
        } elseif ($name === 'parent') {
            while ($name === 'parent') {
                $parent = get_parent_class($prop->getDeclaringClass()->getName());
                $name = $parent ?: 'parent'; // fallback
            }
        }

        $userDefined = class_exists($name) && new ReflectionClass($name)->isUserDefined();

        if ($userDefined) {
            $queue->enqueue($name);
        }

        $ref = $userDefined ? new ReferenceType($name) : ScalarType::unknown;

        // nullable class type
        if ($type->allowsNull()) {
            return new UnionType([$ref, ScalarType::null]);
        }

        return $ref;
    }
}
