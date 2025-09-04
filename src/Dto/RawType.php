<?php

declare(strict_types=1);

namespace Typographos\Dto;

use Override;
use Typographos\Interfaces\TypeScriptType;

/**
 * @api
 */
final class RawType implements TypeScriptType
{
    public function __construct(
        public string $rawExpr
    ) {}

    #[Override]
    public function render(RenderCtx $ctx): string
    {
        return $this->rawExpr;
    }
}
