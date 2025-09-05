<?php

declare(strict_types=1);

use Typographos\Generator;
use Typographos\Tests\Fixtures\Intersections;

it('cannot generate intersections', function (): void {
    expect(fn () => new Generator()
        ->outputTo('tests/intersections-generated.d.ts')
        ->withIndent('    ')
        ->generate([Intersections::class]))
        ->toThrow(InvalidArgumentException::class, 'Intersection types are not supported');
});
