<?php

declare(strict_types=1);

namespace PhpTs\Data;

use InvalidArgumentException;
use ReflectionProperty;

final class TsArray implements TsType
{
    private function __construct(
        private string $raw
    ) {}

    public function render(RenderCtx $ctx): string
    {
        return $this->raw;
    }

    public static function from(ReflectionProperty $prop): self
    {
        $declClass = $prop->getDeclaringClass();

        $doc = $prop->getDocComment() ?: $declClass->getDocComment();

        $errorLoc = 'for property $'.$prop->getName().' in '.$declClass->getFileName().':'.$declClass->getStartLine();

        if (! $doc) {
            throw new InvalidArgumentException('Missing doc comment '.$errorLoc);
        }

        if (! preg_match('/@var\s+([^\s*]+)/i', $doc, $m)) {
            throw new InvalidArgumentException('Malformed PHPDoc '.$doc.' '.$errorLoc);
        }

        try {
            return self::parseArrayType(trim($m[1]));
        } catch (InvalidArgumentException $e) {
            throw new InvalidArgumentException($e->getMessage().' '.$errorLoc);
        }
    }

    private static function parseArrayType(string $type, int $depth = 0): TsType
    {
        // list<T>
        if (preg_match('/^list<(.+)>$/i', $type, $m)) {
            $t = explode(',', trim($m[1]), 2);
            if (count($t) !== 1) {
                throw new InvalidArgumentException('Expected exactly one type argument when evaluating ['.$type.']');
            }
            $valTs = self::parseArrayType($t[0], $depth + 1);

            return self::asArray($valTs);
        }

        // non-empty-list<T>
        if (preg_match('/^non-empty-list<(.+)>$/i', $type, $m)) {
            $t = explode(',', trim($m[1]), 2);
            if (count($t) !== 1) {
                throw new InvalidArgumentException('Expected exactly one type argument when evaluating ['.$type.']');
            }
            $valTs = self::parseArrayType($t[0], $depth + 1);

            return self::asNonEmptyArray($valTs);
        }

        // array<K,V>
        if (preg_match('/^array<(.+)>$/i', $type, $m)) {
            $kv = explode(',', trim($m[1]), 2);
            if (count($kv) !== 2) {
                throw new InvalidArgumentException('Expected array<K,V> to have exactly two type args when evaluating ['.$type.']');
            }
            [$kRaw, $vRaw] = [trim($kv[0]), trim($kv[1])];

            $keyKind = self::classifyArrayKey($kRaw);
            $valTs = self::parseArrayType($vRaw, $depth + 1);

            return match ($keyKind) {
                ArrayKeyType::int => self::asArray($valTs),
                ArrayKeyType::string => self::asIndexString($valTs),
                ArrayKeyType::both => self::asIndexString($valTs),
                ArrayKeyType::other => self::asIndexString($valTs),
            };
        }

        if ($depth === 0) {
            throw new InvalidArgumentException('Unsupported PHPDoc array type '.trim($type));
        }

        return TsScalar::from($type);
    }

    /**
     * Classify array key K in array<K,V> into int|string|both|other
     */
    protected static function classifyArrayKey(string $key): ArrayKeyType
    {
        $keys = array_map(trim(...), explode('|', trim($key)));

        $hasInt = false;
        $hasStr = false;
        $other = false;
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
                    $hasInt = true;
                    $hasStr = true;
                    break;

                default:
                    $other = true;
            }
        }

        if ($other) {
            return ArrayKeyType::other;
        }
        if ($hasInt && $hasStr) {
            return ArrayKeyType::both;
        }
        if ($hasInt) {
            return ArrayKeyType::int;
        }
        if ($hasStr) {
            return ArrayKeyType::string;
        }

        return ArrayKeyType::other;
    }

    protected static function asArray(TsType $ts): self
    {
        $rendered = $ts->render(RenderCtx::root());

        return new self($rendered.'[]');
    }

    protected static function asNonEmptyArray(TsType $ts): self
    {
        $rendered = $ts->render(RenderCtx::root());

        return new self('['.$rendered.', ...'.$rendered.'[]]');
    }

    protected static function asIndexString(TsType $ts): self
    {
        $rendered = $ts->render(RenderCtx::root());


        return new self('{ [key: string]: '.$rendered.' }');
    }
}
