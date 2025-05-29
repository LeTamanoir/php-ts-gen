<?php

declare(strict_types=1);

namespace PhpTs\Parser;

readonly class RecordTypeNode extends TypeNode
{
    public function __construct(
        public TypeNode $key_type,
        public TypeNode $value_type,
    ) {}

    public function toTypeScript(ToTypeScriptContext $context): string
    {
        return sprintf(
            'Record<%s, %s>',
            $this->key_type->toTypeScript(new ToTypeScriptContext(
                parent_type: $this,
                depth: $context->depth,
            )),
            $this->value_type->toTypeScript(new ToTypeScriptContext(
                parent_type: $this,
                depth: $context->depth,
            ))
        );
    }
}
