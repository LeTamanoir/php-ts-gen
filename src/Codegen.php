<?php

declare(strict_types=1);

namespace Typographos;

use FilesystemIterator;
use InvalidArgumentException;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use ReflectionClass;
use ReflectionProperty;
use RuntimeException;
use SplFileInfo;
use Typographos\Attributes\TypeScript;
use Typographos\Dto\ArrayType;
use Typographos\Dto\RawType;
use Typographos\Dto\RecordType;
use Typographos\Dto\ReferenceType;
use Typographos\Dto\RenderCtx;
use Typographos\Dto\RootNamespaceType;
use Typographos\Dto\ScalarType;
use Typographos\Dto\UnionType;
use Typographos\Interfaces\TypeScriptType;

/**
 * @api
 */
final class Codegen
{
    public function __construct(
        private Config $config
    ) {}

    /**
     * Generate TypeScript types from the given class names
     * and write them to the file specified in the config
     *
     * @param  class-string[]  $classNames
     */
    public function generate(array $classNames = []): void
    {
        if ($this->config->autoDiscoverDirectory !== null) {
            $classNames = [...$classNames, ...$this->autoDiscoverClasses()];
        }

        $root = $this->parseClasses($classNames);

        $ctx = new RenderCtx(
            indent: $this->config->indent,
            depth: 0
        );

        $ts = $root->render($ctx);

        if (
            (file_exists($this->config->filePath) && ! is_writable($this->config->filePath)) ||
            @file_put_contents($this->config->filePath, $ts) === false
        ) {
            throw new RuntimeException('Failed to write generated types to file '.$this->config->filePath);
        }
    }

    /**
     * @return class-string[]
     */
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

    /**
     * Generate TypeScript types from the given class names
     *
     * @param  class-string[]  $classNames
     */
    public function parseClasses(array $classNames): RootNamespaceType
    {
        if (count($classNames) === 0) {
            throw new InvalidArgumentException('No classes to generate');
        }

        $queue = new Queue($classNames);
        $root = new RootNamespaceType;

        while ($className = $queue->shift()) {
            $namespace = preg_replace('/\\\\[^\\\\]+$/', '', $className);
            $root->addRecord($namespace, $this->parseClass($className, $queue));
        }

        return $root;
    }

    /**
     * @param  class-string  $className
     */
    private function parseClass(string $className, Queue $queue): RecordType
    {
        $ref = new ReflectionClass($className);

        $r = new RecordType($ref->getShortName());

        foreach ($ref->getProperties(ReflectionProperty::IS_PUBLIC) as $prop) {
            $type = (string) $prop->getType();
            $propName = $prop->getName();

            $req = $this->mapRequirements($type, $prop);
            $ts = $this->mapType($req, $queue);
            $r->addProperty($propName, $ts);
        }

        return $r;
    }

    private function applyRequirements(string $type, ReflectionProperty $prop): string
    {
        if ($type === 'array') {
            $declClass = $prop->getDeclaringClass();
            $errorLoc = 'for property $'.$prop->getName().' in '.$declClass->getFileName().':'.$declClass->getStartLine();

            if ($doc = $prop->getDocComment()) {
                if (! preg_match('/@var\s+([^*]+)/i', $doc, $m)) {
                    throw new InvalidArgumentException('Malformed PHPDoc ['.$doc.'] '.$errorLoc);
                }
            } elseif ($doc = $declClass->getConstructor()->getDocComment()) {
                if (! preg_match('/@param\s+([^\s*]+)\s+\$'.preg_quote($prop->getName()).'/i', $doc, $m)) {
                    throw new InvalidArgumentException('Malformed PHPDoc ['.$doc.'] '.$errorLoc);
                }
            } else {
                throw new InvalidArgumentException('Missing doc comment '.$errorLoc);
            }

            return trim($m[1]);
        }

        if ($type === 'self') {
            return $prop->getDeclaringClass()->getName();
        }

        if ($type === 'parent') {
            while ($type === 'parent') {
                $type = get_parent_class($prop->getDeclaringClass()->getName());
                if (! $type) {
                    throw new InvalidArgumentException('Parent class not found for '.$prop->getDeclaringClass()->getName());
                }
            }

            return $type;
        }

        return $type;
    }

    /**
     * Some PHP types require additional requirements before they can be mapped.
     */
    private function mapRequirements(string $type, ReflectionProperty $prop): string
    {
        // intersections are not supported
        if (str_contains($type, '&')) {
            throw new InvalidArgumentException('Intersection types are not supported');
        }

        // if nullable it can't be a union
        if (str_starts_with($type, '?')) {
            return '?'.$this->applyRequirements(substr($type, 1), $prop);
        }

        $types = Utils::splitTopLevel($type, '|');
        $parts = [];

        foreach ($types as $t) {
            $parts[] = $this->applyRequirements($t, $prop);
        }

        return implode('|', $parts);
    }

    /**
     * @internal
     */
    public function mapType(string $type, Queue $queue): TypeScriptType
    {
        $types = Utils::splitTopLevel($type, '|');
        $parts = [];

        if (count($types) === 0) {
            return ScalarType::unknown;
        }

        foreach ($types as $t) {
            $parts[] = $this->mapNamedType($t, $queue);
        }

        if (count($parts) === 1) {
            return $parts[0];
        }

        return new UnionType($parts);
    }

    private function mapNamedType(string $type, Queue $queue): TypeScriptType
    {
        if ($type === '') {
            return ScalarType::unknown;
        }

        $allowsNull = str_starts_with($type, '?');
        if ($allowsNull) {
            $type = substr($type, 1);
        }

        if (isset($this->config->typeReplacements[$type])) {
            $ts = new RawType($this->config->typeReplacements[$type]);

            if ($allowsNull) {
                return new UnionType([$ts, ScalarType::null]);
            }

            return $ts;
        }

        if (Utils::isBuiltinType($type)) {
            $ts = Utils::isArrayType($type)
                ? ArrayType::from($this, $type, $queue)
                : ScalarType::from($type);

            if ($allowsNull && $type !== 'null' && $type !== 'mixed') {
                return new UnionType([$ts, ScalarType::null]);
            }

            return $ts;
        }

        $userDefined = class_exists($type) && new ReflectionClass($type)->isUserDefined();

        if ($userDefined) {
            $queue->enqueue($type);
        }

        $ts = $userDefined ? new ReferenceType($type) : ScalarType::unknown;

        // nullable class type
        if ($allowsNull) {
            return new UnionType([$ts, ScalarType::null]);
        }

        return $ts;
    }
}
