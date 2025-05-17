<?php

namespace PhpTs\Parser\Nodes\Complex;

use PhpTs\Parser\Nodes\TypeNode;

readonly class ReferenceTypeNode extends TypeNode
{
    public function __construct(public string $class_name) {}

    public function toTypeScript(?TypeNode $parent_type = null): string
    {
        return $this->class_name;
    }
}
