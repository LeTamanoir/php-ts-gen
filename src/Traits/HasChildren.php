<?php

declare(strict_types=1);

namespace Typographos\Traits;

use InvalidArgumentException;

/**
 * @template T
 */
trait HasChildren
{
    /**
     * @var array<string, T>
     */
    private array $children = [];

    /**
     * @return T|null
     */
    public function getChild(string $childKey)
    {
        return $this->children[$childKey] ?? null;
    }

    /**
     * @param  T  $type
     */
    public function addChild(string $childKey, $type): self
    {
        if (isset($this->children[$childKey])) {
            throw new InvalidArgumentException('Child '.$childKey.' already exists');
        }

        $this->children[$childKey] = $type;

        return $this;
    }
}
