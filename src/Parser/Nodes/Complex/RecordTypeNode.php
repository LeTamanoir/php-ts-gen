<?php

declare(strict_types=1);

namespace PhpTs\Parser\Nodes\Complex;

use PhpTs\Parser\Nodes\TypeNode;

readonly class RecordTypeNode extends TypeNode
{
    public function __construct(
        public TypeNode $key_type,
        public TypeNode $value_type,
    ) {}

    public function toTypeScript(?TypeNode $parent_type = null): string
    {
        return "Record<{$this->key_type->toTypeScript($this)}, {$this->value_type->toTypeScript($this)}>";
    }
}
