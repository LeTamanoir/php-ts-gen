<?php

declare(strict_types=1);

use Typographos\Config;
use Typographos\Generator;
use Typographos\Tests\Fixtures\Arrays;
use Typographos\Tests\Fixtures\Child;
use Typographos\Tests\Fixtures\Intersections;
use Typographos\Tests\Fixtures\InvalidArrayArray;
use Typographos\Tests\Fixtures\InvalidArrayArrayKey;
use Typographos\Tests\Fixtures\InvalidArrayArrayType;
use Typographos\Tests\Fixtures\InvalidArrayList;
use Typographos\Tests\Fixtures\InvalidArrayMissingDocBlock;
use Typographos\Tests\Fixtures\InvalidArrayNonEmptyList;
use Typographos\Tests\Fixtures\InvalidArrayParamDocBlock;
use Typographos\Tests\Fixtures\InvalidArrayVarDocBlock;
use Typographos\Tests\Fixtures\Nullable;
use Typographos\Tests\Fixtures\Scalars;
use Typographos\Tests\Fixtures\Unions;

afterEach(function (): void {
    if (file_exists('tests/generated.d.ts')) {
        unlink('tests/generated.d.ts');
    }
});

$config = new Config()
    ->withFilePath('tests/generated.d.ts')
    ->withIndent('    ');

it('can generate scalars', function () use ($config): void {
    new Generator($config)->generate([
        Scalars::class,
    ]);

    expect(file_get_contents('tests/generated.d.ts'))->toBe(file_get_contents('tests/Expected/scalars.d.ts'));
});

it('can generate unions', function () use ($config): void {
    new Generator($config)->generate([
        Unions::class,
    ]);

    expect(file_get_contents('tests/generated.d.ts'))->toBe(file_get_contents('tests/Expected/unions.d.ts'));
});

it('can\'t generate intersections', function () use ($config): void {
    expect(fn() => new Generator($config)->generate([Intersections::class]))
        ->toThrow(InvalidArgumentException::class, 'Intersection types are not supported');
});

it('can generate arrays', function () use ($config): void {
    new Generator($config)->generate([
        Arrays::class,
    ]);

    expect(file_get_contents('tests/generated.d.ts'))->toBe(file_get_contents('tests/Expected/arrays.d.ts'));
})->only();

it('can handle invalid arrays', function () use ($config): void {
    $gen = new Generator($config);

    expect(fn() => $gen->generate([InvalidArrayVarDocBlock::class]))
        ->toThrow(InvalidArgumentException::class, 'Malformed PHPDoc [/** @invalid-var-tag */]');

    expect(fn() => $gen->generate([InvalidArrayMissingDocBlock::class]))
        ->toThrow(InvalidArgumentException::class, 'Missing doc comment');

    expect(fn() => $gen->generate([InvalidArrayParamDocBlock::class]))
        ->toThrow(InvalidArgumentException::class, "Malformed PHPDoc [/**\n     * @param invalid-type\n     */]");

    expect(fn() => $gen->generate([InvalidArrayList::class]))
        ->toThrow(
            InvalidArgumentException::class,
            'Expected exactly one type argument when evaluating [list<int, int, int>]',
        );

    expect(fn() => $gen->generate([InvalidArrayNonEmptyList::class]))
        ->toThrow(
            InvalidArgumentException::class,
            'Expected exactly one type argument when evaluating [non-empty-list<int, int, int>]',
        );

    expect(fn() => $gen->generate([InvalidArrayArray::class]))
        ->toThrow(
            InvalidArgumentException::class,
            'Expected array<K,V> to have exactly two type args when evaluating [array<int, int, int, int>]',
        );

    expect(fn() => $gen->generate([InvalidArrayArrayType::class]))
        ->toThrow(InvalidArgumentException::class, 'Unsupported PHPDoc array type array');

    expect(fn() => $gen->generate([InvalidArrayArrayKey::class]))
        ->toThrow(InvalidArgumentException::class, 'Unsupported array key type [float]');
});

it('can use generate nullable properties', function () use ($config): void {
    new Generator($config)->generate([
        Nullable::class,
    ]);

    expect(file_get_contents('tests/generated.d.ts'))->toBe(file_get_contents('tests/Expected/nullable.d.ts'));
});

it('can handle specifi keywords', function () use ($config): void {
    new Generator($config)->generate([
        Child::class,
    ]);

    expect(file_get_contents('tests/generated.d.ts'))->toBe(file_get_contents('tests/Expected/child.d.ts'));
});

it('can use type replacer', function () use ($config): void {
    new Generator((clone $config)->withTypeReplacement(
        'int',
        'custom_raw_typescript_type',
    ))->generate([Scalars::class]);

    expect(file_get_contents('tests/generated.d.ts'))->toBe(file_get_contents('tests/Expected/replacements.d.ts'));
});

it('can use custom indent', function () use ($config): void {
    new Generator((clone $config)->withIndent(' - '))->generate([
        Scalars::class,
    ]);

    expect(file_get_contents('tests/generated.d.ts'))->toBe(file_get_contents('tests/Expected/indent.d.ts'));
});

it('can generate to a custom path', function () use ($config): void {
    new Generator((clone $config)->withFilePath('tests/custom-path.d.ts'))->generate([
        Scalars::class,
    ]);

    expect(file_get_contents('tests/custom-path.d.ts'))->not->toBeEmpty();

    unlink('tests/custom-path.d.ts');
});

it('can use auto-discovery', function () use ($config): void {
    new Generator((clone $config)->withAutoDiscoverDirectory(__DIR__ . '/Fixtures'))->generate();

    expect(file_get_contents('tests/generated.d.ts'))->toBe(file_get_contents('tests/Expected/attributes.d.ts'));
});

it('can\'t discover broken dir', function () use ($config): void {
    expect(fn() => new Generator((clone $config)->withAutoDiscoverDirectory(__DIR__ . '/unknown'))->generate())
        ->toThrow(RuntimeException::class, 'Auto discover directory not found: ' . __DIR__ . '/unknown');
});

it('can\'t generate nothing', function () use ($config): void {
    expect(fn() => new Generator($config)->generate())
        ->toThrow(InvalidArgumentException::class, 'No classes to generate');
});

it('can\'t write to broken destination', function () use ($config): void {
    touch('tests/broken-file.d.ts');

    chmod('tests/broken-file.d.ts', 0);

    expect(fn() => new Generator((clone $config)->withFilePath('tests/broken-file.d.ts'))->generate([Scalars::class]))
        ->toThrow(RuntimeException::class, 'Failed to write generated types to file tests/broken-file.d.ts');

    unlink('tests/broken-file.d.ts');
});

it('can use fluent create method', function (): void {
    $generator = Generator::create();

    expect($generator)->toBeInstanceOf(Generator::class);
});

it('can use fluent withTypeReplacement method', function (): void {
    $generator = Generator::create()
        ->withTypeReplacement('int', 'number')
        ->outputTo('tests/fluent-type-replacement.d.ts')
        ->generate([Scalars::class]);

    expect(file_get_contents('tests/fluent-type-replacement.d.ts'))->toContain('number');

    unlink('tests/fluent-type-replacement.d.ts');
});

it('can use discoverFrom fluent interface', function (): void {
    // Test fluent interface chaining with discoverFrom
    $generator = Generator::create()
        ->discoverFrom(__DIR__ . '/Fixtures')
        ->withIndent('    ')
        ->outputTo('tests/discovery-chain-generated.d.ts');

    expect($generator)->toBeInstanceOf(Generator::class);

    // Generate to verify the chain worked
    $generator->generate([Scalars::class]);
    expect(file_exists('tests/discovery-chain-generated.d.ts'))->toBeTrue();
    unlink('tests/discovery-chain-generated.d.ts');
});
