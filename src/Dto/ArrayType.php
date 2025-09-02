<?php

declare(strict_types=1);

namespace Typographos\Dto;

use InvalidArgumentException;
use ReflectionClass;
use ReflectionProperty;
use Typographos\Interfaces\TypeScriptType;
use Typographos\Queue;

final class ArrayType implements TypeScriptType
{
    private function __construct(
        private ArrayKind $kind,
        private TypeScriptType $inner
    ) {}

    public function render(RenderCtx $ctx): string
    {
        $inner = $this->inner->render($ctx);

        return match ($this->kind) {
            ArrayKind::List => $inner.'[]',
            ArrayKind::NonEmptyList => '['.$inner.', ...'.$inner.'[]]',
            ArrayKind::IndexString => '{ [key: string]: '.$inner.' }',
        };
    }

    public static function from(ReflectionProperty $prop, Queue $queue): self
    {
        $declClass = $prop->getDeclaringClass();

        $errorLoc = 'for property $'.$prop->getName().' in '.$declClass->getFileName().':'.$declClass->getStartLine();

        if ($doc = $prop->getDocComment()) {
            if (! preg_match('/@var\s+([^*]+)/i', $doc, $m)) {
                throw new InvalidArgumentException('Malformed PHPDoc ['.$doc.'] '.$errorLoc);
            }
        } elseif ($doc = $declClass->getConstructor()->getDocComment()) {
            if (! preg_match('/@param\s+([^\s*]+)\s+\$'.preg_quote($prop->getName()).'/i', $doc, $m)) {
                throw new InvalidArgumentException('Malformed PHPDoc ['.$doc.'] '.$errorLoc);
            }
        } else {
            throw new InvalidArgumentException('Missing doc comment '.$errorLoc);
        }

        try {
            return self::parseArrayType(trim($m[1]), $queue);
        } catch (InvalidArgumentException $e) {
            throw new InvalidArgumentException($e->getMessage().' '.$errorLoc);
        }
    }

    private static function parseArrayType(string $type, Queue $queue, int $depth = 0): TypeScriptType
    {
        // list<T>
        if (preg_match('/^list<(.+)>$/i', $type, $m)) {
            $t = self::splitTopLevel(trim($m[1]));
            if (count($t) !== 1) {
                throw new InvalidArgumentException('Expected exactly one type argument when evaluating ['.$type.']');
            }
            $valTs = self::parseArrayType($t[0], $queue, $depth + 1);

            return new self(ArrayKind::List, $valTs);
        }

        // non-empty-list<T>
        if (preg_match('/^non-empty-list<(.+)>$/i', $type, $m)) {
            $t = self::splitTopLevel(trim($m[1]));
            if (count($t) !== 1) {
                throw new InvalidArgumentException('Expected exactly one type argument when evaluating ['.$type.']');
            }
            $valTs = self::parseArrayType($t[0], $queue, $depth + 1);

            return new self(ArrayKind::NonEmptyList, $valTs);
        }

        // array<K,V>
        if (preg_match('/^array<(.+)>$/i', $type, $m)) {
            $kv = self::splitTopLevel(trim($m[1]));
            if (count($kv) !== 2) {
                throw new InvalidArgumentException('Expected array<K,V> to have exactly two type args when evaluating ['.$type.']');
            }
            [$kRaw, $vRaw] = [trim($kv[0]), trim($kv[1])];

            $keyKind = self::classifyArrayKey($kRaw);
            $valTs = self::parseArrayType($vRaw, $queue, $depth + 1);

            return match ($keyKind) {
                ArrayKeyType::Int => new self(ArrayKind::List, $valTs),
                ArrayKeyType::String => new self(ArrayKind::IndexString, $valTs),
                ArrayKeyType::Both => new self(ArrayKind::IndexString, $valTs),
            };
        }

        if ($depth === 0) {
            throw new InvalidArgumentException('Unsupported PHPDoc array type '.trim($type));
        }

        $userDefined = class_exists($type) && new ReflectionClass($type)->isUserDefined();

        if ($userDefined) {
            $queue->enqueue($type);
        }

        return $userDefined ? new ReferenceType($type) : ScalarType::from($type);
    }

    /**
     * Split by commas at top level, ignores commas inside generics  <...>
     */
    private static function splitTopLevel(string $s): array
    {
        $parts = [];
        $buf = '';
        $depth = 0;

        foreach (str_split($s) as $ch) {
            if ($ch === '<') {
                $depth++;
                $buf .= $ch;

                continue;
            }
            if ($ch === '>') {
                $depth = max(0, $depth - 1);
                $buf .= $ch;

                continue;

            } if ($ch === ',' && $depth === 0) {
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

    protected static function classifyArrayKey(string $key): ArrayKeyType
    {
        $keys = array_map(trim(...), explode('|', trim($key)));

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
                    return ArrayKeyType::Both;

                default:
                    throw new InvalidArgumentException('Unsupported array key type ['.$k.']');
            }
        }

        if ($hasStr && $hasInt) {
            return ArrayKeyType::Both;
        }
        if ($hasStr) {
            return ArrayKeyType::String;
        }

        return ArrayKeyType::Int;
    }
}
