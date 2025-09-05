<?php

declare(strict_types=1);

namespace Typographos\Dto;

use Override;
use Typographos\Interfaces\TypeScriptTypeInterface;
use Typographos\Utils;

/**
 * ReferenceType represents a reference to another TypeScript type.
 */
final class ReferenceType implements TypeScriptTypeInterface
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
