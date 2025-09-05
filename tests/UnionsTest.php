<?php

declare(strict_types=1);

use Typographos\Generator;
use Typographos\Tests\Fixtures\Unions;

afterEach(function (): void {
    if (file_exists('tests/unions-generated.d.ts')) {
        unlink('tests/unions-generated.d.ts');
    }
});

it('can generate unions', function (): void {
    new Generator()
        ->outputTo('tests/unions-generated.d.ts')
        ->withIndent('    ')
        ->generate([Unions::class]);

    expect(file_get_contents('tests/unions-generated.d.ts'))->toBe(file_get_contents('tests/Expected/unions.d.ts'));
});
