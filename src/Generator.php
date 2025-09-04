<?php

declare(strict_types=1);

namespace Typographos;

use InvalidArgumentException;
use ReflectionClass;
use ReflectionProperty;
use Typographos\Dto\RecordType;
use Typographos\Dto\RenderCtx;
use Typographos\Dto\RootNamespaceType;

final class Generator
{
    private Config $config;

    public function __construct(null|Config $config = null)
    {
        $this->config = $config ?? new Config();
    }

    /**
     * Create a new Generator instance
     *
     * @api
     */
    public static function create(): self
    {
        return new self();
    }

    /**
     * Set the directory to auto-discover classes from
     *
     * @api
     */
    public function discoverFrom(string $directory): self
    {
        $this->config = $this->config->withAutoDiscoverDirectory($directory);

        return $this;
    }

    /**
     * Set the output file path
     *
     * @api
     */
    public function outputTo(string $filePath): self
    {
        $this->config = $this->config->withFilePath($filePath);

        return $this;
    }

    /**
     * Set the indentation style
     *
     * @api
     */
    public function withIndent(string $indent): self
    {
        $this->config = $this->config->withIndent($indent);

        return $this;
    }

    /**
     * Add a type replacement
     *
     * @api
     */
    public function withTypeReplacement(string $phpType, string $tsType): self
    {
        $this->config = $this->config->withTypeReplacement($phpType, $tsType);

        return $this;
    }

    /**
     * Generate TypeScript types from the given class names
     * and write them to the file specified in the config
     *
     * @param  array<class-string>  $classNames
     *
     * @api
     */
    public function generate(array $classNames = []): void
    {
        $classes = ClassDiscovery::findClasses($classNames, $this->config->autoDiscoverDirectory);
        $rootNamespace = $this->buildTypeHierarchy($classes);
        $typeScript = $this->renderTypeScript($rootNamespace);
        FileWriter::writeToFile($typeScript, $this->config->filePath);
    }

    /**
     * Build the namespace hierarchy from class names
     *
     * Processes classes in a queue, allowing new classes to be discovered
     * during conversion (e.g., when properties reference other DTOs).
     *
     * @param  array<class-string>  $classNames  Initial classes to process
     */
    private function buildTypeHierarchy(array $classNames): RootNamespaceType
    {
        if (count($classNames) === 0) {
            throw new InvalidArgumentException('No classes to generate');
        }

        $queue = new Queue($classNames);
        $root = new RootNamespaceType();

        // Process all classes in queue (queue may grow during processing)
        while (true) {
            $className = $queue->shift();
            if ($className === null) {
                break;
            }

            // Extract namespace: App\\DTO\\User → App\\DTO
            $namespace = preg_replace('/\\\\[^\\\\]+$/', '', $className);
            if ($namespace === null) {
                $namespace = '';
            }

            $record = $this->convertClassToRecord($className, $queue);
            $root->addRecord($namespace, $record);
        }

        return $root;
    }

    /**
     * Convert a PHP class to a TypeScript record type
     *
     * @param  class-string  $className
     */
    private function convertClassToRecord(string $className, Queue $queue): RecordType
    {
        $ref = new ReflectionClass($className);
        $record = new RecordType($ref->getShortName());

        foreach ($ref->getProperties(ReflectionProperty::IS_PUBLIC) as $prop) {
            $type = (string) $prop->getType();
            $propName = $prop->getName();

            $resolvedType = TypeResolver::resolveType($type, $prop);
            $tsType = TypeConverter::convertToTypeScript($resolvedType, $queue, $this->config->typeReplacements);
            $record->addProperty($propName, $tsType);
        }

        return $record;
    }

    /**
     * Render the TypeScript output
     */
    private function renderTypeScript(RootNamespaceType $root): string
    {
        $ctx = new RenderCtx(
            indent: $this->config->indent,
            depth: 0,
        );

        return $root->render($ctx);
    }
}
