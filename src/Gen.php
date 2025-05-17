<?php

namespace PhpTs;

use PhpTs\Parser\Nodes\TypeNode;
use PhpTs\Parser\TypeParser;

/**
 * Main entry point for PHP-TS generation
 */
class Gen
{
    /**
     * Generate TypeScript types from PHP classes
     *
     * @param  list<class-string>  $dtos  Array of class names to generate types for
     */
    public static function generate(array $dtos): string
    {
        if (count(array_unique($dtos)) !== count($dtos)) {
            throw new \Exception('Duplicate class names found');
        }

        $ast = [];

        foreach ($dtos as $dto) {
            $ast[] = TypeParser::parseClass($dto);
        }

        return implode(PHP_EOL, array_map(fn (TypeNode $node) => $node->toTypeScript(), $ast));
    }
}
