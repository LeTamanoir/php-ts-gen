<?php

declare(strict_types=1);

namespace PhpTs\Data;

use PhpTs\Utils;

final class TsRecord implements TsType
{
    /**
     * @param  list<array{name: string, type: TsType}>  $properties
     */
    public function __construct(
        public string $name,
        private array $properties = [],
    ) {}

    public function addProperty(string $name, TsType $type)
    {
        $this->properties[] = [
            'name' => $name,
            'type' => $type,
        ];
    }

    public function render(RenderCtx $ctx): string
    {
        $ts = '';
        $idt = str_repeat($ctx->indent, $ctx->depth);

        foreach ($this->properties as $p) {
            $ts .= $idt.Utils::tsProp($p['name']).': '.$p['type']->render($ctx)."\n";
        }

        return $ts;
    }
}
