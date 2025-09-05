<?php

declare(strict_types=1);

use Typographos\Generator;
use Typographos\Tests\Fixtures\Nullable;

afterEach(function (): void {
    if (file_exists('tests/nullable-generated.d.ts')) {
        unlink('tests/nullable-generated.d.ts');
    }
});

it('can generate nullable properties', function (): void {
    new Generator()
        ->outputTo('tests/nullable-generated.d.ts')
        ->withIndent('    ')
        ->generate([Nullable::class]);

    expect(file_get_contents('tests/nullable-generated.d.ts'))
        ->toBe(file_get_contents('tests/Expected/nullable.d.ts'));
});
