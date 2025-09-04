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

    public static function from(string $type): self
    {
        $keys = array_map(trim(...), explode('|', trim($type)));

        $hasInt = false;
        $hasStr = false;

        foreach ($keys as $k) {
            switch (strtolower($k)) {
                case 'int':
                case 'positive-int':
                case 'negative-int':
                case 'int-mask':
                case 'int-mask-of':
                    $hasInt = true;
                    break;

                case 'string':
                case 'non-empty-string':
                case 'lowercase-string':
                case 'uppercase-string':
                case 'class-string':
                case 'literal-string':
                    $hasStr = true;
                    break;

                case 'array-key':
                    return self::Both;

                default:
                    throw new InvalidArgumentException('Unsupported array key type ['.$k.']');
            }
        }

        if ($hasStr && $hasInt) {
            return self::Both;
        }
        if ($hasStr) {
            return self::String;
        }

        return self::Int;
    }
}
