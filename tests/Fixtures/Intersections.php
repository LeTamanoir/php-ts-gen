<?php

declare(strict_types=1);

namespace Typographos\Tests\Fixtures;

class Intersections
{
    public function __construct(
        public \Iterator&\Countable $iterableAndCountable,
    ) {}
}
