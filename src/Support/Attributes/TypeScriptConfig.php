<?php

declare(strict_types=1);

namespace Typographos\Support\Attributes;

use Attribute;

#[Attribute(Attribute::TARGET_CLASS)]
final readonly class TypeScriptConfig
{
    public function __construct(
        public null|string $namespace = null,
        public null|string $outputPath = null,
        public array $typeReplacements = [],
    ) {}
}
