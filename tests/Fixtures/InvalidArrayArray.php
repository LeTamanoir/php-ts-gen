<?php

declare(strict_types=1);

namespace Typographos\Tests\Fixtures;

class InvalidArrayArray
{
    public function __construct(
        /** @var array<int, int, int, int> */
        public array $invalidArray,
    ) {}
}
