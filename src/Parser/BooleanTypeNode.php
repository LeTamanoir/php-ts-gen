<?php

declare(strict_types=1);

namespace PhpTs\Parser;

readonly class BooleanTypeNode extends TypeNode
{
    public function toTypeScript(ToTypeScriptContext $context): string
    {
        return 'boolean';
    }
}
