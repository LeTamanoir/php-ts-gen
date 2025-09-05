<?php

declare(strict_types=1);

namespace Typographos;

use InvalidArgumentException;
use ReflectionException;
use RuntimeException;
use Typographos\Dto\GenCtx;
use Typographos\Dto\RenderCtx;
use Typographos\Dto\RootNamespaceType;

final class Generator
{
    /**
     * Indentation style
     */
    public string $indent = "\t";

    /**
     * Type replacements to replace PHP types with TypeScript types
     *
     * @var array<string, string>
     */
    public array $typeReplacements = [];

    /**
     * Directory to auto-discover classes from
     */
    public ?string $discoverDirectory = null;

    /**
     * File path to write the generated types to
     */
    public string $filePath = 'generated.d.ts';

    /**
     * Set the directory to auto-discover classes from
     */
    public function discoverFrom(string $directory): self
    {
        $this->discoverDirectory = $directory;

        return $this;
    }

    /**
     * Set the output file path
     */
    public function outputTo(string $filePath): self
    {
        $this->filePath = $filePath;

        return $this;
    }

    /**
     * Set the indentation style
     */
    public function withIndent(string $indent): self
    {
        $this->indent = $indent;

        return $this;
    }

    /**
     * Add a type replacement
     */
    public function withTypeReplacement(string $phpType, string $tsType): self
    {
        $this->typeReplacements[$phpType] = $tsType;

        return $this;
    }

    /**
     * Generate TypeScript types from the given class names
     * and write them to the file specified in the generator
     *
     * @param  array<class-string>  $classNames
     *
     * @throws InvalidArgumentException
     * @throws RuntimeException
     * @throws ReflectionException
     */
    public function generate(array $classNames = []): void
    {
        if ($this->discoverDirectory) {
            $classNames = array_unique(array_merge($classNames, ClassDiscovery::discover($this->discoverDirectory)));
        }

        if (count($classNames) === 0) {
            throw new InvalidArgumentException('No classes to generate');
        }

        $genCtx = new GenCtx(
            queue: new Queue($classNames),
            typeReplacements: $this->typeReplacements,
            parentProperty: null,
        );

        $root = RootNamespaceType::from($genCtx);

        $renderCtx = new RenderCtx(
            indent: $this->indent,
            depth: 0,
        );

        $ts = $root->render($renderCtx);

        if (file_exists($this->filePath) && ! is_writable($this->filePath) || ! file_put_contents($this->filePath, $ts)) {
            throw new RuntimeException('Failed to write generated types to file '.$this->filePath);
        }
    }
}
