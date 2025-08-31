<?php

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

afterEach(function () {
    if (file_exists('tests/test.d.ts')) {
        unlink('tests/test.d.ts');
    }
});

$config = new Config()
    ->withFilePath('tests/test.d.ts')
    ->withIndent('    ');

it('can generate scalars', function () use ($config) {

    new Generator($config)
        ->generate(
            Scalars::class,
        );

    expect(file_get_contents('tests/test.d.ts'))->toBe(file_get_contents('tests/Expected/scalars.d.ts'));

});

it('can generate unions', function () use ($config) {

    new Generator($config)
        ->generate(
            Unions::class,
        );

    expect(file_get_contents('tests/test.d.ts'))->toBe(file_get_contents('tests/Expected/unions.d.ts'));

});

it('can generate intersections', function () use ($config) {

    new Generator($config)
        ->generate(
            Intersections::class,
        );

    expect(file_get_contents('tests/test.d.ts'))->toBe(file_get_contents('tests/Expected/intersections.d.ts'));

});

it('can generate arrays', function () use ($config) {

    new Generator($config)
        ->generate(
            Arrays::class,
        );

    expect(file_get_contents('tests/test.d.ts'))->toBe(file_get_contents('tests/Expected/arrays.d.ts'));

});

it('can handle invalid arrays', function () use ($config) {

    $gen = new Generator($config);

    expect(fn () => $gen->generate(InvalidArrayVarDocBlock::class))
        ->toThrow(InvalidArgumentException::class, 'Malformed PHPDoc [/** @invalid-var-tag */] for property $invalidVarDocBlock in ');

    expect(fn () => $gen->generate(InvalidArrayMissingDocBlock::class))
        ->toThrow(InvalidArgumentException::class, 'Missing doc comment for property $missingDocBlock in ');

    expect(fn () => $gen->generate(InvalidArrayParamDocBlock::class))
        ->toThrow(InvalidArgumentException::class, "Malformed PHPDoc [/**\n     * @param invalid-type\n     */] for property \$invalidParamDocBlock in ");

    expect(fn () => $gen->generate(InvalidArrayList::class))
        ->toThrow(InvalidArgumentException::class, 'Expected exactly one type argument when evaluating [list<int, int, int>] for property $invalidList in ');

    expect(fn () => $gen->generate(InvalidArrayNonEmptyList::class))
        ->toThrow(InvalidArgumentException::class, 'Expected exactly one type argument when evaluating [non-empty-list<int, int, int>] for property $invalidNonEmptyList in ');

    expect(fn () => $gen->generate(InvalidArrayArray::class))
        ->toThrow(InvalidArgumentException::class, 'Expected array<K,V> to have exactly two type args when evaluating [array<int, int, int, int>] for property $invalidArray in ');

    expect(fn () => $gen->generate(InvalidArrayArrayType::class))
        ->toThrow(InvalidArgumentException::class, 'Unsupported PHPDoc array type array for property $invalidArrayType in ');

    expect(fn () => $gen->generate(InvalidArrayArrayKey::class))
        ->toThrow(InvalidArgumentException::class, 'Unsupported array key type [float] for property $invalidArrayKey in ');
});

it('can use generate nullable properties', function () use ($config) {

    new Generator($config)
        ->generate(
            Nullable::class,
        );

    expect(file_get_contents('tests/test.d.ts'))->toBe(file_get_contents('tests/Expected/nullable.d.ts'));

});

it('can handle specifi keywords', function () use ($config) {

    new Generator($config)
        ->generate(
            Child::class,
        );

    expect(file_get_contents('tests/test.d.ts'))->toBe(file_get_contents('tests/Expected/child.d.ts'));

});

it('can use type replacer', function () use ($config) {

    new Generator((clone $config)->withTypeReplacement(
        'int', 'custom_raw_typescript_type'
    ))
        ->generate(
            Scalars::class,
        );

    expect(file_get_contents('tests/test.d.ts'))->toBe(file_get_contents('tests/Expected/replacements.d.ts'));

});

it('can use custom indent', function () use ($config) {

    new Generator((clone $config)->withIndent(
        ' - '
    ))
        ->generate(
            Scalars::class,
        );

    expect(file_get_contents('tests/test.d.ts'))->toBe(file_get_contents('tests/Expected/indent.d.ts'));

});

it('can generate to a custom path', function () use ($config) {

    new Generator((clone $config)->withFilePath(
        'tests/custom-path.d.ts'
    ))
        ->generate(
            Scalars::class,
        );

    expect(file_get_contents('tests/custom-path.d.ts'))->not->toBeEmpty();

    unlink('tests/custom-path.d.ts');

});

it('can use auto-discovery', function () use ($config) {

    new Generator((clone $config)->withAutoDiscoverDirectory(__DIR__.'/Fixtures'))
        ->generate();

    expect(file_get_contents('tests/test.d.ts'))->toBe(file_get_contents('tests/Expected/attributes.d.ts'));

});

it('can\'t discover broken dir', function () use ($config) {

    expect(fn () => new Generator((clone $config)->withAutoDiscoverDirectory(__DIR__.'/unknown'))->generate())
        ->toThrow(RuntimeException::class, 'Auto discover directory not found: '.__DIR__.'/unknown');

});

it('can\'t generate nothing', function () use ($config) {

    expect(fn () => new Generator($config)->generate())
        ->toThrow(InvalidArgumentException::class, 'No classes to generate');

});

it('can\'t write to broken destination', function () use ($config) {

    touch('tests/broken-file.d.ts');

    chmod('tests/broken-file.d.ts', 0);

    expect(fn () => new Generator((clone $config)->withFilePath('tests/broken-file.d.ts'))->generate(Scalars::class))
        ->toThrow(RuntimeException::class, 'Failed to write generated types to file tests/broken-file.d.ts');

    unlink('tests/broken-file.d.ts');

});
