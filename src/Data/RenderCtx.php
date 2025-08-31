<?php

declare(strict_types=1);

namespace PhpTs\Data;

final class RenderCtx
{
    public function __construct(
        public string $indent,
        public int $depth,
    ) {}

    public static function root(): self
    {
        return new self(indent: '', depth: 0);
    }

    public function increaseDepth(): self
    {
        return new self($this->indent, $this->depth + 1);
    }
}
