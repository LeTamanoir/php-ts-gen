<?php

namespace PhpTs\Exceptions\Array;

use PhpTs\Exceptions\PhpTsException;
use ReflectionProperty;

/**
 * Base class for all array-related exceptions
 */
abstract class ArrayExceptionBase extends PhpTsException
{
    /**
     * Create a new array exception
     */
    public function __construct(string $message, ReflectionProperty $property)
    {
        parent::__construct(sprintf(
            '%s for %s',
            $message,
            $this->getPropertyDebugInfo($property)
        ));
    }
}
