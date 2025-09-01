<?php

declare(strict_types=1);

namespace Typographos\Dto;

use Typographos\Interfaces\TypeScriptType;

final class RawType implements TypeScriptType
{
    public function __construct(public string $rawExpr) {}

    public function render(RenderCtx $ctx): string
    {
        return $this->rawExpr;
    }
}
