<?php

declare(strict_types=1);

namespace PhpTs\Parser;

readonly class AnyTypeNode extends TypeNode
{
    public function toTypeScript(ToTypeScriptContext $context): string
    {
        return 'any';
    }
}
