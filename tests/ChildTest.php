<?php

declare(strict_types=1);

use Typographos\Generator;
use Typographos\Tests\Fixtures\Child;

afterEach(function (): void {
    if (file_exists('tests/child-generated.d.ts')) {
        unlink('tests/child-generated.d.ts');
    }
});

it('can handle specific keywords', function (): void {
    new Generator()
        ->outputTo('tests/child-generated.d.ts')
        ->withIndent('    ')
        ->generate([Child::class]);

    expect(file_get_contents('tests/child-generated.d.ts'))
        ->toBe(file_get_contents('tests/Expected/child.d.ts'));
});