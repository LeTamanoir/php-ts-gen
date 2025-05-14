<?php

namespace PhpTs\Exceptions;

use ReflectionProperty;

class UnsupportedTypeException extends PhpTsException
{
    public function __construct(string $type_name, ReflectionProperty $property)
    {
        $message = sprintf(
            '[%s] type not supported for %s',
            $type_name,
            $this->getPropertyDebugInfo($property)
        );

        parent::__construct($message);
    }
}
