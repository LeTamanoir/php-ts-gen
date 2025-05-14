<?php

namespace PhpTs\Exceptions;

use Exception;
use ReflectionClass;
use ReflectionProperty;

/**
 * Base class for all exceptions
 */
abstract class PhpTsException extends Exception
{
    /**
     * Get formatted property debug info with clickable file path for IDEs
     */
    protected function getPropertyDebugInfo(ReflectionProperty $property): string
    {
        $class = $property->getDeclaringClass();

        return sprintf(
            'property [%s] in class [%s] at %s:%d',
            $property->getName(),
            $class->getName(),
            $class->getFileName(),
            $class->getStartLine()
        );
    }

    /**
     * Get formatted class debug info with clickable file path for IDEs
     */
    protected function getClassDebugInfo(ReflectionClass $class): string
    {
        return sprintf(
            'class [%s] at %s:%d',
            $class->getName(),
            $class->getFileName(),
            $class->getStartLine()
        );
    }
}
