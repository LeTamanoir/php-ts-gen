<?php

declare(strict_types=1);

namespace Typographos\Dto;

use InvalidArgumentException;
use Override;
use Typographos\Interfaces\TypeScriptTypeInterface;

/**
 * UnionType represents a union of TypeScript types.
 */
final class UnionType implements TypeScriptTypeInterface
{
    /**
     * @param  TypeScriptTypeInterface[]  $types  Non-empty array of types to union
     *
     * @throws InvalidArgumentException
     */
    public function __construct(
        private array $types,
    ) {
        if (count($types) === 0) {
            throw new InvalidArgumentException('UnionType requires at least one type');
        }
    }

    #[Override]
    public function render(RenderCtx $ctx): string
    {
        return implode(' | ', $this->getUniqueRenderedTypes($ctx));
    }

    /**
     * Get unique rendered type strings, preserving order
     *
     * @return string[]
     */
    private function getUniqueRenderedTypes(RenderCtx $ctx): array
    {
        $uniqueTypes = [];
        $seen = [];

        foreach ($this->types as $type) {
            $rendered = $type->render($ctx);
            if (!isset($seen[$rendered])) {
                $uniqueTypes[] = $rendered;
                $seen[$rendered] = true;
            }
        }

        return $uniqueTypes;
    }
}
