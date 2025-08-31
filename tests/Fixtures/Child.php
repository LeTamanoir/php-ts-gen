<?php

declare(strict_types=1);

namespace PhpTs\Tests\Fixtures;

class Child extends _Parent
{
    public function __construct(
        public parent $parent,
    ) {}
}
