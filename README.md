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

- **[Basic Usage](cookbook/01-basic-usage.php)** - Clock helper and instantiation
- **[Timezone Support](cookbook/02-timezone-support.php)** - Working with different timezones
- **[Frozen Clock for Testing](cookbook/03-frozen-clock-testing.php)** - Fixed timestamps for tests
- **[Dependency Injection](cookbook/04-dependency-injection.php)** - Building testable services
- **[Clock Implementations Overview](cookbook/05-clock-implementations.php)** - All available implementations
- **[Advanced Testing](cookbook/06-advanced-testing.php)** - MockClock and SequenceClock scenarios
- **[Clock Decorators](cookbook/07-decorators.php)** - Caching and logging decorators
- **[Clock Registry](cookbook/08-registry.php)** - Managing multiple named instances
- **[Laravel Integration](cookbook/09-laravel-integration.php)** - Service container and facades

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
