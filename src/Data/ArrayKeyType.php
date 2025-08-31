<?php

declare(strict_types=1);

namespace PhpTs\Data;

enum ArrayKeyType
{
    case int;
    case string;
    case both;
    case other;
}
