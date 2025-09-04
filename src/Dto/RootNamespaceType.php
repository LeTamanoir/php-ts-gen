<?php

declare(strict_types=1);

namespace Typographos\Dto;

use Override;
use Typographos\Interfaces\TypeScriptType;
use Typographos\Traits\HasChildren;
use Typographos\Utils;

/**
 * @api
 */
final class RootNamespaceType implements TypeScriptType
{
    /** @use HasChildren<RecordType|NamespaceType> */
    use HasChildren;

    public function addRecord(string $namespace, RecordType $record): void
    {
        $this->findNamespace($namespace)->addChild('RecordType::'.$record->name, $record);
    }

    private function findNamespace(string $namespace): RootNamespaceType|NamespaceType
    {
        $parts = Utils::fqcnParts($namespace);

        /** @var RootNamespaceType|NamespaceType */
        $node = $this;
        foreach ($parts as $part) {
            $nsKey = 'NamespaceType::'.$part;
            if (! $node->getChild($nsKey)) {
                $node->addChild($nsKey, new NamespaceType($part));
            }
            $node = $node->getChild($nsKey);
        }

        return $node;
    }

    #[Override]
    public function render(RenderCtx $ctx): string
    {
        $ts = '';
        foreach ($this->children as $child) {
            $ts .= $child->render($ctx);
        }

        return $ts;
    }
}
