<?php

declare(strict_types=1);

namespace PhpTs\Tests\Fixtures;

class References
{
    public function __construct(
        public self $selfRef,
    ) {}
}
