<?php

declare(strict_types=1);

namespace Typographos\Tests\Fixtures;

use DateTime;

class Nullable
{
    public function __construct(
        public null|string $maybeString,
        public null|int $maybeInt,
        public null|self $maybeSelf,
        public null|DateTime $maybeDate,
    ) {}
}
