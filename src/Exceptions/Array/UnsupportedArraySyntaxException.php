<?php

namespace PhpTs\Exceptions\Array;

use ReflectionProperty;

class UnsupportedArraySyntaxException extends ArrayExceptionBase
{
    public function __construct(ReflectionProperty $property)
    {
        parent::__construct('Unsupported array syntax', $property);
    }
}
