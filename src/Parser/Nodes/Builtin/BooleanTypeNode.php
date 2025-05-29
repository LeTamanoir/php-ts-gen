<?php

declare(strict_types=1);

namespace PhpTs\Parser\Nodes\Builtin;

use PhpTs\Parser\Nodes\ToTypeScriptContext;
use PhpTs\Parser\Nodes\TypeNode;

readonly class BooleanTypeNode extends TypeNode
{
    public function toTypeScript(ToTypeScriptContext $context): string
    {
        return 'boolean';
    }
}
