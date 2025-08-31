<?php

declare(strict_types=1);

namespace Typographos\Tests\Fixtures;

class InvalidArrayMissingDocBlock
{
    public function __construct(
        public array $missingDocBlock,
    ) {}
}
