[![GitHub Workflow Status][ico-tests]][link-tests]
[![Latest Version on Packagist][ico-version]][link-packagist]
[![Software License][ico-license]](LICENSE.md)
[![Total Downloads][ico-downloads]][link-downloads]

------

Clock abstraction for PHP 8.4+ with multiple implementations including Carbon, DateTime, and frozen time for testing.

## Requirements

> **Requires [PHP 8.4+](https://php.net/releases/)**

## Installation

```bash
composer require cline/clock
```

## Documentation

- **[Getting Started](https://docs.cline.sh/clock/getting-started/)** - Installation and basic usage
- **[Clock Implementations](https://docs.cline.sh/clock/implementations/)** - All available clock types
- **[Testing Strategies](https://docs.cline.sh/clock/testing/)** - Patterns for time-dependent code
- **[Laravel Integration](https://docs.cline.sh/clock/laravel/)** - Service provider, facade, and DI
- **[Decorators](https://docs.cline.sh/clock/decorators/)** - Caching and logging decorators
- **[Utilities](https://docs.cline.sh/clock/utilities/)** - Helper function and registry
- **[Examples](https://docs.cline.sh/clock/examples/)** - Real-world usage patterns

## Change log

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) and [CODE_OF_CONDUCT](CODE_OF_CONDUCT.md) for details.

## Security

If you discover any security related issues, please use the [GitHub security reporting form][link-security] rather than the issue queue.

## Credits

- [Brian Faust][link-maintainer]
- [All Contributors][link-contributors]

## License

The MIT License. Please see [License File](LICENSE.md) for more information.

[ico-tests]: https://github.com/faustbrian/clock/actions/workflows/quality-assurance.yaml/badge.svg
[ico-version]: https://img.shields.io/packagist/v/cline/clock.svg
[ico-license]: https://img.shields.io/badge/License-MIT-green.svg
[ico-downloads]: https://img.shields.io/packagist/dt/cline/clock.svg

[link-tests]: https://github.com/faustbrian/clock/actions
[link-packagist]: https://packagist.org/packages/cline/clock
[link-downloads]: https://packagist.org/packages/cline/clock
[link-security]: https://github.com/faustbrian/clock/security
[link-maintainer]: https://github.com/faustbrian
[link-contributors]: ../../contributors
