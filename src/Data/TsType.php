<?php

declare(strict_types=1);

namespace Typographos\Data;

interface TsType
{
    public function render(RenderCtx $ctx): string;
}
