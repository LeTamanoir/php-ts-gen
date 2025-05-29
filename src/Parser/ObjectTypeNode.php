<?php

declare(strict_types=1);

namespace PhpTs\Parser;

readonly class ObjectTypeNode extends TypeNode
{
    /**
     * @param  list<PropertyTypeNode>  $properties
     */
    public function __construct(
        public string $name,
        public array $properties,
    ) {}

    public function toTypeScript(ToTypeScriptContext $context): string
    {
        $properties = array_map(
            fn (PropertyTypeNode $property) => $property->toTypeScript(new ToTypeScriptContext(
                parent_type: $this,
                depth: $context->depth + 1,
            )),
            $this->properties
        );

        $type_str = "{\n".implode(";\n", $properties)."\n}";

        if ($context->parent_type === null) {
            return 'type '.$this->name.' = '.$type_str.';';
        }

        return $type_str;
    }
}
