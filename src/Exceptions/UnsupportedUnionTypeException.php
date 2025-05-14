<?php

namespace PhpTs\Exceptions;

use ReflectionProperty;

class UnsupportedUnionTypeException extends PhpTsException
{
    public function __construct(ReflectionProperty $property)
    {
        $message = sprintf(
            'Union type not supported for %s',
            $this->getPropertyDebugInfo($property)
        );

        parent::__construct($message);
    }
}
