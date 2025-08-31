<?php

declare(strict_types=1);

namespace PhpTs\Data;

enum TsScalar implements TsType
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

    public function render(RenderCtx $ctx): string
    {
        return $this->name;
    }

    public static function from(string $phpScalar): self
    {
        return match ($phpScalar) {
            'int', 'float' => TsScalar::number,
            'string' => TsScalar::string,
            'bool' => TsScalar::boolean,
            'object' => TsScalar::object,
            'mixed' => TsScalar::any,
            'null' => TsScalar::null,
            default => TsScalar::unknown,
        };
    }
}
