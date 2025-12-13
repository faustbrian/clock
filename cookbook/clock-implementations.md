# Clock Implementations

The clock package provides multiple implementations of the PSR-20 `ClockInterface`, each designed for specific use cases. All implementations return `DateTimeImmutable` objects and are immutable themselves.

## Core Clocks

### CarbonImmutableClock

Uses Laravel's Carbon library with immutable instances. This is the default clock for Laravel integration.

```php
use Cline\Clock\Clocks\CarbonImmutableClock;

$clock = new CarbonImmutableClock();
$now = $clock->now(); // Returns CarbonImmutable instance
```

**When to use:**
- Laravel applications
- When you need Carbon's rich API
- Default choice for most applications

### CarbonClock

Uses Laravel's Carbon library with mutable instances.

```php
use Cline\Clock\Clocks\CarbonClock;

$clock = new CarbonClock();
$now = $clock->now(); // Returns Carbon instance
```

**When to use:**
- Legacy code requiring mutable Carbon instances
- Prefer `CarbonImmutableClock` for new code

### DateTimeImmutableClock

Uses PHP's native `DateTimeImmutable` class without any dependencies.

```php
use Cline\Clock\Clocks\DateTimeImmutableClock;

$clock = new DateTimeImmutableClock();
$now = $clock->now(); // Returns DateTimeImmutable
```

**When to use:**
- No Laravel dependency
- Lightweight applications
- Maximum compatibility

### DateTimeClock

Uses PHP's native mutable `DateTime` class.

```php
use Cline\Clock\Clocks\DateTimeClock;

$clock = new DateTimeClock();
$now = $clock->now(); // Returns DateTime
```

**When to use:**
- Legacy code requiring mutable DateTime
- Prefer immutable alternatives for new code

### UtcClock

Always returns time in UTC timezone, regardless of system timezone.

```php
use Cline\Clock\Clocks\UtcClock;

$clock = new UtcClock();
$now = $clock->now(); // Always UTC timezone
```

**When to use:**
- Distributed systems
- API servers
- Database timestamps
- Timezone-independent operations

## Testing Clocks

### FrozenClock

Returns a fixed point in time, perfect for deterministic testing.

```php
use Cline\Clock\Clocks\FrozenClock;

$fixedTime = new DateTimeImmutable('2025-01-15 12:00:00');
$clock = new FrozenClock($fixedTime);

$now = $clock->now(); // Always returns 2025-01-15 12:00:00
```

**When to use:**
- Unit tests requiring fixed timestamps
- Testing time-dependent logic
- Reproducible test scenarios

### MockClock

Combines frozen time with manual advancement and sequencing capabilities.

```php
use Cline\Clock\Clocks\MockClock;

$clock = new MockClock(new DateTimeImmutable('2025-01-15 12:00:00'));

// Freeze at specific time
$now = $clock->now(); // 2025-01-15 12:00:00

// Advance time manually
$clock->advance(hours: 2);
$later = $clock->now(); // 2025-01-15 14:00:00

// Set sequence of times
$clock->sequence([
    new DateTimeImmutable('2025-01-15 15:00:00'),
    new DateTimeImmutable('2025-01-15 16:00:00'),
]);
$first = $clock->now(); // 2025-01-15 15:00:00
$second = $clock->now(); // 2025-01-15 16:00:00
```

**When to use:**
- Complex testing scenarios
- Testing time progression
- Integration tests

### SequenceClock

Returns predetermined sequence of times, useful for testing ordered operations.

```php
use Cline\Clock\Clocks\SequenceClock;

$times = [
    new DateTimeImmutable('2025-01-15 10:00:00'),
    new DateTimeImmutable('2025-01-15 11:00:00'),
    new DateTimeImmutable('2025-01-15 12:00:00'),
];

$clock = new SequenceClock(...$times);

$first = $clock->now();  // 2025-01-15 10:00:00
$second = $clock->now(); // 2025-01-15 11:00:00
$third = $clock->now();  // 2025-01-15 12:00:00
```

**When to use:**
- Testing ordered time-dependent operations
- Simulating specific time sequences
- Batch processing tests

### TickClock

Allows manual time advancement by specific intervals.

```php
use Cline\Clock\Clocks\TickClock;

$clock = new TickClock(
    start: new DateTimeImmutable('2025-01-15 12:00:00'),
    interval: new DateInterval('PT1H') // 1 hour
);

$first = $clock->now();  // 2025-01-15 12:00:00
$clock->tick();
$second = $clock->now(); // 2025-01-15 13:00:00
$clock->tick();
$third = $clock->now();  // 2025-01-15 14:00:00
```

**When to use:**
- Testing incremental time progression
- Simulating scheduled tasks
- Step-by-step time advancement

### OffsetClock

Wraps another clock and adds a fixed time offset.

```php
use Cline\Clock\Clocks\OffsetClock;
use Cline\Clock\Clocks\SystemClock;

$systemClock = new SystemClock();
$clock = new OffsetClock(
    clock: $systemClock,
    offset: new DateInterval('P1D') // 1 day forward
);

// If system time is 2025-01-15 12:00:00
$now = $clock->now(); // 2025-01-16 12:00:00
```

**When to use:**
- Testing future/past scenarios
- Simulating time zones
- Time-shift testing

## Timezone Support

Most clocks support timezone configuration:

```php
use Cline\Clock\Clocks\CarbonImmutableClock;

$clock = new CarbonImmutableClock(timezone: 'America/New_York');
$now = $clock->now(); // Returns time in America/New_York timezone

// Change timezone
$tokyoClock = new CarbonImmutableClock(timezone: 'Asia/Tokyo');
$tokyoTime = $tokyoClock->now();
```

## Helper Function

Use the `clock()` helper to get the default clock instance:

```php
use function Cline\Clock\clock;

$now = clock()->now(); // Uses CarbonImmutableClock by default
```

## Choosing the Right Clock

**Production:**
- Laravel apps: `CarbonImmutableClock`
- Standalone PHP: `DateTimeImmutableClock`
- Distributed systems: `UtcClock`

**Testing:**
- Fixed time: `FrozenClock`
- Complex scenarios: `MockClock`
- Ordered operations: `SequenceClock`
- Time progression: `TickClock`
