<?php

declare(strict_types=1);

namespace Typographos\Dto;

enum ArrayKind
{
    case List;
    case NonEmptyList;
    case IndexString;
}
