<?php

declare(strict_types=1);

namespace PhpTs\Tests\Fixtures;

class Unions
{
    public function __construct(
        public string|int $scalar,
        public string|self $scalarAndSelf,
        public \Countable|\Iterator $countableOrIterator,
    ) {}
}
