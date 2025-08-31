<?php

declare(strict_types=1);

namespace PhpTs\Tests\Fixtures;

use PhpTs\Attributes\TypeScript;

#[TypeScript]
class Attributes
{
    public function __construct(
        public string $sample,
    ) {}
}
