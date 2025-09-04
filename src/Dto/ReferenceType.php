<?php

declare(strict_types=1);

namespace Typographos\Dto;

use Override;
use Typographos\Interfaces\TypeScriptType;
use Typographos\Utils;

/**
 * ReferenceType represents a reference to another TypeScript type.
 *
 * @api
 */
final class ReferenceType implements TypeScriptType
{
    public function __construct(
        public string $fqcn,
    ) {}

    #[Override]
    public function render(RenderCtx $ctx): string
    {
        return Utils::tsFqcn($this->fqcn);
    }
}
