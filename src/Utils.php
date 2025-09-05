<?php

declare(strict_types=1);

namespace Typographos;

final class Utils
{
    /**
     * @var array<string, string[]>
     */
    public static array $fqcnPartsCache = [];

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
     * Strip the generic type from a type.
     *
     * @example
     * ```php
     * Utils::stripGeneric('array<string>'); // 'array'
     * Utils::stripGeneric('non-empty-list<string>'); // 'non-empty-list'
     * Utils::stripGeneric('list<string>'); // 'list'
     * ```
     */
    public static function stripGeneric(string $type): string
    {
        for ($i = 0; $i < strlen($type); $i++) {
            if ($type[$i] === '<') {
                return substr($type, 0, $i);
            }
        }

        return $type;
    }

    /**
     * Check if a type is an array type.
     */
    public static function isArrayType(string $type): bool
    {
        return match (self::stripGeneric($type)) {
            'non-empty-list', 'list', 'array' => true,
            default => false,
        };
    }

    /**
     * Check if a type is a built-in PHP type.
     */
    public static function isBuiltinType(string $type): bool
    {
        if (self::isArrayType($type)) {
            return true;
        }

        return match ($type) {
            'int',
            'float',
            'string',
            'bool',
            'object',
            'callable',
            'iterable',
            'mixed',
            'null',
            'void',
            'false',
            'true',
            'never', => true,
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

                continue;
            }
            if ($ch === '>') {
                $depth = max(0, $depth - 1);
                $buf .= $ch;

                continue;
            }
            if ($ch === $separator && $depth === 0) {
                $parts[] = trim($buf);
                $buf = '';

                continue;
            }
            $buf .= $ch;
        }

        if ($buf !== '') {
            $parts[] = trim($buf);
        }

        return $parts;
    }
}
