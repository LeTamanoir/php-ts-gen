<?php

namespace PhpTs\Parser;

readonly class ArrayTypeNode extends TypeNode
{
    public function __construct(
        public TypeNode $value_type,
    ) {}

    public function toTypeScript(ToTypeScriptContext $context): string
    {
        return sprintf('%s[]', $this->value_type->toTypeScript(new ToTypeScriptContext(
            parent_type: $this,
            depth: $context->depth,
        )));
    }
}
