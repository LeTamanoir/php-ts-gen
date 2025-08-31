<?php

declare(strict_types=1);

namespace Typographos\Tests\Fixtures;

class InvalidArrayParamDocBlock
{
    /**
     * @param invalid-type
     */
    public function __construct(
        public array $invalidParamDocBlock,
    ) {}
}
