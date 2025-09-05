<?php

declare(strict_types=1);

namespace Typographos\Traits;

use InvalidArgumentException;

/**
 * @template T
 */
trait HasChildrenTrait
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
     *
     * @throws InvalidArgumentException
     */
    public function addChild(string $childKey, mixed $type): self
    {
        if ($childKey === '') {
            throw new InvalidArgumentException('Child key cannot be empty');
        }

        if (isset($this->children[$childKey])) {
            throw new InvalidArgumentException('Child ' . $childKey . ' already exists');
        }

        $this->children[$childKey] = $type;

        return $this;
    }
}
