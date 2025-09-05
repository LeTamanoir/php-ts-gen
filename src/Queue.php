<?php

declare(strict_types=1);

namespace Typographos;

final class Queue
{
    /**
     * @var array<class-string, mixed>
     */
    private array $queue;

    /**
     * @var array<class-string, bool>
     */
    private array $visited;

    /**
     * @param  class-string[]  $classNames
     */
    public function __construct(array $classNames = [])
    {
        $this->queue = array_flip($classNames);
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

        $this->queue[$className] = true;
    }

    /**
     * @return class-string|null
     */
    public function shift(): string|null
    {
        $className = array_key_first($this->queue);

        if ($className !== null) {
            unset($this->queue[$className]);
            $this->visited[$className] = true;
        }

        return $className;
    }
}
