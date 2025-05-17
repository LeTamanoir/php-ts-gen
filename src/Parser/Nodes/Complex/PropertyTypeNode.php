<?php

declare(strict_types=1);

namespace PhpTs\Parser\Nodes\Complex;

use PhpTs\Parser\Nodes\TypeNode;

readonly class PropertyTypeNode extends TypeNode
{
    public function __construct(
        public string $name,
        public TypeNode $type,
    ) {}

    public function toTypeScript(?TypeNode $parent_type = null): string
    {
        return "{$this->name}: {$this->type->toTypeScript($this)}";
    }
}
