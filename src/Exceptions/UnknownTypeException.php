<?php

namespace PhpTs\Exceptions;

use ReflectionProperty;

class UnknownTypeException extends PhpTsException
{
    public function __construct(ReflectionProperty $property)
    {
        $message = sprintf(
            'Unknown type for %s',
            $this->getPropertyDebugInfo($property)
        );

        parent::__construct($message);
    }
}
