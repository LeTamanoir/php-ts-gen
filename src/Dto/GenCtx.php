<?php

declare(strict_types=1);

namespace Typographos\Dto;

use ReflectionProperty;
use Typographos\Queue;

final class GenCtx
{
    /**
     * @param  array<string, string>  $typeReplacements
     */
    public function __construct(
        public Queue $queue = new Queue([]),
        public array $typeReplacements = [],
        public null|ReflectionProperty $parentProperty = null,
    ) {}
}
