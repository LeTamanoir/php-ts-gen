<?php

declare(strict_types=1);

namespace PhpTs\Parser;

readonly class StringTypeNode extends TypeNode
{
    public function toTypeScript(ToTypeScriptContext $context): string
    {
        return 'string';
    }
}
