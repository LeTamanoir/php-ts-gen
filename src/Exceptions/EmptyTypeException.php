<?php

namespace PhpTs\Exceptions;

use ReflectionProperty;

class EmptyTypeException extends PhpTsException
{
    public function __construct(ReflectionProperty $property)
    {
        $message = sprintf(
            'Empty type in @param tag for %s',
            $this->getPropertyDebugInfo($property)
        );

        parent::__construct($message);
    }
}
