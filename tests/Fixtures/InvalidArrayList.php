<?php

declare(strict_types=1);

namespace PhpTs\Tests\Fixtures;

class InvalidArrayList
{
    public function __construct(
        /** @var list<int, int, int> */
        public array $invalidList,
    ) {}
}
