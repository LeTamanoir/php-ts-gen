<?php

declare(strict_types=1);

namespace PhpTs\Tests\Fixtures;

class InvalidArrayArrayType
{
    public function __construct(
        /** @var array */
        public array $invalidArrayType,
    ) {}
}
