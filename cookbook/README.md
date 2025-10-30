# Clock Package Cookbook

This directory contains practical examples demonstrating how to use the Clock package in various scenarios.

## Examples

### 01. Basic Usage
**File:** `01-basic-usage.php`

Learn how to use the `clock()` helper function and instantiate different clock implementations.

```bash
php cookbook/01-basic-usage.php
```

### 02. Timezone Support
**File:** `02-timezone-support.php`

Work with clocks in different timezones around the world.

```bash
php cookbook/02-timezone-support.php
```

### 03. Frozen Clock for Testing
**File:** `03-frozen-clock-testing.php`

Use `FrozenClock` to test time-dependent code with fixed timestamps.

```bash
php cookbook/03-frozen-clock-testing.php
```

### 04. Dependency Injection
**File:** `04-dependency-injection.php`

Build testable services using dependency injection with clock interfaces.

```bash
php cookbook/04-dependency-injection.php
```

### 05. Clock Implementations Overview
**File:** `05-clock-implementations.php`

See all available clock implementations and their specific use cases.

```bash
php cookbook/05-clock-implementations.php
```

### 06. Advanced Testing
**File:** `06-advanced-testing.php`

Advanced testing scenarios with MockClock and SequenceClock.

```bash
php cookbook/06-advanced-testing.php
```

### 07. Clock Decorators
**File:** `07-decorators.php`

Add functionality to clocks using decorators (caching, logging).

```bash
php cookbook/07-decorators.php
```

### 08. Clock Registry
**File:** `08-registry.php`

Manage multiple named clock instances with ClockRegistry.

```bash
php cookbook/08-registry.php
```

### 09. Laravel Integration
**File:** `09-laravel-integration.php`

Use the clock package with Laravel's service container and facades.

```bash
php cookbook/09-laravel-integration.php
```

## Running Examples

All examples require the package dependencies to be installed:

```bash
composer install
```

Then run any example:

```bash
php cookbook/01-basic-usage.php
```

## Available Clock Implementations

### Core Clocks
- **CarbonClock** - Uses Laravel's Date facade with Carbon
- **CarbonImmutableClock** - Uses CarbonImmutable (default)
- **DateTimeClock** - Uses native PHP DateTime
- **DateTimeImmutableClock** - Uses native DateTimeImmutable
- **FrozenClock** - Fixed time for testing
- **UtcClock** - Always returns UTC time

### Testing Clocks
- **MockClock** - Freeze, advance, and sequence combined
- **SequenceClock** - Predetermined time sequence
- **TickClock** - Manual time advancement
- **OffsetClock** - Time with fixed offset

### Decorators
- **LoggingClock** - Logs clock access
- **CachingClock** - Caches clock results

### Utilities
- **ClockRegistry** - Named clock instances
- **ClockComparison** - Trait for time comparisons

## PSR-20 Compliance

All clocks implement the PSR-20 `ClockInterface`, making them compatible with any PSR-20 aware libraries.

## Laravel Integration

The package includes a service provider and facade for seamless Laravel integration. See `09-laravel-integration.php` for details.
