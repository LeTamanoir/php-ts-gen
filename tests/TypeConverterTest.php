<?php

declare(strict_types=1);

// Test class for user-defined type testing
class UserDefinedTestClass
{
    public function __construct(
        public string $name,
    ) {}
}

use Typographos\Dto\GenCtx;
use Typographos\Dto\RawType;
use Typographos\Dto\ReferenceType;
use Typographos\Dto\ScalarType;
use Typographos\Dto\UnionType;
use Typographos\Queue;
use Typographos\TypeConverter;

it('handles empty string type', function (): void {
    $ctx = new GenCtx(new Queue([]), [], null);
    $result = TypeConverter::convert($ctx, '');

    expect($result)->toBeInstanceOf(ScalarType::class);
    expect($result->render(new \Typographos\Dto\RenderCtx('', 0)))->toBe('unknown');
});

it('handles nullable type replacements', function (): void {
    $typeReplacements = ['CustomType' => 'MyCustomType'];
    $ctx = new GenCtx(new Queue([]), $typeReplacements, null);

    $result = TypeConverter::convert($ctx, '?CustomType');

    expect($result)->toBeInstanceOf(UnionType::class);
    expect($result->render(new \Typographos\Dto\RenderCtx('', 0)))->toBe('MyCustomType | null');
});

it('handles basic type replacements', function (): void {
    $typeReplacements = [
        'int' => 'number',
        'string' => 'text',
    ];
    $ctx = new GenCtx(new Queue([]), $typeReplacements, null);

    $result = TypeConverter::convert($ctx, 'int');
    expect($result)->toBeInstanceOf(RawType::class);
    expect($result->render(new \Typographos\Dto\RenderCtx('', 0)))->toBe('number');

    $result2 = TypeConverter::convert($ctx, 'string');
    expect($result2)->toBeInstanceOf(RawType::class);
    expect($result2->render(new \Typographos\Dto\RenderCtx('', 0)))->toBe('text');
});

it('handles scalar types', function (): void {
    $ctx = new GenCtx(new Queue([]), [], null);

    $result = TypeConverter::convert($ctx, 'string');
    expect($result)->toBeInstanceOf(ScalarType::class);
    expect($result->render(new \Typographos\Dto\RenderCtx('', 0)))->toBe('string');

    $result2 = TypeConverter::convert($ctx, 'int');
    expect($result2)->toBeInstanceOf(ScalarType::class);
    expect($result2->render(new \Typographos\Dto\RenderCtx('', 0)))->toBe('number');
});

it('handles nullable scalar types', function (): void {
    $ctx = new GenCtx(new Queue([]), [], null);

    $result = TypeConverter::convert($ctx, '?string');
    expect($result)->toBeInstanceOf(UnionType::class);
    expect($result->render(new \Typographos\Dto\RenderCtx('', 0)))->toBe('string | null');
});

it('handles union types', function (): void {
    $ctx = new GenCtx(new Queue([]), [], null);

    $result = TypeConverter::convert($ctx, 'string|int|bool');
    expect($result)->toBeInstanceOf(UnionType::class);
    expect($result->render(new \Typographos\Dto\RenderCtx('', 0)))->toBe('string | number | boolean');
});

it('handles user-defined classes', function (): void {
    $ctx = new GenCtx(new Queue([]), [], null);

    $result = TypeConverter::convert($ctx, 'UserDefinedTestClass');
    expect($result)->toBeInstanceOf(ReferenceType::class);
    expect($result->render(new \Typographos\Dto\RenderCtx('', 0)))->toBe('UserDefinedTestClass');
});

it('handles unknown types', function (): void {
    $ctx = new GenCtx(new Queue([]), [], null);

    $result = TypeConverter::convert($ctx, 'NonExistentClass');
    expect($result)->toBeInstanceOf(ScalarType::class);
    expect($result->render(new \Typographos\Dto\RenderCtx('', 0)))->toBe('unknown');
});

it('handles mixed null types correctly', function (): void {
    $ctx = new GenCtx(new Queue([]), [], null);

    // null and mixed shouldn't get extra null union
    $result1 = TypeConverter::convert($ctx, '?null');
    expect($result1->render(new \Typographos\Dto\RenderCtx('', 0)))->toBe('null');

    $result2 = TypeConverter::convert($ctx, '?mixed');
    expect($result2->render(new \Typographos\Dto\RenderCtx('', 0)))->toBe('any');
});

it('enqueues user-defined classes', function (): void {
    $queue = new Queue([]);
    $ctx = new GenCtx($queue, [], null);

    TypeConverter::convert($ctx, 'UserDefinedTestClass');

    // The queue should now contain UserDefinedTestClass
    $queuedClass = $queue->shift();
    expect($queuedClass)->toBe('UserDefinedTestClass');
});

it('handles complex type replacements with nullability', function (): void {
    $typeReplacements = [
        'CustomInterface' => 'MyInterface',
        'AnotherType' => 'SomeType',
    ];
    $ctx = new GenCtx(new Queue([]), $typeReplacements, null);

    $result1 = TypeConverter::convert($ctx, '?CustomInterface');
    expect($result1->render(new \Typographos\Dto\RenderCtx('', 0)))->toBe('MyInterface | null');

    $result2 = TypeConverter::convert($ctx, '?AnotherType');
    expect($result2->render(new \Typographos\Dto\RenderCtx('', 0)))->toBe('SomeType | null');
});
