<?php

declare(strict_types=1);

namespace Typographos\Dto;

use Override;
use Typographos\Interfaces\TypeScriptTypeInterface;
use Typographos\Traits\HasPropertiesTrait;
use Typographos\Utils;

/**
 * @api
 */
final class InlineRecordType implements TypeScriptTypeInterface
{
    /**
     * @use HasPropertiesTrait<TypeScriptTypeInterface>
     */
    use HasPropertiesTrait;

    #[Override]
    public function render(RenderCtx $ctx): string
    {
        $baseIndent = str_repeat($ctx->indent, $ctx->depth + 1);
        $propertyIndent = $baseIndent . $ctx->indent;

        $ts = "{\n";

        foreach ($this->properties as $name => $type) {
            $ts .= $propertyIndent . Utils::tsProp($name) . ': ' . $type->render($ctx) . "\n";
        }

        $ts .= $baseIndent . '}';

        return $ts;
    }
}
