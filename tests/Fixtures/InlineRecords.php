<?php

declare(strict_types=1);

namespace Typographos\Tests\Fixtures;

use Typographos\Attributes\InlineType;

final class InlineRecords
{
    public function __construct(
        #[InlineType]
        public Scalars $inlineScalars,
        public Scalars $scalars,
    ) {}
}
