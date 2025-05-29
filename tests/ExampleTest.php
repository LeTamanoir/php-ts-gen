<?php

use PhpTs\Generator;

class Dto
{
    /**
     * @param  string[]  $tags
     * @param  array<string, Dto>  $zip_codes
     */
    public function __construct(
        public string $name,
        public Dto $friend,
        public int $age,
        public bool $isActive,
        public string|bool $strange,
        public ?array $tags,
        public ?string $email,
        public ?string $phone,
        public ?string $address,
        public ?string $city,
        public ?string $state,
        public array $zip_codes,
        public ?string $country,
    ) {}
}

class Dto2
{
    public function __construct(
        public Dto $friend,
        public ?string $country,
    ) {}
}

it('can test', function () {

    $types = Generator::generate([
        Dto::class,
        Dto2::class,
    ]);

    dd($types);

});
