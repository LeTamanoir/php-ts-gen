<?php

declare(strict_types=1);

namespace Typographos\Traits;

use InvalidArgumentException;

/**
 * @template T
 */
trait HasPropertiesTrait
{
    /**
     * @var array<string, T>
     */
    private array $properties = [];

    /**
     * @param  T  $property
     */
    public function addProperty(string $propertyKey, mixed $property): self
    {
        if ($propertyKey === '') {
            throw new InvalidArgumentException('Property key cannot be empty');
        }

        if (isset($this->properties[$propertyKey])) {
            throw new InvalidArgumentException('Property ' . $propertyKey . ' already exists');
        }

        $this->properties[$propertyKey] = $property;

        return $this;
    }
}
