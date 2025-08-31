<?php

declare(strict_types=1);

namespace Typographos\Tests\Fixtures;

class InvalidArrayVarDocBlock
{
    public function __construct(
        /** @invalid-var-tag */
        public array $invalidVarDocBlock,
    ) {}
}
