<?php

declare(strict_types=1);

namespace Typographos\Tests\Fixtures;

class References
{
    public function __construct(
        public self $selfRef,
    ) {}
}
