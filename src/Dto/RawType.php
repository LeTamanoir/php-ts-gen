<?php

declare(strict_types=1);

namespace Typographos\Dto;

use Override;
use Typographos\Interfaces\TypeScriptTypeInterface;

final class RawType implements TypeScriptTypeInterface
{
    public function __construct(
        public string $rawExpr,
    ) {}

    #[Override]
    public function render(RenderCtx $ctx): string
    {
        return $this->rawExpr;
    }
}
