<?php

declare(strict_types=1);

use Typographos\Generator;
use Typographos\Tests\Fixtures\InlineRecords;

beforeEach(function (): void {
    if (file_exists('tests/inline-generated.d.ts')) {
        unlink('tests/inline-generated.d.ts');
    }
    if (file_exists('tests/mixed-inline-generated.d.ts')) {
        unlink('tests/mixed-inline-generated.d.ts');
    }
});

afterEach(function (): void {
    if (file_exists('tests/inline-generated.d.ts')) {
        unlink('tests/inline-generated.d.ts');
    }
    if (file_exists('tests/mixed-inline-generated.d.ts')) {
        unlink('tests/mixed-inline-generated.d.ts');
    }
});

it('can generate inline records', function (): void {
    new Generator()
        ->withIndent("\t")
        ->outputTo('tests/inline-generated.d.ts')
        ->generate([InlineRecords::class]);

    expect(file_get_contents('tests/inline-generated.d.ts'))->toBe(file_get_contents('tests/Expected/inline.d.ts'));
});
