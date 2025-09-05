<?php

declare(strict_types=1);

use Typographos\Generator;
use Typographos\Tests\Fixtures\Scalars;

afterEach(function (): void {
    if (file_exists('tests/scalars-generated.d.ts')) {
        unlink('tests/scalars-generated.d.ts');
    }
});

it('can generate scalars', function (): void {
    new Generator()
        ->outputTo('tests/scalars-generated.d.ts')
        ->withIndent('    ')
        ->generate([Scalars::class]);

    expect(file_get_contents('tests/scalars-generated.d.ts'))->toBe(file_get_contents('tests/Expected/scalars.d.ts'));
});

it('can use type replacer', function (): void {
    new Generator()
        ->outputTo('tests/scalars-generated.d.ts')
        ->withIndent('    ')
        ->withTypeReplacement('int', 'custom_raw_typescript_type')
        ->generate([Scalars::class]);

    expect(file_get_contents('tests/scalars-generated.d.ts'))
        ->toBe(file_get_contents('tests/Expected/replacements.d.ts'));
});

it('can use custom indent', function (): void {
    new Generator()
        ->outputTo('tests/scalars-generated.d.ts')
        ->withIndent(' - ')
        ->generate([Scalars::class]);

    expect(file_get_contents('tests/scalars-generated.d.ts'))->toBe(file_get_contents('tests/Expected/indent.d.ts'));
});
