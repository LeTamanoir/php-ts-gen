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
use Typographos\Generator;

new Generator()
    ->discoverFrom(__DIR__.'/app/DTO')
    ->outputTo('generated.d.ts')
    ->withIndent("\t")
    ->generate();
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

- **Zero-configuration setup**: Just add `#[TypeScript]` to your PHP classes and generate types automatically
- **Smart type detection**: Automatically converts PHP types to their TypeScript equivalents with full nullable and union type support
- **Flexible inline types**: Use `#[InlineType]` to embed simple objects directly instead of creating separate interfaces  
- **Rich array support**: Handles complex array types like `list<T>`, `non-empty-list<T>`, and `array<K,V>` from PHPDoc annotations
- **Namespace preservation**: Maintains your PHP namespace structure in the generated TypeScript declarations
- **Custom type mapping**: Replace any PHP type with custom TypeScript types (e.g., `DateTime` → `string`, `int` → `bigint`)
- **Directory scanning**: Automatically discover all your DTOs from entire directories
- **Clean output**: Generates properly formatted, readable TypeScript declaration files

### Configuration

```php
use Typographos\Generator;

// Simple usage
new Generator()
    ->discoverFrom('src')                           // recursively scan for #[TypeScript]
    ->outputTo('types.d.ts')                        // output file path
    ->generate();

// Advanced configuration
new Generator()
    ->discoverFrom(__DIR__.'/app/DTO')
    ->outputTo('resources/js/types.d.ts')
    ->withIndent('    ')                            // default: "\t"
    ->withTypeReplacement(DateTime::class, 'string')
    ->generate();

```

#### Usage Notes

- **Auto-discovery**: Use `->discoverFrom('path')` to recursively scan for classes with `#[TypeScript]` attribute
- **Explicit classes**: Pass class names to `->generate(['App\\DTO\\User', 'App\\DTO\\Post'])` to skip discovery
- **Output**: Use `->outputTo('file.d.ts')` to specify the output file path
- **Property filtering**: Only public properties are emitted
- **Array types**: Requires PHPDoc `@var` or constructor `@param` for `array`-typed properties

### Example: arrays via PHPDoc

```php
/** @var list<string> */
public array $tags;

/** @var array<string,int> */
public array $scoresByUser;

/** @var non-empty-list<list<string>> */
public array $matrix;
```

### Example: inline records

Use the `#[InlineType]` attribute to inline class types instead of creating separate interfaces:

```php
use Typographos\Attributes\InlineType;
use Typographos\Attributes\TypeScript;

#[TypeScript]
class Address
{
    public function __construct(
        public string $street,
        public string $city,
        public string $zipCode,
    ) {}
}

#[TypeScript]
class User
{
    public function __construct(
        public string $name,
        #[InlineType]                // ← Inline this class
        public Address $address,
        public Address $mailingAddress, // ← Reference (separate interface)
    ) {}
}
```

Generated TypeScript:
```typescript
declare namespace App {
  export namespace DTO {
    export interface User {
      name: string
      address: {                    // ← Inlined
        street: string
        city: string
        zipCode: string
      }
      mailingAddress: Address       // ← Reference
    }
    export interface Address {      // ← Separate interface for reference
      street: string
      city: string
      zipCode: string
    }
  }
}
```

**When to use inline records:**
- Simple value objects that are only used in one place
- Reducing the number of generated interfaces for better readability
- Embedding small DTOs directly into parent types

### Advanced Generation

For advanced scenarios, the library provides a straightforward fluent interface. All generation happens through the main `Generator` class:

```php
use Typographos\Generator;

// Simple usage - generates and writes to file
new Generator()
    ->discoverFrom('src/DTOs')
    ->outputTo('types.d.ts')
    ->withIndent("\t")
    ->generate();

// Advanced configuration with type replacements
new Generator()
    ->discoverFrom('app/Models')
    ->withTypeReplacement(\DateTime::class, 'string')
    ->withTypeReplacement('int', 'bigint')
    ->withIndent('    ')
    ->outputTo('api-types.d.ts')
    ->generate();

// Explicit class list (skips auto-discovery)
new Generator()
    ->withIndent("\t")
    ->generate([
        'App\\DTO\\User',
        'App\\DTO\\Post',
        'App\\DTO\\Comment',
    ]);
```

**Key methods:**
- `discoverFrom(string $directory)`: Auto-discover classes with `#[TypeScript]` attribute
- `outputTo(string $filePath)`: Set output file path
- `withIndent(string $indent)`: Set indentation style (default: `"\t"`)
- `withTypeReplacement(string $phpType, string $tsType)`: Replace PHP types with custom TypeScript types
- `generate(array $classNames = [])`: Generate and write types (optionally specify classes explicitly)

### Architecture

The refactored architecture provides clean separation of concerns:

- **`Generator`**: Main orchestrator with fluent interface that coordinates the entire generation process
- **`ClassDiscovery`**: Static utility for finding classes with TypeScript attributes from directories
- **`TypeResolver`**: Resolves PHP types, handling special cases like `array`, `self`, `parent`, and unions  
- **`TypeConverter`**: Static utility that converts resolved PHP types to TypeScript type objects
- **`Queue`**: Manages the processing queue of classes during generation

The library uses static utility classes for optimal performance while maintaining an intuitive fluent API through the main `Generator` class.

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
