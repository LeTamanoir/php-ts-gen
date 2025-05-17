<?php

declare(strict_types=1);

namespace PhpTs\Parser\Nodes\Complex;

use PhpTs\Parser\Nodes\TypeNode;

readonly class ObjectTypeNode extends TypeNode
{
    /**
     * @param  list<PropertyTypeNode>  $properties
     */
    public function __construct(
        public string $name,
        public array $properties,
    ) {}

    public function toTypeScript(?TypeNode $parent_type = null): string
    {
        $properties = array_map(
            fn (PropertyTypeNode $property) => $property->toTypeScript($this),
            $this->properties
        );

        $type_str = '{'.implode('; ', $properties).'}';

        if ($parent_type === null) {
            return 'type '.$this->name.' = '.$type_str.';';
        }

        return $type_str;
    }
}
