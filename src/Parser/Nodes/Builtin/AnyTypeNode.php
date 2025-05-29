<?php

declare(strict_types=1);

namespace PhpTs\Parser\Nodes\Builtin;

use PhpTs\Parser\Nodes\ToTypeScriptContext;
use PhpTs\Parser\Nodes\TypeNode;

readonly class AnyTypeNode extends TypeNode
{
    public function toTypeScript(ToTypeScriptContext $context): string
    {
        return 'any';
    }
}
