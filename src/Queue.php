<?php

declare(strict_types=1);

namespace PhpTs;

class Queue
{
    /**
     * @var array<class-string>
     */
    protected array $queue;

    protected array $visited;

    public function __construct(array $classNames)
    {
        $this->queue = $classNames;
        $this->visited = [];
    }

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
