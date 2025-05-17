<?php

declare(strict_types=1);

namespace PhpTs\Parser\Nodes\Builtin;

use PhpTs\Parser\Nodes\TypeNode;

readonly class AnyTypeNode extends TypeNode
{
    public function toTypeScript(?TypeNode $parent_type = null): string
    {
        return 'any';
    }
}
