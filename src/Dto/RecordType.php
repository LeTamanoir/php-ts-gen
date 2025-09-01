<?php

declare(strict_types=1);

namespace Typographos\Dto;

use Typographos\Interfaces\TypeScriptType;
use Typographos\Utils;

final class RecordType implements TypeScriptType
{
    /**
     * @param  array<string, TypeScriptType>  $properties
     */
    public function __construct(
        public string $name,
        private array $properties = [],
    ) {}

    public function addProperty(string $name, TypeScriptType $type)
    {
        $this->properties[$name] = $type;
    }

    public function render(RenderCtx $ctx): string
    {
        $ts = '';
        $idt = str_repeat($ctx->indent, $ctx->depth);

        foreach ($this->properties as $name => $type) {
            $ts .= $idt.Utils::tsProp($name).': '.$type->render($ctx)."\n";
        }

        return $ts;
    }
}
