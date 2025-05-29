<?php

declare(strict_types=1);

namespace PhpTs\Parser\Nodes;

/**
 * Context for converting a TypeNode to TypeScript
 */
readonly class ToTypeScriptContext
{
    public function __construct(
        public ?TypeNode $parent_type = null,
        public int $depth = 0,
    ) {}

    public static function initial(): self
    {
        return new self(
            parent_type: null,
            depth: 0,
        );
    }
}
