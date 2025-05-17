<?php

declare(strict_types=1);

namespace PhpTs\Parser\Nodes\Complex;

use PhpTs\Parser\Nodes\TypeNode;

readonly class UnionTypeNode extends TypeNode
{
    /**
     * @param  list<TypeNode>  $types
     */
    public function __construct(
        public readonly array $types,
    ) {}

    public function toTypeScript(?TypeNode $parent_type = null): string
    {
        $types = array_map(
            fn (TypeNode $type) => $type->toTypeScript($this),
            $this->types
        );

        $type_str = implode(' | ', $types);

        if ($parent_type instanceof IntersectionTypeNode || $parent_type instanceof UnionTypeNode) {
            return '('.$type_str.')';
        }

        return $type_str;
    }

    public function optimize(): TypeNode
    {
        $unique_types = [];

        foreach ($this->types as $type) {
            $optimized = $type->optimize();

            if ($optimized instanceof UnionTypeNode) {
                // Flatten nested unions
                foreach ($optimized->types as $nested_type) {
                    $unique_types[] = $nested_type;
                }

                continue;
            }

            // Check if we already have this type
            $type_str = $optimized->toTypeScript($this);
            if (! isset($unique_types[$type_str])) {
                $unique_types[$type_str] = $optimized;
            }
        }

        $result = array_values($unique_types);

        // If we have only one type and it's not nullable, return it directly
        if (count($result) === 1) {
            return $result[0];
        }

        // Otherwise, create a new union with the optimized types
        return new UnionTypeNode($result);
    }
}
