<?php

declare(strict_types=1);

namespace Typographos\Tests\Fixtures;

class InvalidArrayNonEmptyList
{
    public function __construct(
        /** @var non-empty-list<int, int, int> */
        public array $invalidNonEmptyList,
    ) {}
}
