<?php

namespace PhpTs\Exceptions;

use ReflectionProperty;

class MissingParamTagException extends PhpTsException
{
    public function __construct(ReflectionProperty $property)
    {
        $message = sprintf(
            'Missing @param tag for %s',
            $this->getPropertyDebugInfo($property)
        );

        parent::__construct($message);
    }
}
