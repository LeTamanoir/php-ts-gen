<?php

declare(strict_types=1);

namespace Typographos;

use InvalidArgumentException;
use ReflectionProperty;

final class TypeResolver
{
    /**
     * Resolve PHP type string, handling special cases like array, self, parent
     *
     * @throws InvalidArgumentException
     */
    public static function resolve(ReflectionProperty $prop): string
    {
        $type = (string) $prop->getType();

        if ($type === '') {
            return '';
        }

        if (str_contains($type, '&')) {
            throw new InvalidArgumentException('Intersection types are not supported');
        }

        // nullable starting with `?` can't be unioned
        if (str_starts_with($type, '?')) {
            return '?'.self::resolveType(substr($type, 1), $prop);
        }

        $types = Utils::splitTopLevel($type, '|');
        $resolved = '';

        for ($i = 0; $i < count($types); $i++) {
            $resolved .= self::resolveType($types[$i], $prop);
            if ($i < (count($types) - 1)) {
                $resolved .= '|';
            }
        }

        return $resolved;
    }

    /**
     * Handle special PHP types that need transformation
     *
     * @throws InvalidArgumentException
     */
    private static function resolveType(string $type, ReflectionProperty $prop): string
    {
        return match ($type) {
            'array' => self::resolveArrayType($prop),
            'self' => $prop->getDeclaringClass()->getName(),
            'parent' => self::resolveParentType($prop),
            default => $type,
        };
    }

    /**
     * Parse array type from PHPDoc comments
     *
     * Searches for array type information in two locations:
     * 1. Property-level @var docblock
     * 2. Constructor @param docblock
     *
     * @throws InvalidArgumentException
     */
    private static function resolveArrayType(ReflectionProperty $prop): string
    {
        $declClass = $prop->getDeclaringClass();
        $errorContext = "for property \${$prop->getName()} in {$declClass->getFileName()}:{$declClass->getStartLine()}";

        // Try property @var docblock first
        $doc = $prop->getDocComment();
        if ($doc) {
            $arrayType = self::extractVarType($doc);
            if ($arrayType !== null) {
                return $arrayType;
            }
            throw new InvalidArgumentException("Malformed PHPDoc [{$doc}] {$errorContext}");
        }

        // Fall back to constructor @param docblock
        $constructorDoc = $prop->getDeclaringClass()->getConstructor()?->getDocComment();
        if ($constructorDoc) {
            $arrayType = self::extractParamType($constructorDoc, $prop->getName());
            if ($arrayType !== null) {
                return $arrayType;
            }
            throw new InvalidArgumentException("Malformed PHPDoc [{$constructorDoc}] {$errorContext}");
        }

        throw new InvalidArgumentException("Missing doc comment {$errorContext}");
    }

    /**
     * Extract type from '@var' docblock
     */
    private static function extractVarType(string $doc): ?string
    {
        $matches = null;
        $pattern = '/@var\s+([^*]+)/i';

        if (preg_match($pattern, $doc, $matches)) {
            return trim($matches[1]);
        }

        return null;
    }

    /**
     * Extract type from '@param' docblock
     */
    private static function extractParamType(string $doc, string $propName): ?string
    {
        $matches = null;
        $pattern = sprintf('/@param\s+([^\s*]+)\s+%s/i', preg_quote('$'.$propName));

        if (preg_match($pattern, $doc, $matches)) {
            return trim($matches[1]);
        }

        return null;
    }

    /**
     * Resolve parent type reference
     *
     * @throws InvalidArgumentException
     */
    private static function resolveParentType(ReflectionProperty $prop): string
    {
        $type = 'parent';
        $currentClass = $prop->getDeclaringClass()->getName();

        while ($type === 'parent') {
            $type = get_parent_class($currentClass);
            if (! $type) {
                throw new InvalidArgumentException('Parent class not found for '.$currentClass);
            }
            $currentClass = $type;
        }

        return $type;
    }
}
