<?php

declare(strict_types=1);

namespace Typographos\Dto;

use Override;
use Typographos\Interfaces\TypeScriptType;

/**
 * UnionType represents a union of TypeScript types.
 *
 * @api
 */
final class UnionType implements TypeScriptType
{
    /**
     * @param  TypeScriptType[]  $types
     */
    public function __construct(private array $types) {}

    #[Override]
    public function render(RenderCtx $ctx): string
    {
        // remove duplicates
        $rendered = [];
        foreach ($this->types as $t) {
            $render = $t->render($ctx);
            $rendered[$render] = true;
        }

        return implode(' | ', array_keys($rendered));
    }
}
