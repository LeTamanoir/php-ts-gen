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
final class RecordType implements TypeScriptTypeInterface
{
    /**
     * @use HasPropertiesTrait<TypeScriptTypeInterface>
     */
    use HasPropertiesTrait;

    public function __construct(
        public string $name,
    ) {}

    #[Override]
    public function render(RenderCtx $ctx): string
    {
        $baseIndent = str_repeat($ctx->indent, $ctx->depth);
        $propertyIndent = $baseIndent . $ctx->indent;

        $ts = $baseIndent . 'export interface ' . $this->name . " {\n";

        foreach ($this->properties as $name => $type) {
            $ts .= $propertyIndent . Utils::tsProp($name) . ': ' . $type->render($ctx) . "\n";
        }

        $ts .= $baseIndent . "}\n";

        return $ts;
    }
}
