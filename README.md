# Generate TypeScript types from your PHP Data Transfer Objects (DTOs)

[![Latest Version on Packagist](https://img.shields.io/packagist/v/phpts/php-ts-gen.svg?style=flat-square)](https://packagist.org/packages/phpts/php-ts-gen)
[![Tests](https://img.shields.io/github/actions/workflow/status/phpts/php-ts-gen/run-tests.yml?branch=main&label=tests&style=flat-square)](https://github.com/LeTamanoir/php-ts-gen/actions/workflows/run-tests.yml)
[![Total Downloads](https://img.shields.io/packagist/dt/phpts/php-ts-gen.svg?style=flat-square)](https://packagist.org/packages/phpts/php-ts-gen)

This is where your description should go. Try and limit it to a paragraph or two. Consider adding a small example.

## Support us

[<img src="https://github-ads.s3.eu-central-1.amazonaws.com/php-ts-gen.jpg?t=1" width="419px" />](https://spatie.be/github-ad-click/php-ts-gen)

We invest a lot of resources into creating [best in class open source packages](https://spatie.be/open-source). You can support us by [buying one of our paid products](https://spatie.be/open-source/support-us).

We highly appreciate you sending us a postcard from your hometown, mentioning which of our package(s) you are using. You'll find our address on [our contact page](https://spatie.be/about-us). We publish all received postcards on [our virtual postcard wall](https://spatie.be/open-source/postcards).

## Installation

You can install the package via composer:

```bash
composer require phpts/php-ts-gen
```

## Usage

```php
$skeleton = new PhpTs\PhpTsGen();
echo $skeleton->echoPhrase('Hello, PhpTs!');
```

## Testing

```bash
composer test
```

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Contributing

Please see [CONTRIBUTING](https://github.com/spatie/.github/blob/main/CONTRIBUTING.md) for details.

## Security Vulnerabilities

Please review [our security policy](../../security/policy) on how to report security vulnerabilities.

## Credits

- [Martin Saldinger](https://github.com/LeTamanoir)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
