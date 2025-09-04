<?php

declare(strict_types=1);

// Test class for user-defined type testing
class UserDefinedTestClass
{
    public function __construct(
        public string $name,
    ) {}
}

use Typographos\Dto\RawType;
use Typographos\Dto\ReferenceType;
use Typographos\Dto\ScalarType;
use Typographos\Dto\UnionType;
use Typographos\Queue;
use Typographos\TypeConverter;

it('handles empty string type', function (): void {
    $queue = new Queue([]);
    $result = TypeConverter::convertToTypeScript('', $queue, []);

    expect($result)->toBeInstanceOf(ScalarType::class);
    expect($result->render(new \Typographos\Dto\RenderCtx('', 0)))->toBe('unknown');
});

it('handles nullable type replacements', function (): void {
    $queue = new Queue([]);
    $typeReplacements = ['CustomType' => 'MyCustomType'];

    $result = TypeConverter::convertToTypeScript('?CustomType', $queue, $typeReplacements);

    expect($result)->toBeInstanceOf(UnionType::class);
    expect($result->render(new \Typographos\Dto\RenderCtx('', 0)))->toBe('MyCustomType | null');
});

it('handles basic type replacements', function (): void {
    $queue = new Queue([]);
    $typeReplacements = [
        'int' => 'number',
        'string' => 'text',
    ];

    $result = TypeConverter::convertToTypeScript('int', $queue, $typeReplacements);
    expect($result)->toBeInstanceOf(RawType::class);
    expect($result->render(new \Typographos\Dto\RenderCtx('', 0)))->toBe('number');

    $result2 = TypeConverter::convertToTypeScript('string', $queue, $typeReplacements);
    expect($result2)->toBeInstanceOf(RawType::class);
    expect($result2->render(new \Typographos\Dto\RenderCtx('', 0)))->toBe('text');
});

it('handles scalar types', function (): void {
    $queue = new Queue([]);

    $result = TypeConverter::convertToTypeScript('string', $queue, []);
    expect($result)->toBeInstanceOf(ScalarType::class);
    expect($result->render(new \Typographos\Dto\RenderCtx('', 0)))->toBe('string');

    $result2 = TypeConverter::convertToTypeScript('int', $queue, []);
    expect($result2)->toBeInstanceOf(ScalarType::class);
    expect($result2->render(new \Typographos\Dto\RenderCtx('', 0)))->toBe('number');
});

it('handles nullable scalar types', function (): void {
    $queue = new Queue([]);

    $result = TypeConverter::convertToTypeScript('?string', $queue, []);
    expect($result)->toBeInstanceOf(UnionType::class);
    expect($result->render(new \Typographos\Dto\RenderCtx('', 0)))->toBe('string | null');
});

it('handles union types', function (): void {
    $queue = new Queue([]);

    $result = TypeConverter::convertToTypeScript('string|int|bool', $queue, []);
    expect($result)->toBeInstanceOf(UnionType::class);
    expect($result->render(new \Typographos\Dto\RenderCtx('', 0)))->toBe('string | number | boolean');
});

it('handles user-defined classes', function (): void {
    $queue = new Queue([]);

    $result = TypeConverter::convertToTypeScript('UserDefinedTestClass', $queue, []);
    expect($result)->toBeInstanceOf(ReferenceType::class);
    expect($result->render(new \Typographos\Dto\RenderCtx('', 0)))->toBe('UserDefinedTestClass');
});

it('handles unknown types', function (): void {
    $queue = new Queue([]);

    $result = TypeConverter::convertToTypeScript('NonExistentClass', $queue, []);
    expect($result)->toBeInstanceOf(ScalarType::class);
    expect($result->render(new \Typographos\Dto\RenderCtx('', 0)))->toBe('unknown');
});

it('handles mixed null types correctly', function (): void {
    $queue = new Queue([]);

    // null and mixed shouldn't get extra null union
    $result1 = TypeConverter::convertToTypeScript('?null', $queue, []);
    expect($result1->render(new \Typographos\Dto\RenderCtx('', 0)))->toBe('null');

    $result2 = TypeConverter::convertToTypeScript('?mixed', $queue, []);
    expect($result2->render(new \Typographos\Dto\RenderCtx('', 0)))->toBe('any');
});

it('enqueues user-defined classes', function (): void {
    $queue = new Queue([]);

    TypeConverter::convertToTypeScript('UserDefinedTestClass', $queue, []);

    // The queue should now contain UserDefinedTestClass
    $queuedClass = $queue->shift();
    expect($queuedClass)->toBe('UserDefinedTestClass');
});

it('handles complex type replacements with nullability', function (): void {
    $queue = new Queue([]);
    $typeReplacements = [
        'CustomInterface' => 'MyInterface',
        'AnotherType' => 'SomeType',
    ];

    $result1 = TypeConverter::convertToTypeScript('?CustomInterface', $queue, $typeReplacements);
    expect($result1->render(new \Typographos\Dto\RenderCtx('', 0)))->toBe('MyInterface | null');

    $result2 = TypeConverter::convertToTypeScript('?AnotherType', $queue, $typeReplacements);
    expect($result2->render(new \Typographos\Dto\RenderCtx('', 0)))->toBe('SomeType | null');
});
