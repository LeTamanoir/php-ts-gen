<?php

namespace PhpTs\Parser;

readonly class ReferenceTypeNode extends TypeNode
{
    public function __construct(public string $class_name) {}

    public function toTypeScript(ToTypeScriptContext $context): string
    {
        return $this->class_name;
    }
}
