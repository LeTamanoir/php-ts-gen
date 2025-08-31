<?php

declare(strict_types=1);

namespace PhpTs;

class Config
{
    public function __construct(
        public string $indent = "\t",
        public array $typeReplacements = [],
        public ?string $autoDiscoverDirectory = null,
        public string $filePath = 'test.d.ts',
    ) {}

    public function withIndent(string $indent): self
    {
        $this->indent = $indent;

        return $this;
    }

    public function withTypeReplacement(string $phpType, string $tsType): self
    {
        $this->typeReplacements[$phpType] = $tsType;

        return $this;
    }

    public function withFilePath(string $filePath): self
    {
        $this->filePath = $filePath;

        return $this;
    }

    public function withAutoDiscoverDirectory(?string $autoDiscoverDirectory): self
    {
        $this->autoDiscoverDirectory = $autoDiscoverDirectory;

        return $this;
    }
}
