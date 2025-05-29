<?php

namespace PhpTs;

use PhpTs\Parser\Nodes\ToTypeScriptContext;
use PhpTs\Parser\Nodes\TypeNode;
use PhpTs\Parser\TypeParser;

class Generator
{
    /**
     * Generate TypeScript types from PHP classes
     *
     * @param  list<class-string>  $dtos  Array of class names to generate types for
     */
    public static function generate(array $dtos): string
    {
        // Remove duplicates
        $dtos = array_unique($dtos);

        $parser = new TypeParser;

        $nodes = array_map(fn (string $dto) => $parser->parseClass($dto), $dtos);

        $types = array_map(fn (TypeNode $node) => $node->toTypeScript(ToTypeScriptContext::initial()), $nodes);

        return implode(PHP_EOL, $types);
    }
}
