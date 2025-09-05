<?php

declare(strict_types=1);

use Typographos\Config;
use Typographos\Generator;
use Typographos\Tests\Fixtures\Nullable;

afterEach(function (): void {
    if (file_exists('tests/nullable-generated.d.ts')) {
        unlink('tests/nullable-generated.d.ts');
    }
});

it('can generate nullable properties', function (): void {
    $config = (new Config())
        ->withFilePath('tests/nullable-generated.d.ts')
        ->withIndent('    ');

    new Generator($config)->generate([Nullable::class]);

    expect(file_get_contents('tests/nullable-generated.d.ts'))
        ->toBe(file_get_contents('tests/Expected/nullable.d.ts'));
});