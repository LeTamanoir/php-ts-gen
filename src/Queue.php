<?php

declare(strict_types=1);

namespace Typographos;

/**
 * @internal
 */
final class Queue
{
    /**
     * @var class-string[]
     */
    private array $queue;

    /**
     * @var array<class-string, bool>
     */
    private array $visited;

    /**
     * @param  class-string[]  $classNames
     */
    public function __construct(array $classNames)
    {
        $this->queue = $classNames;
        $this->visited = [];
    }

    /**
     * @param  class-string  $className
     */
    public function enqueue(string $className): void
    {
        if (isset($this->visited[$className])) {
            return;
        }

        $this->queue[] = $className;
    }

    public function shift(): ?string
    {
        $className = array_shift($this->queue);
        if ($className) {
            $this->visited[$className] = true;
        }

        return $className;
    }
}
