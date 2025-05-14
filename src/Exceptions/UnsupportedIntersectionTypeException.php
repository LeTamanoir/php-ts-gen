<?php

namespace PhpTs\Exceptions;

use ReflectionProperty;

class UnsupportedIntersectionTypeException extends PhpTsException
{
    public function __construct(ReflectionProperty $property)
    {
        $message = sprintf(
            'Intersection type not supported for %s',
            $this->getPropertyDebugInfo($property)
        );

        parent::__construct($message);
    }
}
