<?php

declare(strict_types=1);

namespace Typographos\Tests\Fixtures;

use Typographos\Attributes\TypeScript;

#[TypeScript]
class Attributes
{
    public function __construct(
        public string $sample,
    ) {}
}
