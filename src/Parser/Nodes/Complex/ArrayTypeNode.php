<?php

namespace PhpTs\Parser\Nodes\Complex;

use PhpTs\Parser\Nodes\TypeNode;

readonly class ArrayTypeNode extends TypeNode
{
    public function __construct(
        public TypeNode $value_type,
    ) {}

    public function toTypeScript(?TypeNode $parent_type = null): string
    {
        return "{$this->value_type->toTypeScript($this)}[]";
    }
}
