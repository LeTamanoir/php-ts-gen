<?php

declare(strict_types=1);

namespace Typographos\Dto;

use Override;
use Typographos\Interfaces\TypeScriptTypeInterface;
use Typographos\Traits\HasChildrenTrait;
use Typographos\Utils;

/**
 * @api
 */
final class RootNamespaceType implements TypeScriptTypeInterface
{
    /** @use HasChildrenTrait<RecordType|NamespaceType> */
    use HasChildrenTrait;

    public function addRecord(string $namespace, RecordType $record): void
    {
        $this->findNamespace($namespace)->addChild('RecordType::' . $record->name, $record);
    }

    private function findNamespace(string $namespace): RootNamespaceType|NamespaceType
    {
        $parts = Utils::fqcnParts($namespace);

        /** @var RootNamespaceType|NamespaceType $node */
        $node = $this;
        foreach ($parts as $part) {
            $nsKey = 'NamespaceType::' . $part;

            /** @var NamespaceType|null $existingChild */
            $existingChild = $node->getChild($nsKey);
            if ($existingChild === null) {
                $newNamespace = new NamespaceType($part);
                $node->addChild($nsKey, $newNamespace);
                $node = $newNamespace;
                continue;
            }
            $node = $existingChild;
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
