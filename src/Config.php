<?php

declare(strict_types=1);

namespace Typographos;

/**
 * @api
 */
final class Config
{
    /**
     * Create a new configuration with defaults.
     *
     * @api
     *
     * @param  array<string, string>  $typeReplacements
     */
    public function __construct(
        public string $indent = "\t",
        public array $typeReplacements = [],
        public ?string $autoDiscoverDirectory = null,
        public string $filePath = 'test.d.ts',
    ) {}

    /**
     * Set a custom indent for the generated code.
     *
     * @api
     */
    public function withIndent(string $indent): self
    {
        $this->indent = $indent;

        return $this;
    }

    /**
     * Transform the given PHP type to a custom TypeScript type.
     *
     * @api
     */
    public function withTypeReplacement(string $phpType, string $tsType): self
    {
        $this->typeReplacements[$phpType] = $tsType;

        return $this;
    }

    /**
     * Set the path to the file where the generated code will be written.
     *
     * @api
     */
    public function withFilePath(string $filePath): self
    {
        $this->filePath = $filePath;

        return $this;
    }

    /**
     * Set the directory to auto-discover classes from.
     *
     * @api
     */
    public function withAutoDiscoverDirectory(?string $autoDiscoverDirectory): self
    {
        $this->autoDiscoverDirectory = $autoDiscoverDirectory;

        return $this;
    }
}
