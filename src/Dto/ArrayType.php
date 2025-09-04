<?php

declare(strict_types=1);

namespace Typographos\Dto;

use InvalidArgumentException;
use Override;
use Typographos\Codegen;
use Typographos\Interfaces\TypeScriptType;
use Typographos\Queue;
use Typographos\Utils;

/**
 * @api
 */
final class ArrayType implements TypeScriptType
{
    private function __construct(
        private ArrayKind $kind,
        private TypeScriptType $inner
    ) {}

    #[Override]
    public function render(RenderCtx $ctx): string
    {
        return $this->kind->render($this->inner->render($ctx));
    }

    public static function from(Codegen $codegen, string $type, Queue $queue): self
    {
        // list<T>
        if (preg_match('/^list<(.+)>$/i', $type, $m)) {
            $t = Utils::splitTopLevel(trim($m[1]), ',');
            if (count($t) !== 1) {
                throw new InvalidArgumentException('Expected exactly one type argument when evaluating ['.$type.']');
            }
            $valTs = $codegen->mapType($t[0], $queue);

            return new self(ArrayKind::List, $valTs);
        }

        // non-empty-list<T>
        if (preg_match('/^non-empty-list<(.+)>$/i', $type, $m)) {
            $t = Utils::splitTopLevel(trim($m[1]), ',');
            if (count($t) !== 1) {
                throw new InvalidArgumentException('Expected exactly one type argument when evaluating ['.$type.']');
            }
            $valTs = $codegen->mapType($t[0], $queue);

            return new self(ArrayKind::NonEmptyList, $valTs);
        }

        // array<K,V>
        if (preg_match('/^array<(.+)>$/i', $type, $m)) {
            $kv = Utils::splitTopLevel(trim($m[1]), ',');
            if (count($kv) !== 2) {
                throw new InvalidArgumentException('Expected array<K,V> to have exactly two type args when evaluating ['.$type.']');
            }
            [$kRaw, $vRaw] = [trim($kv[0]), trim($kv[1])];

            $keyKind = ArrayKeyType::from($kRaw);
            $valTs = $codegen->mapType($vRaw, $queue);

            return match ($keyKind) {
                ArrayKeyType::Int => new self(ArrayKind::List, $valTs),
                ArrayKeyType::String => new self(ArrayKind::IndexString, $valTs),
                ArrayKeyType::Both => new self(ArrayKind::IndexString, $valTs),
            };
        }

        throw new InvalidArgumentException('Unsupported PHPDoc array type '.trim($type));
    }
}
