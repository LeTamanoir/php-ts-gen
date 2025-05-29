<?php

declare(strict_types=1);

namespace PhpTs\Parser;

readonly class PropertyTypeNode extends TypeNode
{
    public function __construct(
        public string $name,
        public TypeNode $type,
    ) {}

    public function toTypeScript(ToTypeScriptContext $context): string
    {
        return sprintf(
            '%s%s: %s',
            str_repeat('   ', $context->depth),
            $this->name,
            $this->type->toTypeScript(new ToTypeScriptContext(
                parent_type: $this,
                depth: $context->depth,
            ))
        );
    }
}
