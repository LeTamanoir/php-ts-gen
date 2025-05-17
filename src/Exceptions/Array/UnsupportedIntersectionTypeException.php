<?php

namespace PhpTs\Exceptions\Array;

use ReflectionProperty;

class UnsupportedIntersectionTypeException extends ArrayExceptionBase
{
    public function __construct(ReflectionProperty $property)
    {
        parent::__construct('Intersection type not supported in arrays', $property);
    }
}
