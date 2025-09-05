<?php

declare(strict_types=1);

require_once __DIR__ . '/Fixtures/InlineRecords.php';

use Typographos\Config;
use Typographos\Generator;
use Typographos\Tests\Fixtures\CompanyWithMixedAddresses;
use Typographos\Tests\Fixtures\UserWithInlineAddress;

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
        ->generate([UserWithInlineAddress::class]);

    expect(file_get_contents('tests/inline-generated.d.ts'))->toBe(file_get_contents('tests/Expected/inline.d.ts'));
});

it('can mix inline and reference records', function (): void {
    new Generator()
        ->withIndent("\t")
        ->outputTo('tests/mixed-inline-generated.d.ts')
        ->generate([CompanyWithMixedAddresses::class]);

    expect(file_get_contents('tests/mixed-inline-generated.d.ts'))
        ->toBe(file_get_contents('tests/Expected/mixed-inline.d.ts'));
});
