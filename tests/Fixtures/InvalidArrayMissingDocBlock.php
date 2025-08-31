<?php

declare(strict_types=1);

namespace PhpTs\Tests\Fixtures;

class InvalidArrayMissingDocBlock
{
    public function __construct(
        public array $missingDocBlock,
    ) {}
}
