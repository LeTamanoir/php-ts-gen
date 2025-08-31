# Typographos

Generate TS types of your PHP DTOs

[![Latest Version on Packagist](https://img.shields.io/packagist/v/phpts/php-ts-gen.svg?style=flat-square)](https://packagist.org/packages/phpts/php-ts-gen)
[![Tests](https://img.shields.io/github/actions/workflow/status/LeTamanoir/Typographos/run-tests.yml?branch=main&label=tests&style=flat-square)](https://github.com/LeTamanoir/Typographos/actions/workflows/run-tests.yml)
[![Total Downloads](https://img.shields.io/packagist/dt/phpts/php-ts-gen.svg?style=flat-square)](https://packagist.org/packages/phpts/php-ts-gen)


## Installation

You can install the package via composer:

```bash
composer require letamanoir/typographos
```

## Usage

`path/to/User.php`:
```php
class User {
    public function __construct(
        public string $name,
        public int $age,
    )
}
```

`codegen.php`:
```php
use Path\To\User;
use Typographos\{Generator, Config};

new Generator(
    new Config()
        ->withIndent("\t")
        ->withAutoDiscoverDirectory("path/to")
        ->withFileName("generated.d.ts")
)
    ->generate();
```

`generated.d.ts`:
```ts
export interface User {
    name: string
    age: number
}
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
