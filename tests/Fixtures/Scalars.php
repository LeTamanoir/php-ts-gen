<?php

declare(strict_types=1);

namespace PhpTs\Tests\Fixtures;

class Scalars
{
    public function __construct(
        public string $string,
        public int $int,
        public float $float,
        public bool $bool,
        public object $object,
        public mixed $mixed,
        public null $null,
        public true $true,
        public false $false,
        public $noType,
    ) {}
}
