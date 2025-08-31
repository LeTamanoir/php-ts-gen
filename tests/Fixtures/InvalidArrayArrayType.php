<?php

declare(strict_types=1);

namespace Typographos\Tests\Fixtures;

class InvalidArrayArrayType
{
    public function __construct(
        /** @var array */
        public array $invalidArrayType,
    ) {}
}
