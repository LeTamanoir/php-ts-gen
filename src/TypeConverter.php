<?php

declare(strict_types=1);

namespace Typographos;

use ReflectionClass;
use Typographos\Dto\ArrayType;
use Typographos\Dto\RawType;
use Typographos\Dto\ReferenceType;
use Typographos\Dto\ScalarType;
use Typographos\Dto\UnionType;
use Typographos\Interfaces\TypeScriptTypeInterface;

final class TypeConverter
{
    /**
     * Convert resolved PHP type to TypeScript type
     *
     * @param  array<string, string>  $typeReplacements
     */
    public static function convertToTypeScript(
        string $phpType,
        Queue $queue,
        array $typeReplacements = [],
    ): TypeScriptTypeInterface {
        $types = Utils::splitTopLevel($phpType, '|');
        $parts = [];

        if (count($types) === 0) {
            return ScalarType::unknown;
        }

        foreach ($types as $t) {
            $parts[] = self::convertSingleType($t, $queue, $typeReplacements);
        }

        if (count($parts) === 1) {
            return $parts[0];
        }

        return new UnionType($parts);
    }

    /**
     * Convert a single PHP type to TypeScript
     *
     * @param  array<string, string>  $typeReplacements
     */
    private static function convertSingleType(
        string $type,
        Queue $queue,
        array $typeReplacements,
    ): TypeScriptTypeInterface {
        if ($type === '') {
            return ScalarType::unknown;
        }

        $allowsNull = str_starts_with($type, '?');
        if ($allowsNull) {
            $type = substr($type, 1);
        }

        // Check for type replacements first
        if (isset($typeReplacements[$type])) {
            $ts = new RawType($typeReplacements[$type]);

            if ($allowsNull) {
                return new UnionType([$ts, ScalarType::null]);
            }

            return $ts;
        }

        // Handle built-in types
        if (Utils::isBuiltinType($type)) {
            $ts = Utils::isArrayType($type)
                ? ArrayType::from($typeReplacements, $type, $queue)
                : ScalarType::from($type);

            if ($allowsNull && $type !== 'null' && $type !== 'mixed') {
                return new UnionType([$ts, ScalarType::null]);
            }

            return $ts;
        }

        // Handle user-defined classes
        $userDefined = class_exists($type) && new ReflectionClass($type)->isUserDefined();

        if ($userDefined) {
            $queue->enqueue($type);
        }

        $ts = $userDefined ? new ReferenceType($type) : ScalarType::unknown;

        // Handle nullable class types
        if ($allowsNull) {
            return new UnionType([$ts, ScalarType::null]);
        }

        return $ts;
    }
}
