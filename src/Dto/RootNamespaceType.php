<?php

declare(strict_types=1);

namespace Typographos\Dto;

use Override;
use Typographos\Interfaces\TypeScriptTypeInterface;
use Typographos\Traits\HasChildrenTrait;
use Typographos\Utils;

final class RootNamespaceType implements TypeScriptTypeInterface
{
    /** @use HasChildrenTrait<RecordType|NamespaceType> */
    use HasChildrenTrait;

    public static function from(GenCtx $ctx): self
    {
        $root = new self();

        // process all classes in queue (queue may grow during processing)
        while ($ctx->queue->isNotEmpty()) {
            $className = $ctx->queue->shift();

            // extract namespace: App\DTO\User → App\DTO
            $namespace = substr($className, 0, strrpos($className, '\\'));

            $record = RecordType::from($ctx, $className);

            $root->addRecord($namespace, $record);
        }

        return $root;
    }

    public function addRecord(string $namespace, RecordType $record): void
    {
        $parts = Utils::fqcnParts($namespace);

        /** @var RootNamespaceType|NamespaceType $node */
        $node = $this;
        foreach ($parts as $part) {
            $nsKey = 'NamespaceType::' . $part;

            $existingChild = $node->getChild($nsKey);

            if ($existingChild === null) {
                $newNamespace = new NamespaceType($part);

                $node->addChild($nsKey, $newNamespace);

                $node = $newNamespace;
            } else {
                $node = $existingChild;
            }
        }

        $node->addChild('RecordType::' . $record->name, $record);
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
