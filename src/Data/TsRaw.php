<?php

declare(strict_types=1);

namespace Typographos\Data;

final class TsRaw implements TsType
{
    public function __construct(public string $rawExpr) {}

    public function render(RenderCtx $ctx): string
    {
        return $this->rawExpr;
    }
}
