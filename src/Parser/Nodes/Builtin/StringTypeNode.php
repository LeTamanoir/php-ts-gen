<?php

declare(strict_types=1);

namespace PhpTs\Parser\Nodes\Builtin;

use PhpTs\Parser\Nodes\TypeNode;

readonly class StringTypeNode extends TypeNode
{
    public function toTypeScript(?TypeNode $parent_type = null): string
    {
        return 'string';
    }
}
