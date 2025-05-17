<?php

declare(strict_types=1);

namespace PhpTs\Parser\Nodes;

/**
 * Base class for all type nodes.
 */
abstract readonly class TypeNode
{
    /**
     * Convert this node to its TypeScript representation
     */
    abstract public function toTypeScript(?TypeNode $parent_type = null): string;

    /**
     * Optimize this node to its most basic form.
     */
    public function optimize(): TypeNode
    {
        return $this;
    }
}
