<?php

declare(strict_types=1);

namespace PhpTs\Parser;

readonly class NumberTypeNode extends TypeNode
{
    public function toTypeScript(ToTypeScriptContext $context): string
    {
        return 'number';
    }
}
