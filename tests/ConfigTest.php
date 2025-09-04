<?php

declare(strict_types=1);
use Typographos\Config;

it('has sensible defaults', function (): void {
    $config = new Config();

    expect($config->indent)->toBe("\t");
    expect($config->typeReplacements)->toBe([]);
    expect($config->autoDiscoverDirectory)->toBeNull();
    expect($config->filePath)->toBe('generated.d.ts');
});

it('can set custom values in constructor', function (): void {
    $config = new Config()
        ->withIndent('  ')
        ->withTypeReplacement('int', 'number')
        ->withTypeReplacement(DateTime::class, 'string')
        ->withAutoDiscoverDirectory('/some/path')
        ->withFilePath('custom.d.ts');

    expect($config->indent)->toBe('  ');
    expect($config->typeReplacements)->toBe([
        'int' => 'number',
        DateTime::class => 'string',
    ]);
    expect($config->autoDiscoverDirectory)->toBe('/some/path');
    expect($config->filePath)->toBe('custom.d.ts');
});
