<?php

declare(strict_types=1);

namespace Typographos\Tests\Fixtures;

class InvalidArrayArrayKey
{
    public function __construct(
        /** @var array<float, string> */
        public array $invalidArrayKey,
    ) {}
}
