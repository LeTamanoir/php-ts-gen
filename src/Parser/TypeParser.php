<?php

namespace PhpTs\Parser;

use Exception;
use PhpTs\Exceptions\EmptyParamTagTypeException;
use PhpTs\Exceptions\MissingDocBlockException;
use PhpTs\Exceptions\MissingParamTagException;
use PhpTs\Exceptions\MissingTypeException;
use PhpTs\Exceptions\UnsupportedTypeException;
use ReflectionClass;
use ReflectionIntersectionType;
use ReflectionNamedType;
use ReflectionProperty;
use ReflectionType;
use ReflectionUnionType;

class TypeParser
{
    /**
     * Array of classes already seen by the parser
     *
     * @var array<string, true>
     */
    protected array $processed_classes;

    /**
     * Create a new TypeParser instance
     */
    public function __construct()
    {
        $this->processed_classes = [];
    }

    /**
     * Parse a class into our AST
     */
    public function parseClass(string $class_name): TypeNode
    {
        // If we've already processed this class, return a reference to it
        if (isset($this->processed_classes[$class_name])) {
            return new ReferenceTypeNode($class_name);
        } else {
            $this->processed_classes[$class_name] = true;
        }

        $class_reflection = new ReflectionClass($class_name);

        $properties = [];

        foreach ($class_reflection->getProperties() as $prop) {
            $prop_type = $prop->getType();

            if ($prop_type === null) {
                throw new MissingTypeException($prop);
            }

            $properties[] = new PropertyTypeNode(
                $prop->getName(),
                $this->parseType($prop_type, $prop)
            );
        }

        return new ObjectTypeNode($class_name, $properties);
    }

    /**
     * Parse a reflection type into our AST
     */
    private function parseType(ReflectionType $type, ReflectionProperty $property): TypeNode
    {
        return match (true) {
            $type instanceof ReflectionNamedType => $this->parseNamedType($type, $property),
            $type instanceof ReflectionUnionType => $this->parseUnionType($type, $property),
            $type instanceof ReflectionIntersectionType => $this->parseIntersectionType($type, $property),
            default => new Exception('Unknown type: '.$type::class),
        };
    }

    /**
     * Parse a named type (built-in or class reference)
     */
    private function parseNamedType(ReflectionNamedType $type, ReflectionProperty $property): TypeNode
    {
        $type_node = $type->isBuiltin()
            ? match ($type->getName()) {
                'array' => $this->parseArrayType($property),
                'bool' => new BooleanTypeNode,
                'float', 'int' => new NumberTypeNode,
                'null' => new NullTypeNode,
                'string' => new StringTypeNode,
                'mixed' => new AnyTypeNode,
                default => throw new UnsupportedTypeException($type->getName(), $property),
            } : $this->parseClass($type->getName());

        // If the type is nullable, create a union with null
        if ($type->allowsNull()) {
            return new UnionTypeNode([$type_node, new NullTypeNode]);
        }

        return $type_node;
    }

    /**
     * Parse a union type
     */
    private function parseUnionType(ReflectionUnionType $type, ReflectionProperty $property): TypeNode
    {
        $types = array_map(
            fn (ReflectionType $t) => $this->parseType($t, $property),
            $type->getTypes()
        );

        return new UnionTypeNode($types);
    }

    /**
     * Parse an intersection type
     */
    private function parseIntersectionType(ReflectionIntersectionType $type, ReflectionProperty $property): TypeNode
    {
        $types = array_map(
            fn (ReflectionType $t) => $this->parseType($t, $property),
            $type->getTypes()
        );

        return new IntersectionTypeNode($types);
    }

    /**
     * Parse array type from property docblock
     */
    private function parseArrayType(ReflectionProperty $property): TypeNode
    {
        $doc_comment = $property->getDeclaringClass()->getConstructor()->getDocComment();

        if ($doc_comment === false) {
            throw new MissingDocBlockException($property);
        }

        // Parse the doc block
        $doc_comment = preg_replace('/^[\/\*]+[\s\n]*/', '', $doc_comment);
        $doc_comment = preg_replace('/[\s\n]+[\/\*]+$/', '', $doc_comment);
        $doc_comment = preg_replace('/[\/\*]+[\s\n]+/', '', $doc_comment);

        // Extract the @param tag for this property
        preg_match('/@param\s+([^\s]+(?:\s*,\s*[^\s]+)*)\s+\$'.preg_quote($property->getName(), '/').'/', $doc_comment, $matches);

        if (empty($matches)) {
            throw new MissingParamTagException($property);
        }

        $type_name = $matches[1];

        if ($type_name === '') {
            throw new EmptyParamTagTypeException($property);
        }

        return $this->parseArrayTypeString($type_name, $property);
    }

    /**
     * Parse array type string into ArrayTypeNode
     */
    private function parseArrayTypeString(string $type_name, ReflectionProperty $property): TypeNode
    {
        // Traditional array notation: T[]
        if (preg_match('/^([a-zA-Z][a-zA-Z0-9_]+)\[\]$/', $type_name, $matches)) {
            return new ArrayTypeNode($this->parseTypeString($matches[1], $property));
        }

        // List notation: list<T>
        if (preg_match('/^list<([a-zA-Z][a-zA-Z0-9_]+)>$/', $type_name, $matches)) {
            return new ArrayTypeNode($this->parseTypeString($matches[1], $property));
        }

        // Record notation: array<K, V>
        if (preg_match('/^array<([a-zA-Z][a-zA-Z0-9_]+),\s*([a-zA-Z][a-zA-Z0-9_]+)>$/', $type_name, $matches)) {
            $key_type = trim($matches[1]);
            $value_type = trim($matches[2]);

            return new RecordTypeNode(
                $this->parseTypeString($key_type, $property),
                $this->parseTypeString($value_type, $property)
            );
        }

        // Simple array notation: array<T>
        if (preg_match('/^array<([a-zA-Z][a-zA-Z0-9_]+)>$/', $type_name, $matches)) {
            return new ArrayTypeNode($this->parseTypeString($matches[1], $property));
        }

        throw new UnsupportedTypeException($type_name, $property);
    }

    /**
     * Parse a type string into a TypeNode
     */
    private function parseTypeString(string $type, ReflectionProperty $property): TypeNode
    {
        // Handle union types
        if (str_contains($type, '|')) {
            $types = array_map(
                fn (string $t) => $this->parseTypeString(trim($t), $property),
                explode('|', $type)
            );

            return new UnionTypeNode($types);
        }

        // Handle intersection types
        if (str_contains($type, '&')) {
            $types = array_map(
                fn (string $t) => $this->parseTypeString(trim($t), $property),
                explode('&', $type)
            );

            return new IntersectionTypeNode($types);
        }

        // Handle class types
        if (class_exists($type)) {
            return $this->parseClass($type);
        }

        // Handle single type
        return match (trim($type)) {
            'bool' => new BooleanTypeNode,
            'float', 'int' => new NumberTypeNode,
            'null' => new NullTypeNode,
            'string' => new StringTypeNode,
            'mixed' => new AnyTypeNode,
            default => throw new UnsupportedTypeException($type, $property),
        };
    }
}
