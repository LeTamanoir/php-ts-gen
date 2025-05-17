<?php

namespace PhpTs\Exceptions;

use ReflectionProperty;

class UnsupportedArrayKeyTypeException extends PhpTsException
{
    public function __construct(string $key_type, ReflectionProperty $property)
    {
        $message = sprintf(
            'Array key type [%s] not supported for %s',
            $key_type,
            $this->getPropertyDebugInfo($property)
        );

        parent::__construct($message);
    }
}
