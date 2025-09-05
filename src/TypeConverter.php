<?php

declare(strict_types=1);

namespace Typographos;

use InvalidArgumentException;
use ReflectionClass;
use ReflectionException;
use Typographos\Attributes\InlineType;
use Typographos\Dto\ArrayType;
use Typographos\Dto\GenCtx;
use Typographos\Dto\InlineRecordType;
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
     * @throws InvalidArgumentException
     * @throws ReflectionException
     */
    public static function convert(GenCtx $ctx, string $type): TypeScriptTypeInterface
    {
        $types = Utils::splitTopLevel($type, '|');
        $parts = [];

        if (count($types) === 0) {
            return ScalarType::unknown;
        }

        foreach ($types as $t) {
            $parts[] = self::convertType($ctx, $t);
        }

        if (count($parts) === 1) {
            return $parts[0];
        }

        return new UnionType($parts);
    }

    /**
     * Convert a single PHP type to TypeScript
     *
     * @throws InvalidArgumentException
     * @throws ReflectionException
     */
    private static function convertType(GenCtx $ctx, string $type): TypeScriptTypeInterface
    {
        if ($type === '') {
            return ScalarType::unknown;
        }

        $allowsNull = str_starts_with($type, '?');
        if ($allowsNull) {
            $type = substr($type, 1);
        }

        // check for type replacements first
        if (isset($ctx->typeReplacements[$type])) {
            $ts = new RawType($ctx->typeReplacements[$type]);

            if ($allowsNull) {
                return new UnionType([$ts, ScalarType::null]);
            }

            return $ts;
        }

        // handle built-in types
        if (Utils::isBuiltinType($type)) {
            $ts = Utils::isArrayType($type) ? ArrayType::from($ctx, $type) : ScalarType::from($type);

            if ($allowsNull && $type !== 'null' && $type !== 'mixed') {
                return new UnionType([$ts, ScalarType::null]);
            }

            return $ts;
        }

        // handle user-defined classes
        $userDefined = class_exists($type) && new ReflectionClass($type)->isUserDefined();

        if ($userDefined) {
            // check if the property has the InlineType attribute
            $shouldInline =
                $ctx->parentProperty !== null && count($ctx->parentProperty->getAttributes(InlineType::class)) > 0;

            if ($shouldInline) {
                $ts = InlineRecordType::from($ctx, $type);
            } else {
                $ctx->queue->enqueue($type);
                $ts = new ReferenceType($type);
            }
        } else {
            $ts = ScalarType::unknown;
        }

        if ($allowsNull) {
            return new UnionType([$ts, ScalarType::null]);
        }

        return $ts;
    }
}
