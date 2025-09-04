<?php

declare(strict_types=1);

namespace Typographos\Dto;

/**
 * @api
 */
final class RenderCtx
{
    public function __construct(
        public string $indent,
        public int $depth,
    ) {}

    public function increaseDepth(): self
    {
        return new self($this->indent, $this->depth + 1);
    }
}
