<?php

declare(strict_types=1);

namespace PhpTs\Parser;

/**
 * Base class for all type nodes.
 */
abstract readonly class TypeNode
{
    /**
     * Convert this node to its TypeScript representation
     */
    abstract public function toTypeScript(ToTypeScriptContext $context): string;
}
