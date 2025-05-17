<?php

namespace PhpTs\Exceptions\Array;

use ReflectionProperty;

class UnsupportedArrayKeyTypeException extends ArrayExceptionBase
{
    public function __construct(string $key_type, ReflectionProperty $property)
    {
        parent::__construct(
            sprintf('Array key type [%s] not supported', $key_type),
            $property
        );
    }
}
