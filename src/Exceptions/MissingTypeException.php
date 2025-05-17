<?php

namespace PhpTs\Exceptions;

use ReflectionProperty;

class MissingTypeException extends PhpTsException
{
    public function __construct(ReflectionProperty $property)
    {
        $message = sprintf(
            'Missing type for %s',
            $this->getPropertyDebugInfo($property)
        );

        parent::__construct($message);
    }
}
