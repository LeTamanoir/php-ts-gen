<?php

declare(strict_types=1);

namespace Typographos\Dto;

use InvalidArgumentException;

/**
 * ArrayKeyType represents the type of the key of an array.
 *
 * @internal
 */
enum ArrayKeyType
{
    case Int;
    case String;
    case Both;

    /**
     * Create ArrayKeyType from PHPDoc type string
     *
     * Supports union types like 'int|string' and specialized types
     * like 'positive-int', 'non-empty-string', etc.
     */
    public static function from(string $type): self
    {
        $keys = array_map(trim(...), explode('|', trim($type)));

        $hasInt = false;
        $hasStr = false;

        foreach ($keys as $key) {
            $keyType = match (strtolower($key)) {
                'int', 'positive-int', 'negative-int', 'int-mask', 'int-mask-of' => self::Int,
                'string',
                'non-empty-string',
                'lowercase-string',
                'uppercase-string',
                'class-string',
                'literal-string', => self::String,
                'array-key' => self::Both,
                default => throw new InvalidArgumentException("Unsupported array key type [{$key}]"),
            };

            if ($keyType === self::Both) {
                return self::Both;
            }

            $hasInt = $hasInt || $keyType === self::Int;
            $hasStr = $hasStr || $keyType === self::String;
        }

        return match (true) {
            $hasStr && $hasInt => self::Both,
            $hasStr => self::String,
            default => self::Int,
        };
    }
}
