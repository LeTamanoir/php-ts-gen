<?php

declare(strict_types=1);

namespace PhpTs\Tests\Fixtures;

use DateTime;

class Nullable
{
    public function __construct(
        public ?string $maybeString,
        public ?int $maybeInt,
        public ?self $maybeSelf,
        public ?DateTime $maybeDate,
    ) {}
}
