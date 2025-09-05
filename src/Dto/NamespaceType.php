<?php

declare(strict_types=1);

namespace Typographos\Dto;

use Override;
use Typographos\Interfaces\TypeScriptTypeInterface;
use Typographos\Traits\HasChildrenTrait;

final class NamespaceType implements TypeScriptTypeInterface
{
    /**
     * @use HasChildrenTrait<NamespaceType|RecordType>
     */
    use HasChildrenTrait;

    public function __construct(
        public string $name,
    ) {}

    #[Override]
    public function render(RenderCtx $ctx): string
    {
        $indent = str_repeat($ctx->indent, $ctx->depth);

        $declaration = $ctx->depth === 0 ? 'declare namespace' : 'export namespace';

        $ts = $indent.$declaration.' '.$this->name." {\n";

        foreach ($this->children as $child) {
            $ts .= $child->render($ctx->increaseDepth());
        }

        $ts .= $indent."}\n";

        return $ts;
    }
}
