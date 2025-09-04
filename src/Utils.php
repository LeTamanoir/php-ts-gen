<?php

declare(strict_types=1);

namespace Typographos;

/**
 * @api
 */
final class Utils
{
    /**
     * @var array<string, string[]>
     */
    public static $fqcnPartsCache = [];

    /**
     * Property names can be quoted—so we only quote when necessary.
     */
    public static function tsProp(string $raw): string
    {
        if (preg_match('/^[A-Za-z_$][A-Za-z0-9_$]*$/', $raw)) {
            return $raw;
        }
        // quote and escape inner quotes/backslashes
        $escaped = addcslashes($raw, '\\"');

        return "\"{$escaped}\"";
    }

    /**
     * Convert a raw string to a valid TS identifier.
     */
    public static function tsIdent(string $raw): string
    {
        return preg_replace('/[^A-Za-z0-9_$]/', '_', $raw) ?? '_';
    }

    /**
     * Get the parts of a fully qualified PHP class name.
     *
     * @return string[]
     */
    public static function fqcnParts(string $raw): array
    {
        return self::$fqcnPartsCache[$raw] ??= array_values(array_filter(explode('\\', ltrim($raw, '\\'))));
    }

    /**
     * Get the TS class name from a fully qualified PHP class name.
     */
    public static function tsFqcn(string $fqcn): string
    {
        return implode('.', array_map(self::tsIdent(...), self::fqcnParts($fqcn)));
    }

    /**
     * Check if a type is an array type.
     */
    public static function isArrayType(string $type): bool
    {
        $type = explode('<', $type, 2)[0] ?? '';

        return match ($type) {
            'non-empty-list',
            'list',
            'array' => true,

            default => false,
        };
    }

    /**
     * Check if a type is a built-in PHP type.
     */
    public static function isBuiltinType(string $type): bool
    {
        $type = explode('<', $type, 2)[0] ?? '';

        return match ($type) {
            'int',
            'float',
            'string',
            'bool',
            'array',
            'object',
            'callable',
            'iterable',
            'mixed',
            'null',
            'void',
            'false',
            'true',
            'non-empty-list',
            'list',
            'never' => true,

            default => false,
        };
    }

    /**
     * @return string[]
     */
    public static function splitTopLevel(string $content, string $separator): array
    {
        $parts = [];
        $buf = '';
        $depth = 0;

        foreach (str_split($content) as $ch) {
            if ($ch === '<') {
                $depth++;
                $buf .= $ch;
            } elseif ($ch === '>') {
                $depth = max(0, $depth - 1);
                $buf .= $ch;
            } elseif ($ch === $separator && $depth === 0) {
                $parts[] = trim($buf);
                $buf = '';
            } else {
                $buf .= $ch;
            }
        }

        if ($buf !== '') {
            $parts[] = trim($buf);
        }

        return $parts;
    }
}
