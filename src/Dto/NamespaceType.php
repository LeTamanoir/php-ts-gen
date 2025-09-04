<?php

declare(strict_types=1);

namespace Typographos\Dto;

use Override;
use Typographos\Interfaces\TypeScriptType;
use Typographos\Traits\HasChildren;

/**
 * @api
 */
final class NamespaceType implements TypeScriptType
{
    /**
     * @use HasChildren<NamespaceType|RecordType>
     */
    use HasChildren;

    public function __construct(
        public string $name
    ) {}

    #[Override]
    public function render(RenderCtx $ctx): string
    {
        $idt = str_repeat($ctx->indent, $ctx->depth);

        $ts = $idt.($ctx->depth === 0 ? 'declare namespace ' : 'export namespace ').$this->name." {\n";

        foreach ($this->children as $t) {
            $ts .= $t->render($ctx->increaseDepth());
        }

        $ts .= $idt."}\n";

        return $ts;
    }
}
