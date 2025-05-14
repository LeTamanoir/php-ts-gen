<?php

namespace PhpTs;

use PhpTs\Exceptions\EmptyTypeException;
use PhpTs\Exceptions\MissingDocBlockException;
use PhpTs\Exceptions\MissingParamTagException;
use PhpTs\Exceptions\UnknownBuiltinTypeException;
use PhpTs\Exceptions\UnknownTypeException;
use PhpTs\Exceptions\UnsupportedArraySyntaxException;
use PhpTs\Exceptions\UnsupportedIntersectionTypeException;
use PhpTs\Exceptions\UnsupportedTypeException;
use PhpTs\Exceptions\UnsupportedUnionTypeException;
use ReflectionClass;
use ReflectionIntersectionType;
use ReflectionNamedType;
use ReflectionProperty;
use ReflectionType;
use ReflectionUnionType;

class Gen
{
    public static array $known_types = [];

    public static function convertPhpBuiltinTypeToTs(string $name, ReflectionProperty $property): string
    {
        return match ($name) {
            'bool' => 'boolean',
            'float' => 'number',
            'int' => 'number',
            'null' => 'null',
            'object' => 'object',
            'string' => 'string',
            'false' => 'false',
            'mixed' => 'any',
            'never' => 'never',
            'true' => 'true',
            default => throw new UnknownBuiltinTypeException($name, $property),
        };
    }

    public static function parseReflectionNamedType(ReflectionNamedType $type, ReflectionProperty $property): string
    {
        $result_type = match ($type->isBuiltin()) {
            true => match ($type->getName()) {
                'array' => self::parseArrayType($type, $property),

                'callable' => throw new UnsupportedTypeException('callable', $property),
                'iterable' => throw new UnsupportedTypeException('iterable', $property),
                'void' => throw new UnsupportedTypeException('void', $property),

                default => self::convertPhpBuiltinTypeToTs($type->getName(), $property),
            },

            false => self::parseClass(new ReflectionClass($type->getName())),
        };

        return $result_type.($type->allowsNull() ? ' | null' : '');
    }

    public static function parseReflectionUnionType(ReflectionUnionType $reflection_type, ReflectionProperty $property): string
    {
        $types = array_map(
            static fn (ReflectionType $t) => self::parseReflectionType($t, $property),
            $reflection_type->getTypes()
        );

        return '('.implode(' | ', $types).')'.($reflection_type->allowsNull() ? ' | null' : '');
    }

    public static function parseReflectionIntersectionType(ReflectionIntersectionType $reflection_type, ReflectionProperty $property): string
    {
        $types = array_map(
            static fn (ReflectionType $t) => self::parseReflectionType($t, $property),
            $reflection_type->getTypes()
        );

        return '('.implode(' & ', $types).')'.($reflection_type->allowsNull() ? ' | null' : '');
    }

    public static function parseReflectionType(ReflectionType $type, ReflectionProperty $property): string
    {
        return match (true) {
            $type instanceof ReflectionNamedType => self::parseReflectionNamedType($type, $property),

            $type instanceof ReflectionUnionType => self::parseReflectionUnionType($type, $property),

            $type instanceof ReflectionIntersectionType => self::parseReflectionIntersectionType($type, $property),

            default => throw new UnknownTypeException($property),
        };
    }

    public static function parseArrayType(ReflectionNamedType $type, ReflectionProperty $property): string
    {
        $doc_comment = $property->getDeclaringClass()->getConstructor()->getDocComment();

        if ($doc_comment === false) {
            throw new MissingDocBlockException($property);
        }
        // parse the doc block
        $doc_comment = preg_replace('/^[\/\*]+[\s\n]*/', '', $doc_comment);
        $doc_comment = preg_replace('/[\s\n]+[\/\*]+$/', '', $doc_comment);
        $doc_comment = preg_replace('/[\/\*]+[\s\n]+/', '', $doc_comment);

        // extract the @param tag for this property
        $pattern = '/@param\s+([^\s]+)\s+\$'.preg_quote($property->getName(), '/').'/';
        preg_match($pattern, $doc_comment, $matches);

        if (empty($matches)) {
            throw new MissingParamTagException($property);
        }

        $type_name = $matches[1];

        if ($type_name === '') {
            throw new EmptyTypeException($property);
        }

        if (str_contains($type_name, '|')) {
            throw new UnsupportedUnionTypeException($property);
        }

        if (str_contains($type_name, '&')) {
            throw new UnsupportedIntersectionTypeException($property);
        }

        if (! str_contains($type_name, '[]')) {
            throw new UnsupportedArraySyntaxException($property);
        }

        $type_name = str_replace('[]', '', $type_name);

        if (class_exists($type_name)) {
            return 'Array<'.self::parseClass(new ReflectionClass($type_name)).'>';
        } else {
            return 'Array<'.self::convertPhpBuiltinTypeToTs($type_name, $property).'>';
        }
    }

    public static function parseClass(ReflectionClass $reflection_class)
    {
        if (isset(static::$known_types[$reflection_class->getName()])) {
            return static::$known_types[$reflection_class->getName()];
        } else {
            static::$known_types[$reflection_class->getName()] = $reflection_class->getName();
        }

        $ts_record = [];
        foreach ($reflection_class->getProperties() as $property) {
            $ts_record[$property->getName()] = self::parseReflectionType($property->getType(), $property);
        }

        $result = '{';
        if (count($ts_record) > 0) {
            $result .= PHP_EOL;
        }
        foreach ($ts_record as $prop_name => $prop_type) {
            $result .= '  '.$prop_name.': '.$prop_type.';'.PHP_EOL;
        }
        $result .= '}';

        return $result;
    }

    /**
     * @param  list<class-string>  $dtos
     */
    public static function generate(array $dtos): void
    {
        $all_types = '';

        foreach (array_unique($dtos) as $dto) {
            $reflection_class = new ReflectionClass($dto);
            $ts_type = self::parseClass($reflection_class);
            $all_types .= 'type '.$reflection_class->getName().' = '.$ts_type.PHP_EOL;
        }

        dd($all_types);
    }
}
