<?php

namespace PhpTs\Exceptions;

use ReflectionProperty;

class UnsupportedArraySyntaxException extends PhpTsException
{
    public function __construct(ReflectionProperty $property)
    {
        $message = sprintf(
            'Only `[]` array syntax is supported for %s',
            $this->getPropertyDebugInfo($property)
        );

        parent::__construct($message);
    }
}
