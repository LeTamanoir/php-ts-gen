<?php

namespace PhpTs\Exceptions;

use ReflectionProperty;

class UnknownBuiltinTypeException extends PhpTsException
{
    public function __construct(string $type_name, ReflectionProperty $property)
    {
        $message = sprintf(
            'Unknown builtin type [%s] for %s',
            $type_name,
            $this->getPropertyDebugInfo($property)
        );

        parent::__construct($message);
    }
}
