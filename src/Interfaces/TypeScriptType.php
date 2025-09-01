<?php

declare(strict_types=1);

namespace Typographos\Interfaces;

use Typographos\Dto\RenderCtx;

interface TypeScriptType
{
    public function render(RenderCtx $ctx): string;
}
