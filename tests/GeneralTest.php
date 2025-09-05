<?php

declare(strict_types=1);

use Typographos\Generator;
use Typographos\Tests\Fixtures\Scalars;

afterEach(function (): void {
    $files = [
        'tests/custom-path.d.ts',
        'tests/discovery-chain-generated.d.ts',
        'tests/fluent-type-replacement.d.ts',
        'tests/broken-file.d.ts',
    ];

    foreach ($files as $file) {
        if (file_exists($file)) {
            unlink($file);
        }
    }
});

it('can generate to a custom path', function (): void {
    new Generator()
        ->outputTo('tests/custom-path.d.ts')
        ->withIndent('    ')
        ->generate([Scalars::class]);

    expect(file_get_contents('tests/custom-path.d.ts'))->not->toBeEmpty();
});

it('can use auto-discovery', function (): void {
    new Generator()
        ->outputTo('tests/discovery-chain-generated.d.ts')
        ->withIndent('    ')
        ->discoverFrom(__DIR__ . '/Fixtures')
        ->generate();

    expect(file_get_contents('tests/discovery-chain-generated.d.ts'))
        ->toBe(file_get_contents('tests/Expected/attributes.d.ts'));
});

it('cannot discover from broken directory', function (): void {
    expect(
        fn() => new Generator()
            ->outputTo('tests/discovery-chain-generated.d.ts')
            ->withIndent('    ')
            ->discoverFrom(__DIR__ . '/unknown')
            ->generate(),
    )
        ->toThrow(RuntimeException::class, 'Auto discover directory not found: ' . __DIR__ . '/unknown');
});

it('cannot generate nothing', function (): void {
    expect(
        fn() => new Generator()
            ->outputTo('tests/discovery-chain-generated.d.ts')
            ->withIndent('    ')
            ->generate(),
    )
        ->toThrow(InvalidArgumentException::class, 'No classes to generate');
});

it('cannot write to broken destination', function (): void {
    touch('tests/broken-file.d.ts');
    chmod('tests/broken-file.d.ts', 0);

    expect(fn() => new Generator()
        ->outputTo('tests/broken-file.d.ts')
        ->withIndent('    ')
        ->generate([Scalars::class]))
        ->toThrow(RuntimeException::class, 'Failed to write generated types to file tests/broken-file.d.ts');
});

it('can use fluent constructor', function (): void {
    $generator = new Generator();
    expect($generator)->toBeInstanceOf(Generator::class);
});

it('can use fluent withTypeReplacement method', function (): void {
    $generator = new Generator();

    $generator
        ->withTypeReplacement('int', 'number')
        ->outputTo('tests/fluent-type-replacement.d.ts')
        ->generate([Scalars::class]);

    expect(file_get_contents('tests/fluent-type-replacement.d.ts'))->toContain('number');
});

it('can use discoverFrom fluent interface', function (): void {
    $generator = new Generator();

    $generator
        ->discoverFrom(__DIR__ . '/Fixtures')
        ->withIndent('    ')
        ->outputTo('tests/discovery-chain-generated.d.ts');

    expect($generator)->toBeInstanceOf(Generator::class);

    // Generate to verify the chain worked
    $generator->generate([Scalars::class]);
    expect(file_exists('tests/discovery-chain-generated.d.ts'))->toBeTrue();
});
