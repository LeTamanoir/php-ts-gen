# Typographos

Generate TypeScript types from your PHP Data Transfer Objects (DTOs).

<!-- Packagist badges (uncomment after publishing) -->
<!-- [![Latest Version on Packagist](https://img.shields.io/packagist/v/letamanoir/typographos.svg?style=flat-square)](https://packagist.org/packages/letamanoir/typographos) -->
[![Tests](https://img.shields.io/github/actions/workflow/status/LeTamanoir/Typographos/run-tests.yml?branch=main&label=tests&style=flat-square)](https://github.com/LeTamanoir/Typographos/actions/workflows/run-tests.yml)
<!-- [![Total Downloads](https://img.shields.io/packagist/dt/letamanoir/typographos.svg?style=flat-square)](https://packagist.org/packages/letamanoir/typographos) -->


## Requirements

- PHP 8.4+

## Installation

Install via Composer:

```bash
composer require letamanoir/typographos
```

## Usage

Annotate your DTOs with the provided attribute and run the generator.

`app/DTO/User.php`:
```php
namespace App\DTO;

use Typographos\Attributes\TypeScript;

#[TypeScript]
class User
{
    public function __construct(
        public string $name,
        public int $age,
    ) {}
}
```

`codegen.php`:
```php
use Typographos\Codegen;
use Typographos\Config;

new Codegen(
    new Config()
        ->withIndent("\t")
        ->withAutoDiscoverDirectory(__DIR__.'/app/DTO')
        ->withFilePath('generated.d.ts')
)->generate();
```

`generated.d.ts`:
```ts
declare namespace App {
    export namespace DTO {
        export interface User {
            name: string
            age: number
        }
    }
}
```

### Features

- **Attribute-driven discovery**: mark classes with `#[Typographos\Attributes\TypeScript]` and auto-discover them from a directory.
- **Union types**: supports PHP unions like `string|int` and nullable like `?Foo` → `Foo | null`.
- **Array PHPDoc support**: parse common PHPDoc array shapes for `array`-typed properties:
  - `list<T>` → `T[]`
  - `non-empty-list<T>` → `[T, ...T[]]`
  - `array<K,V>` → `V[]` when `K` is int-like; otherwise `{ [key: string]: V }`
- **Namespace-aware output**: generated types mirror PHP namespaces as nested TS namespaces.
- **Type replacements**: map PHP classes/scalars to TS types, e.g. `DateTime::class => 'string'` or `'int' => 'bigint'`.

### Configuration

```php
new Config()
    ->withIndent("\t")                  // default: "\t"
    ->withFilePath('types.d.ts')          // default: 'test.d.ts'
    ->withAutoDiscoverDirectory('src')    // recursively require_once and scan for #[TypeScript]
    ->withTypeReplacement(DateTime::class, 'string');
```

- Pass explicit classes to `->generate(...$fqcn)` to skip discovery or to force inclusion.
- Only public properties are emitted.
- For `array`-typed properties, a PHPDoc `@var` or a constructor `@param` entry is required; otherwise an error is thrown.

### Example: arrays via PHPDoc

```php
/** @var list<string> */
public array $tags;

/** @var array<string,int> */
public array $scoresByUser;

/** @var non-empty-list<list<string>> */
public array $matrix;
```

### Limitations and notes

- Intersection types (`A&B`) are not supported and will throw.
- Untyped public properties are emitted as `unknown`.
- `self`/`parent` types are resolved to concrete class names before generation.
- When writing to the destination file, ensure it is writable by the process.

### Troubleshooting

Common exceptions you might see:

- `No classes to generate` — Call `generate()` with at least one FQCN or enable auto-discovery.
- `Missing doc comment` — Add a PHPDoc `@var` (or constructor `@param`) for `array` properties.
- `Intersection types are not supported` — Replace intersections with a supported shape.
- `Unsupported array key type [...]` — Only string-like, int-like, or `array-key` keys are supported in `array<K,V>`.

## Testing

```bash
composer test
```

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Contributing

Please see [CONTRIBUTING](https://github.com/spatie/.github/blob/main/CONTRIBUTING.md) for details.

## Credits

- [Martin Saldinger](https://github.com/LeTamanoir)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
