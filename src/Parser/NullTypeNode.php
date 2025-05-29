<?php

declare(strict_types=1);

namespace PhpTs\Parser;

readonly class NullTypeNode extends TypeNode
{
    public function toTypeScript(ToTypeScriptContext $context): string
    {
        return 'null';
    }
}
