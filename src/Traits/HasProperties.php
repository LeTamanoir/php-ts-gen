<?php

declare(strict_types=1);

namespace Typographos\Traits;

use InvalidArgumentException;

/**
 * @template T
 */
trait HasProperties
{
    /**
     * @var array<string, T>
     */
    private array $properties = [];

    /**
     * @param  T  $property
     */
    public function addProperty(string $propertyKey, $property): self
    {
        if (isset($this->properties[$propertyKey])) {
            throw new InvalidArgumentException('Property '.$propertyKey.' already exists');
        }

        $this->properties[$propertyKey] = $property;

        return $this;
    }
}
