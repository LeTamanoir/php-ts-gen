<?php

declare(strict_types=1);

namespace Typographos\Dto;

use InvalidArgumentException;
use Override;
use Typographos\Interfaces\TypeScriptTypeInterface;
use Typographos\Queue;
use Typographos\TypeConverter;
use Typographos\Utils;

/**
 * @api
 */
final class ArrayType implements TypeScriptTypeInterface
{
    private function __construct(
        private ArrayKind $kind,
        private TypeScriptTypeInterface $inner,
    ) {}

    #[Override]
    public function render(RenderCtx $ctx): string
    {
        return $this->kind->render($this->inner->render($ctx));
    }

    /**
     * Create ArrayType from PHPDoc array notation
     *
     * Supported formats:
     * - list<T> → T[]
     * - non-empty-list<T> → [T, ...T[]]
     * - array<K,V> → V[] or { [key: string]: V }
     *
     * @param  array<string, string>  $typeReplacements
     */
    public static function from(array $typeReplacements, string $type, Queue $queue): self
    {
        // Parse generic array notation
        if (!preg_match('/^([a-z-]+)<(.+)>$/i', $type, $matches)) {
            throw new InvalidArgumentException('Unsupported PHPDoc array type ' . trim($type));
        }

        [$_, $arrayTypeName, $typeArgs] = $matches;

        return match (strtolower($arrayTypeName)) {
            'list' => self::createList($typeArgs, $queue, $typeReplacements, $type),
            'non-empty-list' => self::createNonEmptyList($typeArgs, $queue, $typeReplacements, $type),
            'array' => self::createArray($typeArgs, $queue, $typeReplacements, $type),
            default => throw new InvalidArgumentException('Unsupported PHPDoc array type ' . trim($type)),
        };
    }

    /**
     * Create list<T> array type
     *
     * @param  array<string, string>  $typeReplacements
     */
    private static function createList(
        string $typeArgs,
        Queue $queue,
        array $typeReplacements,
        string $originalType,
    ): self {
        $types = Utils::splitTopLevel(trim($typeArgs), ',');
        if (count($types) !== 1) {
            throw new InvalidArgumentException("Expected exactly one type argument when evaluating [{$originalType}]");
        }

        $valueType = TypeConverter::convertToTypeScript(trim($types[0]), $queue, $typeReplacements);

        return new self(ArrayKind::List, $valueType);
    }

    /**
     * Create non-empty-list<T> array type
     *
     * @param  array<string, string>  $typeReplacements
     */
    private static function createNonEmptyList(
        string $typeArgs,
        Queue $queue,
        array $typeReplacements,
        string $originalType,
    ): self {
        $types = Utils::splitTopLevel(trim($typeArgs), ',');
        if (count($types) !== 1) {
            throw new InvalidArgumentException("Expected exactly one type argument when evaluating [{$originalType}]");
        }

        $valueType = TypeConverter::convertToTypeScript(trim($types[0]), $queue, $typeReplacements);

        return new self(ArrayKind::NonEmptyList, $valueType);
    }

    /**
     * Create array<K,V> type with key-value pairs
     *
     * @param  array<string, string>  $typeReplacements
     */
    private static function createArray(
        string $typeArgs,
        Queue $queue,
        array $typeReplacements,
        string $originalType,
    ): self {
        $types = Utils::splitTopLevel(trim($typeArgs), ',');
        if (count($types) !== 2) {
            throw new InvalidArgumentException(
                "Expected array<K,V> to have exactly two type args when evaluating [{$originalType}]",
            );
        }

        [$keyRaw, $valueRaw] = [trim($types[0]), trim($types[1])];
        $keyKind = ArrayKeyType::from($keyRaw);
        $valueType = TypeConverter::convertToTypeScript($valueRaw, $queue, $typeReplacements);

        return match ($keyKind) {
            ArrayKeyType::Int => new self(ArrayKind::List, $valueType),
            ArrayKeyType::String => new self(ArrayKind::IndexString, $valueType),
            ArrayKeyType::Both => new self(ArrayKind::IndexString, $valueType),
        };
    }
}
