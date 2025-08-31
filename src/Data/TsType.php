<?php

declare(strict_types=1);

namespace PhpTs\Data;

interface TsType
{
    public function render(RenderCtx $ctx): string;
}
