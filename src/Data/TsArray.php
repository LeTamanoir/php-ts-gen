<?php

declare(strict_types=1);

namespace Typographos\Data;

use InvalidArgumentException;
use Typographos\Queue;
use ReflectionClass;
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

    private static function parseArrayType(string $type, Queue $queue, int $depth = 0): TsType
    {
        // list<T>
        if (preg_match('/^list<(.+)>$/i', $type, $m)) {
            $t = self::splitTopLevel(trim($m[1]));
            if (count($t) !== 1) {
                throw new InvalidArgumentException('Expected exactly one type argument when evaluating ['.$type.']');
            }
            $valTs = self::parseArrayType($t[0], $queue, $depth + 1);

            return self::asArray($valTs);
        }

        // non-empty-list<T>
        if (preg_match('/^non-empty-list<(.+)>$/i', $type, $m)) {
            $t = self::splitTopLevel(trim($m[1]));
            if (count($t) !== 1) {
                throw new InvalidArgumentException('Expected exactly one type argument when evaluating ['.$type.']');
            }
            $valTs = self::parseArrayType($t[0], $queue, $depth + 1);

            return self::asNonEmptyArray($valTs);
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
                ArrayKeyType::int => self::asArray($valTs),
                ArrayKeyType::string => self::asIndexString($valTs),
                ArrayKeyType::both => self::asIndexString($valTs),
            };
        }

        if ($depth === 0) {
            throw new InvalidArgumentException('Unsupported PHPDoc array type '.trim($type));
        }

        $userDefined = class_exists($type) && new ReflectionClass($type)->isUserDefined();

        if ($userDefined) {
            $queue->enqueue($type);
        }

        return $userDefined ? new TsReference($type) : TsScalar::from($type);
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
            } elseif ($ch === '>') {
                $depth = max(0, $depth - 1);
                $buf .= $ch;
            } elseif ($ch === ',' && $depth === 0) {
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
                    $hasInt = true;
                    $hasStr = true;
                    break;

                default:
                    throw new InvalidArgumentException('Unsupported array key type ['.$k.']');
            }
        }

        if ($hasInt && $hasStr) {
            return ArrayKeyType::both;
        }
        if ($hasInt) {
            return ArrayKeyType::int;
        }

        // if ($hasStr) {
        return ArrayKeyType::string;
        // }
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
