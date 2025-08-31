<?php

declare(strict_types=1);

namespace PhpTs;

class Queue
{
    /**
     * @var array<class-string>
     */
    protected array $queue;

    public function __construct(array $classNames)
    {
        $this->queue = $classNames;
    }

    public function enqueue(string $className): void
    {
        $this->queue[] = $className;
    }

    public function shift(): ?string
    {
        return array_shift($this->queue);
    }
}
