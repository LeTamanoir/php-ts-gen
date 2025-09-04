<?php

declare(strict_types=1);

namespace Typographos\Dto;

use Override;
use Typographos\Interfaces\TypeScriptType;
use Typographos\Traits\HasProperties;
use Typographos\Utils;

/**
 * @api
 */
final class InlineRecordType implements TypeScriptType
{
    /**
     * @use HasProperties<TypeScriptType>
     */
    use HasProperties;

    #[Override]
    public function render(RenderCtx $ctx): string
    {
        $idt = str_repeat($ctx->indent, $ctx->depth + 1);
        $innerIdt = $idt.$ctx->indent;

        $ts = "{\n";

        foreach ($this->properties as $name => $type) {
            $ts .= $innerIdt.Utils::tsProp($name).': '.$type->render($ctx)."\n";
        }

        $ts .= $idt.'}';

        return $ts;
    }
}
