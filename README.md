# Typographos

Generate TypeScript types from your PHP Data Transfer Objects (DTOs).

<!-- [![Latest Version on Packagist](https://img.shields.io/packagist/v/letamanoir/php-ts-gen.svg?style=flat-square)](https://packagist.org/packages/letamanoir/php-ts-gen) -->
[![Tests](https://img.shields.io/github/actions/workflow/status/LeTamanoir/php-ts-gen/run-tests.yml?branch=main&label=tests&style=flat-square)](https://github.com/LeTamanoir/php-ts-gen/actions/workflows/run-tests.yml)
<!-- [![Total Downloads](https://img.shields.io/packagist/dt/letamanoir/php-ts-gen.svg?style=flat-square)](https://packagist.org/packages/letamanoir/php-ts-gen) -->


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
use Typographos\{Generator, Config};

new Generator(
    new Config()
        ->withIndent("\t")
        ->withAutoDiscoverDirectory(__DIR__.'/app/DTO')
        ->withFilePath('generated.d.ts')
)->generate();
```

`generated.d.ts`:
```ts
declare namespace App {
    namespace DTO {
        export interface User {
            name: string
            age: number
        }
    }
}
```

### Features

- **Attribute-driven discovery**: mark classes with `#[Typographos\Attributes\TypeScript]` and auto-discover them from a directory.
- **Unions and intersections**: supports `string|int` and `A&B` in public property types.
- **Array PHPDoc support**: parse common PHPDoc array shapes for `array`-typed properties:
  - `list<T>` → `T[]`
  - `non-empty-list<T>` → `[T, ...T[]]`
  - `array<K,V>` → `V[]` when `K` is int-like; otherwise `{ [key: string]: V }`
- **Namespace-aware output**: generated types mirror PHP namespaces as nested TS namespaces.
- **Type replacements**: map PHP classes to TS types, e.g., `DateTime::class => 'string'`.

### Configuration

```php
new Config()
    ->withIndent("\t")                  // default: "\t"
    ->withFilePath('types.d.ts')        // default: 'test.d.ts'
    ->withAutoDiscoverDirectory('src')  // recursively require_once and scan for #[TypeScript]
    ->withTypeReplacement(DateTime::class, 'string');
```

- Pass explicit classes to `->generate(...)` to skip discovery or to force inclusion.
- Only public properties are emitted.
- For `array`-typed properties, a PHPDoc `@var` is required; otherwise an error is thrown.

### Example: arrays via PHPDoc

```php
/** @var list<string> */
public array $tags;

/** @var array<string,int> */
public array $scoresByUser;

/** @var non-empty-list<list<string>> */
public array $matrix;
```

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
