<?php

declare(strict_types=1);

namespace PhpTs\Tests\Fixtures;

class Intersections
{
    public function __construct(
        public \Iterator&\Countable $iterableAndCountable,
    ) {}
}
