<?php

declare(strict_types=1);

namespace Typographos\Dto;

use InvalidArgumentException;
use Override;
use Typographos\Interfaces\TypeScriptTypeInterface;

enum ScalarType implements TypeScriptTypeInterface
{
    case boolean;
    case number;
    case unknown;
    case object;
    case null;
    case undefined;
    case string;
    case true;
    case false;
    case any;
    case never;

    public static function from(string $phpScalar): self
    {
        return match ($phpScalar) {
            'int', 'float' => self::number,
            'string' => self::string,
            'bool' => self::boolean,
            'object' => self::object,
            'mixed' => self::any,
            'null' => self::null,
            'true' => self::true,
            'false' => self::false,
            default => throw new InvalidArgumentException('Unsupported scalar type ' . $phpScalar),
        };
    }

    #[Override]
    public function render(RenderCtx $ctx): string
    {
        return $this->name;
    }
}
