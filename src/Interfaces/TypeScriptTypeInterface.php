<?php

declare(strict_types=1);

namespace Typographos\Interfaces;

use Typographos\Dto\RenderCtx;

interface TypeScriptTypeInterface
{
    public function render(RenderCtx $ctx): string;
}
