<?php

namespace PhpTs\Generator;

use PhpTs\Parser\ArrayTypeNode;
use PhpTs\Parser\IntersectionTypeNode;
use PhpTs\Parser\NamedTypeNode;
use PhpTs\Parser\TypeNode;
use PhpTs\Parser\UnionTypeNode;

class TypeScriptGenerator
{
    /**
     * Convert a TypeNode to TypeScript type
     */
    public static function generateType(TypeNode $node): string
    {
        return match (true) {
            $node instanceof NamedTypeNode => self::generateNamedType($node),
            $node instanceof UnionTypeNode => self::generateUnionType($node),
            $node instanceof IntersectionTypeNode => self::generateIntersectionType($node),
            $node instanceof ArrayTypeNode => self::generateArrayType($node),
            default => throw new \RuntimeException('Unknown type node: '.get_class($node)),
        };
    }

    /**
     * Convert a NamedTypeNode to TypeScript type
     */
    private static function generateNamedType(NamedTypeNode $node): string
    {
        if ($node->isBuiltin) {
            return match ($node->name) {
                'bool' => 'boolean',
                'float', 'int' => 'number',
                'null' => 'null',
                'object' => 'object',
                'string' => 'string',
                'false' => 'false',
                'mixed' => 'any',
                'never' => 'never',
                'true' => 'true',
                default => throw new \RuntimeException("Unknown builtin type: {$node->name}"),
            };
        }

        return $node->name;
    }

    /**
     * Convert a UnionTypeNode to TypeScript type
     */
    private static function generateUnionType(UnionTypeNode $node): string
    {
        $types = array_map(
            fn (TypeNode $t) => self::generateType($t),
            $node->types
        );

        return '('.implode(' | ', $types).')';
    }

    /**
     * Convert an IntersectionTypeNode to TypeScript type
     */
    private static function generateIntersectionType(IntersectionTypeNode $node): string
    {
        $types = array_map(
            fn (TypeNode $t) => self::generateType($t),
            $node->types
        );

        return '('.implode(' & ', $types).')';
    }

    /**
     * Convert an ArrayTypeNode to TypeScript type
     */
    private static function generateArrayType(ArrayTypeNode $node): string
    {
        $value_type = self::generateType($node->valueType);

        if ($node->keyType === null) {
            return "Array<{$value_type}>";
        }

        $key_type = self::generateType($node->keyType);

        return "Record<{$key_type}, {$value_type}>";
    }
}
