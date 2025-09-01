<?php

declare(strict_types=1);

namespace Typographos\Dto;

use Typographos\Interfaces\TypeScriptType;
use Typographos\Utils;

final class ReferenceType implements TypeScriptType
{
    public function __construct(
        public string $className,
    ) {}

    public function render(RenderCtx $ctx): string
    {
        return implode('.', array_map(Utils::tsIdent(...), array_filter(explode('\\', $this->className))));
    }
}
