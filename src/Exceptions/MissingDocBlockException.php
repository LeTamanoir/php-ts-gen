<?php

namespace PhpTs\Exceptions;

use ReflectionProperty;

class MissingDocBlockException extends PhpTsException
{
    public function __construct(ReflectionProperty $property)
    {
        $message = sprintf(
            'Missing constructor @param tag for %s',
            $this->getPropertyDebugInfo($property)
        );

        parent::__construct($message);
    }
}
