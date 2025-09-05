<?php

declare(strict_types=1);

use Typographos\Generator;
use Typographos\Tests\Fixtures\Arrays;
use Typographos\Tests\Fixtures\InvalidArrayArray;
use Typographos\Tests\Fixtures\InvalidArrayArrayKey;
use Typographos\Tests\Fixtures\InvalidArrayArrayType;
use Typographos\Tests\Fixtures\InvalidArrayList;
use Typographos\Tests\Fixtures\InvalidArrayMissingDocBlock;
use Typographos\Tests\Fixtures\InvalidArrayNonEmptyList;
use Typographos\Tests\Fixtures\InvalidArrayParamDocBlock;
use Typographos\Tests\Fixtures\InvalidArrayVarDocBlock;

afterEach(function (): void {
    if (file_exists('tests/arrays-generated.d.ts')) {
        unlink('tests/arrays-generated.d.ts');
    }
});

it('can generate arrays', function (): void {
    new Generator()
        ->outputTo('tests/arrays-generated.d.ts')
        ->withIndent('    ')
        ->generate([Arrays::class]);

    expect(file_get_contents('tests/arrays-generated.d.ts'))->toBe(file_get_contents('tests/Expected/arrays.d.ts'));
});

it('can handle invalid arrays', function (): void {
    $gen = new Generator()
        ->outputTo('tests/arrays-generated.d.ts')
        ->withIndent('    ');

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
