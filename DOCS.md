## Table of Contents

1. Overview (`docs/README.md`)
2. Decorators (`docs/decorators.md`)
3. Examples (`docs/examples.md`)
4. Implementations (`docs/implementations.md`)
5. Laravel (`docs/laravel.md`)
6. Testing (`docs/testing.md`)
7. Utilities (`docs/utilities.md`)
Clock is a PSR-20 compliant clock abstraction for PHP that provides multiple implementations for different use cases, including Carbon, DateTime, and specialized testing clocks.

## Requirements

> **Requires [PHP 8.4+](https://php.net/releases/)**

## Installation

```bash
composer require cline/clock
```

The package auto-registers with Laravel via package discovery.

## Quick Example

```php
use Cline\Clock\Clocks\CarbonImmutableClock;
use Cline\Clock\Clocks\FrozenClock;
use DateTimeImmutable;

// Production: Get current time
$clock = new CarbonImmutableClock();
$now = $clock->now(); // DateTimeImmutable

// Testing: Fixed time for deterministic tests
$frozen = new FrozenClock(new DateTimeImmutable('2025-01-15 12:00:00'));
$frozen->now(); // Always returns 2025-01-15 12:00:00
```

## Helper Function

Use the `clock()` helper for quick access:

```php
use function Cline\Clock\clock;

$now = clock()->now(); // Uses CarbonImmutableClock by default

// With timezone
$clock = clock(timezone: new DateTimeZone('America/New_York'));
```

## Laravel Facade

```php
use Cline\Clock\Facades\Clock;

$now = Clock::now();
```

## Available Implementations

| Clock | Use Case |
|-------|----------|
| `CarbonImmutableClock` | Default for Laravel apps |
| `CarbonClock` | Mutable Carbon instances |
| `DateTimeImmutableClock` | Native PHP without dependencies |
| `DateTimeClock` | Native mutable DateTime |
| `UtcClock` | Always UTC timezone |
| `FrozenClock` | Fixed time for testing |
| `MockClock` | Mutable testing clock |
| `SequenceClock` | Predetermined time sequence |
| `TickClock` | Manual time advancement |
| `OffsetClock` | Time offset decorator |

## Next Steps

- [Clock Implementations](./implementations.md) - Detailed guide to all clock types
- [Testing Strategies](./testing.md) - Patterns for testing time-dependent code
- [Laravel Integration](./laravel.md) - Service provider, facade, and DI
- [Decorators](./decorators.md) - Caching and logging decorators
- [Examples](./examples.md) - Real-world usage patterns

The clock package includes decorator classes that wrap any `ClockInterface` implementation to add caching or logging capabilities.

## CachingClock

Caches time values for a configurable TTL period, reducing overhead from repeated time calculations.

```php
use Cline\Clock\Decorators\CachingClock;
use Cline\Clock\Clocks\CarbonImmutableClock;

$baseClock = new CarbonImmutableClock();
$clock = new CachingClock($baseClock, ttlSeconds: 5);

$first = $clock->now();  // Fetches from base clock
$second = $clock->now(); // Returns cached value
$third = $clock->now();  // Returns cached value

// After 5 seconds...
$fourth = $clock->now(); // Fetches fresh value
```

### Configuration

```php
// 1 second TTL (default)
$clock = new CachingClock($baseClock);

// Custom TTL
$clock = new CachingClock($baseClock, ttlSeconds: 10);
```

### Clear Cache

Force the next call to fetch a fresh value:

```php
$clock->clear();
$fresh = $clock->now(); // Fetches from base clock
```

### Use Cases

- High-frequency time checks where microsecond precision isn't required
- Reducing system calls in performance-critical code
- Batch operations that should use the same timestamp

## LoggingClock

Logs every time retrieval to a PSR-3 logger with detailed context.

```php
use Cline\Clock\Decorators\LoggingClock;
use Cline\Clock\Clocks\CarbonImmutableClock;
use Psr\Log\LoggerInterface;

$baseClock = new CarbonImmutableClock();
$logger = app(LoggerInterface::class);

$clock = new LoggingClock($baseClock, $logger);

$clock->now();
// Logs: "Clock returned time" with context:
// - timestamp: "2025-01-15 12:00:00.123456"
// - timezone: "UTC"
// - clock_class: "Cline\Clock\Clocks\CarbonImmutableClock"
```

### Log Level

Configure the PSR-3 log level:

```php
// Default: debug
$clock = new LoggingClock($baseClock, $logger);

// Custom level
$clock = new LoggingClock($baseClock, $logger, level: 'info');
```

Available levels: `emergency`, `alert`, `critical`, `error`, `warning`, `notice`, `info`, `debug`

### Log Output

Each call to `now()` produces a log entry:

```json
{
    "message": "Clock returned time",
    "context": {
        "timestamp": "2025-01-15 12:00:00.123456",
        "timezone": "America/New_York",
        "clock_class": "Cline\\Clock\\Clocks\\CarbonImmutableClock"
    },
    "level": "debug"
}
```

### Use Cases

- Debugging time-related issues
- Auditing time access patterns
- Monitoring clock usage in production
- Tracing time flow through complex operations

## Combining Decorators

Decorators can be stacked:

```php
use Cline\Clock\Decorators\CachingClock;
use Cline\Clock\Decorators\LoggingClock;
use Cline\Clock\Clocks\CarbonImmutableClock;

$baseClock = new CarbonImmutableClock();

// Log first, then cache
$logged = new LoggingClock($baseClock, $logger);
$cached = new CachingClock($logged, ttlSeconds: 5);

// Or cache first, then log (logs cache hits too)
$cached = new CachingClock($baseClock, ttlSeconds: 5);
$logged = new LoggingClock($cached, $logger);
```

The order matters:
- **Log then Cache**: Only logs cache misses (when base clock is called)
- **Cache then Log**: Logs every access including cache hits

## Creating Custom Decorators

Implement `ClockInterface` and wrap another clock:

```php
use Cline\Clock\Contracts\ClockInterface;
use DateTimeImmutable;

final readonly class MetricsClock implements ClockInterface
{
    public function __construct(
        private ClockInterface $clock,
        private MetricsCollector $metrics,
    ) {}

    public function now(): DateTimeImmutable
    {
        $this->metrics->increment('clock.calls');

        $start = microtime(true);
        $result = $this->clock->now();
        $duration = microtime(true) - $start;

        $this->metrics->timing('clock.duration', $duration);

        return $result;
    }
}
```

## Laravel Service Provider Example

Register decorated clocks:

```php
use Cline\Clock\Clocks\CarbonImmutableClock;
use Cline\Clock\Contracts\ClockInterface;
use Cline\Clock\Decorators\CachingClock;
use Cline\Clock\Decorators\LoggingClock;
use Illuminate\Support\ServiceProvider;
use Psr\Log\LoggerInterface;

class ClockServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(ClockInterface::class, function ($app) {
            $clock = new CarbonImmutableClock();

            if ($app->environment('production')) {
                // Cache in production
                $clock = new CachingClock($clock, ttlSeconds: 1);
            }

            if (config('clock.logging', false)) {
                // Add logging when enabled
                $clock = new LoggingClock(
                    $clock,
                    $app->make(LoggerInterface::class),
                    level: 'debug'
                );
            }

            return $clock;
        });
    }
}
```

Practical examples demonstrating common use cases for the clock package.

## Rate Limiting

Implement rate limiting with time-based token bucket:

```php
use Cline\Clock\Contracts\ClockInterface;
use DateTimeImmutable;

class RateLimiter
{
    private array $buckets = [];

    public function __construct(
        private readonly ClockInterface $clock,
        private readonly int $maxAttempts = 60,
        private readonly int $decayMinutes = 1,
    ) {}

    public function tooManyAttempts(string $key): bool
    {
        $this->cleanOldAttempts($key);

        return count($this->buckets[$key] ?? []) >= $this->maxAttempts;
    }

    public function hit(string $key): void
    {
        $this->buckets[$key][] = $this->clock->now();
    }

    public function availableAt(string $key): DateTimeImmutable
    {
        $this->cleanOldAttempts($key);

        if (empty($this->buckets[$key])) {
            return $this->clock->now();
        }

        $oldestAttempt = min($this->buckets[$key]);

        return $oldestAttempt->modify("+{$this->decayMinutes} minutes");
    }

    private function cleanOldAttempts(string $key): void
    {
        if (!isset($this->buckets[$key])) {
            return;
        }

        $cutoff = $this->clock->now()->modify("-{$this->decayMinutes} minutes");

        $this->buckets[$key] = array_filter(
            $this->buckets[$key],
            fn($time) => $time >= $cutoff
        );
    }
}
```

## Session Management

Track session expiration with configurable timeouts:

```php
use Cline\Clock\Contracts\ClockInterface;
use DateTimeImmutable;

class Session
{
    private ?DateTimeImmutable $lastActivity = null;

    public function __construct(
        private readonly ClockInterface $clock,
        private readonly int $timeoutSeconds = 3600,
    ) {}

    public function start(): void
    {
        $this->lastActivity = $this->clock->now();
    }

    public function touch(): void
    {
        $this->lastActivity = $this->clock->now();
    }

    public function isActive(): bool
    {
        if ($this->lastActivity === null) {
            return false;
        }

        $expiresAt = $this->lastActivity->modify("+{$this->timeoutSeconds} seconds");

        return $this->clock->now() < $expiresAt;
    }

    public function secondsUntilExpiry(): int
    {
        if (!$this->isActive()) {
            return 0;
        }

        $expiresAt = $this->lastActivity->modify("+{$this->timeoutSeconds} seconds");

        return $expiresAt->getTimestamp() - $this->clock->now()->getTimestamp();
    }
}
```

## Subscription Management

Handle subscription lifecycle with precise timing:

```php
use Cline\Clock\Contracts\ClockInterface;
use DateTimeImmutable;

class Subscription
{
    public function __construct(
        private readonly ClockInterface $clock,
        public DateTimeImmutable $startsAt,
        public DateTimeImmutable $endsAt,
    ) {}

    public function isActive(): bool
    {
        $now = $this->clock->now();

        return $now >= $this->startsAt && $now <= $this->endsAt;
    }

    public function isPending(): bool
    {
        return $this->clock->now() < $this->startsAt;
    }

    public function isExpired(): bool
    {
        return $this->clock->now() > $this->endsAt;
    }

    public function daysRemaining(): int
    {
        if ($this->isExpired()) {
            return 0;
        }

        return $this->clock->now()->diff($this->endsAt)->days;
    }

    public function renew(int $months = 12): self
    {
        $newStartsAt = $this->isExpired()
            ? $this->clock->now()
            : $this->endsAt;

        $newEndsAt = $newStartsAt->modify("+{$months} months");

        return new self($this->clock, $newStartsAt, $newEndsAt);
    }
}
```

## Coupon System

Implement time-sensitive discount coupons:

```php
use Cline\Clock\Contracts\ClockInterface;
use DateTimeImmutable;

class Coupon
{
    public function __construct(
        private readonly ClockInterface $clock,
        public readonly string $code,
        public readonly float $discount,
        public readonly DateTimeImmutable $validFrom,
        public readonly DateTimeImmutable $validUntil,
        public readonly ?int $maxUses = null,
        private int $usageCount = 0,
    ) {}

    public function isValid(): bool
    {
        return $this->isWithinValidPeriod()
            && !$this->isMaxUsesReached();
    }

    public function isWithinValidPeriod(): bool
    {
        $now = $this->clock->now();

        return $now >= $this->validFrom && $now <= $this->validUntil;
    }

    public function isMaxUsesReached(): bool
    {
        if ($this->maxUses === null) {
            return false;
        }

        return $this->usageCount >= $this->maxUses;
    }

    public function hoursUntilExpiry(): int
    {
        if (!$this->isWithinValidPeriod()) {
            return 0;
        }

        $diff = $this->clock->now()->diff($this->validUntil);

        return ($diff->days * 24) + $diff->h;
    }

    public function use(): void
    {
        if (!$this->isValid()) {
            throw new InvalidArgumentException("Coupon is not valid");
        }

        $this->usageCount++;
    }
}
```

## Task Scheduler

Schedule and execute tasks at specific intervals:

```php
use Cline\Clock\Contracts\ClockInterface;
use DateTimeImmutable;

class TaskScheduler
{
    private array $tasks = [];
    private array $lastRun = [];

    public function __construct(
        private readonly ClockInterface $clock
    ) {}

    public function schedule(string $id, callable $task, int $intervalSeconds): void
    {
        $this->tasks[$id] = [
            'task' => $task,
            'interval' => $intervalSeconds,
        ];
    }

    public function run(): array
    {
        $executed = [];
        $now = $this->clock->now();

        foreach ($this->tasks as $id => $config) {
            if (!$this->shouldRun($id, $now, $config['interval'])) {
                continue;
            }

            ($config['task'])();
            $this->lastRun[$id] = $now;
            $executed[] = $id;
        }

        return $executed;
    }

    private function shouldRun(string $id, DateTimeImmutable $now, int $interval): bool
    {
        if (!isset($this->lastRun[$id])) {
            return true;
        }

        $nextRun = $this->lastRun[$id]->modify("+{$interval} seconds");

        return $now >= $nextRun;
    }

    public function nextRunTime(string $id): ?DateTimeImmutable
    {
        if (!isset($this->tasks[$id])) {
            return null;
        }

        if (!isset($this->lastRun[$id])) {
            return $this->clock->now();
        }

        return $this->lastRun[$id]->modify("+{$this->tasks[$id]['interval']} seconds");
    }
}
```

## Cache with TTL

Implement time-aware caching:

```php
use Cline\Clock\Contracts\ClockInterface;
use DateTimeImmutable;

class Cache
{
    private array $store = [];

    public function __construct(
        private readonly ClockInterface $clock
    ) {}

    public function put(string $key, mixed $value, int $ttlSeconds): void
    {
        $this->store[$key] = [
            'value' => $value,
            'expires_at' => $this->clock->now()->modify("+{$ttlSeconds} seconds"),
        ];
    }

    public function get(string $key, mixed $default = null): mixed
    {
        if (!$this->has($key)) {
            return $default;
        }

        return $this->store[$key]['value'];
    }

    public function has(string $key): bool
    {
        if (!isset($this->store[$key])) {
            return false;
        }

        if ($this->clock->now() > $this->store[$key]['expires_at']) {
            unset($this->store[$key]);
            return false;
        }

        return true;
    }

    public function remember(string $key, int $ttlSeconds, callable $callback): mixed
    {
        if ($this->has($key)) {
            return $this->get($key);
        }

        $value = $callback();
        $this->put($key, $value, $ttlSeconds);

        return $value;
    }

    public function ttl(string $key): ?int
    {
        if (!$this->has($key)) {
            return null;
        }

        $expiresAt = $this->store[$key]['expires_at'];

        return max(0, $expiresAt->getTimestamp() - $this->clock->now()->getTimestamp());
    }
}
```

## Event Logger

Log events with precise timing:

```php
use Cline\Clock\Contracts\ClockInterface;
use DateTimeImmutable;

class EventLogger
{
    private array $events = [];

    public function __construct(
        private readonly ClockInterface $clock
    ) {}

    public function log(string $type, string $message, array $context = []): void
    {
        $this->events[] = [
            'type' => $type,
            'message' => $message,
            'context' => $context,
            'timestamp' => $this->clock->now(),
        ];
    }

    public function getEvents(
        ?string $type = null,
        ?DateTimeImmutable $since = null
    ): array {
        $events = $this->events;

        if ($type !== null) {
            $events = array_filter(
                $events,
                fn($event) => $event['type'] === $type
            );
        }

        if ($since !== null) {
            $events = array_filter(
                $events,
                fn($event) => $event['timestamp'] >= $since
            );
        }

        return array_values($events);
    }

    public function getRecentEvents(int $minutes = 5): array
    {
        $since = $this->clock->now()->modify("-{$minutes} minutes");

        return $this->getEvents(since: $since);
    }
}
```

## Retry with Exponential Backoff

```php
use Cline\Clock\Contracts\ClockInterface;
use DateTimeImmutable;

class RetryHandler
{
    private array $attempts = [];

    public function __construct(
        private readonly ClockInterface $clock,
        private readonly int $maxAttempts = 3,
        private readonly int $baseDelaySeconds = 1,
    ) {}

    public function canRetry(string $key): bool
    {
        $attempts = $this->attempts[$key] ?? [];

        if (count($attempts) >= $this->maxAttempts) {
            return false;
        }

        if (empty($attempts)) {
            return true;
        }

        $nextRetryAt = $this->calculateNextRetry($key);

        return $this->clock->now() >= $nextRetryAt;
    }

    public function recordAttempt(string $key): void
    {
        $this->attempts[$key][] = $this->clock->now();
    }

    public function calculateNextRetry(string $key): DateTimeImmutable
    {
        $attempts = $this->attempts[$key] ?? [];
        $attemptCount = count($attempts);

        if ($attemptCount === 0) {
            return $this->clock->now();
        }

        $lastAttempt = end($attempts);
        $delay = $this->baseDelaySeconds * (2 ** ($attemptCount - 1));

        return $lastAttempt->modify("+{$delay} seconds");
    }

    public function reset(string $key): void
    {
        unset($this->attempts[$key]);
    }
}
```

## Clock Registry Usage

Manage multiple clocks for different purposes:

```php
use Cline\Clock\Support\ClockRegistry;
use Cline\Clock\Clocks\CarbonImmutableClock;
use Cline\Clock\Clocks\UtcClock;

// Register clocks
ClockRegistry::set('local', new CarbonImmutableClock());
ClockRegistry::set('utc', new UtcClock());
ClockRegistry::setDefault('local');

// Use in application
$localTime = ClockRegistry::get('local')->now();
$utcTime = ClockRegistry::get('utc')->now();
$defaultTime = ClockRegistry::getDefault()->now();

// Check registration
ClockRegistry::has('local'); // true
ClockRegistry::registered(); // ['local', 'utc']

// Clear in tests
ClockRegistry::clear();
```

## Testing Example

Complete test demonstrating workflow:

```php
use Cline\Clock\Clocks\MockClock;
use DateTimeImmutable;

test('complete subscription workflow', function () {
    $clock = new MockClock(new DateTimeImmutable('2025-01-01 00:00:00'));

    $subscription = new Subscription(
        clock: $clock,
        startsAt: new DateTimeImmutable('2025-01-15 00:00:00'),
        endsAt: new DateTimeImmutable('2026-01-15 00:00:00'),
    );

    // Pending
    expect($subscription->isPending())->toBeTrue();
    expect($subscription->isActive())->toBeFalse();

    // Active
    $clock->freezeAt('2025-01-15 00:00:00');
    expect($subscription->isActive())->toBeTrue();
    expect($subscription->daysRemaining())->toBe(365);

    // 6 months later
    $clock->advance('+180 days');
    expect($subscription->daysRemaining())->toBe(185);

    // Near expiration
    $clock->advance('+178 days');
    expect($subscription->daysRemaining())->toBe(7);

    // Renew
    $renewed = $subscription->renew();
    expect($renewed->isActive())->toBeTrue();
});
```

The clock package provides multiple implementations of the PSR-20 `ClockInterface`, each designed for specific use cases.

## Production Clocks

### CarbonImmutableClock

The default clock for Laravel applications. Uses Carbon's immutable datetime instances.

```php
use Cline\Clock\Clocks\CarbonImmutableClock;
use DateTimeZone;

$clock = new CarbonImmutableClock();
$now = $clock->now();

// With timezone
$clock = new CarbonImmutableClock(new DateTimeZone('America/New_York'));
```

**Best for:** Laravel applications, when you need Carbon's rich API.

### CarbonClock

Uses Laravel's Date facade with mutable Carbon instances.

```php
use Cline\Clock\Clocks\CarbonClock;

$clock = new CarbonClock();
$now = $clock->now(); // Converted to DateTimeImmutable
```

**Best for:** Legacy code requiring mutable Carbon instances.

### DateTimeImmutableClock

Uses PHP's native `DateTimeImmutable` without third-party dependencies.

```php
use Cline\Clock\Clocks\DateTimeImmutableClock;

$clock = new DateTimeImmutableClock();
$now = $clock->now();
```

**Best for:** Lightweight applications, maximum compatibility.

### DateTimeClock

Uses PHP's native mutable `DateTime` class.

```php
use Cline\Clock\Clocks\DateTimeClock;

$clock = new DateTimeClock();
$now = $clock->now(); // Converted to DateTimeImmutable
```

**Best for:** Legacy code requiring mutable DateTime.

### UtcClock

Always returns time in UTC timezone regardless of system configuration.

```php
use Cline\Clock\Clocks\UtcClock;

$clock = new UtcClock();
$now = $clock->now(); // Always UTC
```

**Best for:** Distributed systems, API servers, database timestamps, timezone-independent operations.

## Testing Clocks

### FrozenClock

Returns a fixed point in time. Perfect for deterministic testing.

```php
use Cline\Clock\Clocks\FrozenClock;
use DateTimeImmutable;

$fixedTime = new DateTimeImmutable('2025-01-15 12:00:00');
$clock = new FrozenClock($fixedTime);

$clock->now(); // Always 2025-01-15 12:00:00
$clock->now(); // Still 2025-01-15 12:00:00

// From string
$clock = FrozenClock::fromString('2025-01-15 12:00:00');
```

**Best for:** Unit tests requiring fixed timestamps, reproducible test scenarios.

### MockClock

Combines frozen time with manual advancement and sequencing capabilities.

```php
use Cline\Clock\Clocks\MockClock;
use DateTimeImmutable;

$clock = new MockClock(new DateTimeImmutable('2025-01-15 12:00:00'));

// Get current time
$clock->now(); // 2025-01-15 12:00:00

// Freeze at specific time
$clock->freezeAt('2025-06-01 00:00:00');
$clock->now(); // 2025-06-01 00:00:00

// Advance time
$clock->advance('+2 hours');
$clock->now(); // 2025-06-01 02:00:00

// Use DateInterval
$clock->advance(new DateInterval('P1D'));
$clock->now(); // 2025-06-02 02:00:00

// Sequence mode
$clock->useSequence([
    new DateTimeImmutable('2025-01-15 10:00:00'),
    new DateTimeImmutable('2025-01-15 11:00:00'),
    new DateTimeImmutable('2025-01-15 12:00:00'),
]);
$clock->now(); // 10:00:00
$clock->now(); // 11:00:00
$clock->now(); // 12:00:00

// Reset
$clock->reset();
```

**Best for:** Complex testing scenarios, time progression tests, integration tests.

### SequenceClock

Returns a predetermined sequence of datetime values. Throws when exhausted.

```php
use Cline\Clock\Clocks\SequenceClock;
use DateTimeImmutable;

$times = [
    new DateTimeImmutable('2025-01-15 10:00:00'),
    new DateTimeImmutable('2025-01-15 11:00:00'),
    new DateTimeImmutable('2025-01-15 12:00:00'),
];

$clock = new SequenceClock($times);

$clock->now(); // 10:00:00
$clock->now(); // 11:00:00
$clock->now(); // 12:00:00
$clock->now(); // Throws RuntimeException

// Check if more times available
$clock->hasNext(); // false

// Reset to beginning
$clock->reset();
$clock->hasNext(); // true
```

**Best for:** Testing ordered time-dependent operations, batch processing tests.

### TickClock

Allows manual time advancement by specific intervals.

```php
use Cline\Clock\Clocks\TickClock;
use DateInterval;
use DateTimeImmutable;

$clock = new TickClock(new DateTimeImmutable('2025-01-15 12:00:00'));

$clock->now(); // 12:00:00

// Advance with string modifier
$clock->tick('+1 hour');
$clock->now(); // 13:00:00

// Advance with DateInterval
$clock->tick(new DateInterval('PT30M'));
$clock->now(); // 13:30:00

// Jump to specific time
$clock->setTo(new DateTimeImmutable('2025-02-01 00:00:00'));
$clock->now(); // 2025-02-01 00:00:00

// Reset
$clock->reset(new DateTimeImmutable('2025-01-01 00:00:00'));
```

**Best for:** Step-by-step time advancement, simulating scheduled tasks.

### OffsetClock

Wraps another clock and applies a fixed time offset.

```php
use Cline\Clock\Clocks\OffsetClock;
use Cline\Clock\Clocks\CarbonImmutableClock;
use DateInterval;

$baseClock = new CarbonImmutableClock();

// Offset with string
$futureClock = new OffsetClock($baseClock, '+7 days');
$futureClock->now(); // 7 days from now

// Offset with DateInterval
$pastClock = new OffsetClock($baseClock, DateInterval::createFromDateString('-1 month'));
$pastClock->now(); // 1 month ago

// Stack offsets
$innerOffset = new OffsetClock($baseClock, '+1 day');
$outerOffset = new OffsetClock($innerOffset, '+1 hour');
$outerOffset->now(); // 1 day and 1 hour from now
```

**Best for:** Testing future/past scenarios, simulating time zones, time-shift testing.

## Freezable Interface

Production clocks implement `FreezableInterface` for creating frozen snapshots:

```php
use Cline\Clock\Clocks\CarbonImmutableClock;

$clock = new CarbonImmutableClock();
$frozen = $clock->freeze(); // FrozenClock at current time

$clock->now();  // Continues advancing
$frozen->now(); // Fixed at freeze moment
```

## Choosing the Right Clock

### Production

| Scenario | Recommended Clock |
|----------|-------------------|
| Laravel applications | `CarbonImmutableClock` |
| Standalone PHP | `DateTimeImmutableClock` |
| Distributed systems | `UtcClock` |
| UTC database storage | `UtcClock` |

### Testing

| Scenario | Recommended Clock |
|----------|-------------------|
| Fixed timestamp | `FrozenClock` |
| Time progression | `MockClock` or `TickClock` |
| Ordered operations | `SequenceClock` |
| Future/past scenarios | `OffsetClock` |
| Complex scenarios | `MockClock` |

The clock package integrates seamlessly with Laravel through automatic service provider registration and facade support.

## Installation

The service provider and facade are automatically registered via Laravel's package auto-discovery:

```bash
composer require cline/clock
```

No manual configuration required.

## Dependency Injection

The preferred method is injecting `ClockInterface` into your classes:

```php
use Cline\Clock\Contracts\ClockInterface;

class OrderController extends Controller
{
    public function __construct(
        private readonly ClockInterface $clock
    ) {}

    public function store(Request $request)
    {
        $order = Order::create([
            'user_id' => $request->user()->id,
            'total' => $request->total,
            'created_at' => $this->clock->now(),
        ]);

        return response()->json($order, 201);
    }
}
```

## Facade

For convenience, use the `Clock` facade:

```php
use Cline\Clock\Facades\Clock;

class ReportService
{
    public function generateReport(): array
    {
        return [
            'generated_at' => Clock::now(),
            'data' => $this->fetchData(),
        ];
    }
}
```

## Service Container

Access the clock through the service container:

```php
// Using interface
$clock = app(ClockInterface::class);

// Using alias
$clock = app('clock');
```

## Customizing the Default Clock

Override the binding in a service provider:

```php
use Cline\Clock\Clocks\UtcClock;
use Cline\Clock\Contracts\ClockInterface;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // Use UTC clock instead of CarbonImmutableClock
        $this->app->singleton(
            ClockInterface::class,
            fn() => new UtcClock()
        );
    }
}
```

## Testing

Override the clock binding in your tests:

```php
use Cline\Clock\Clocks\FrozenClock;
use Cline\Clock\Contracts\ClockInterface;

class OrderTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->app->singleton(
            ClockInterface::class,
            fn() => new FrozenClock(new DateTimeImmutable('2025-01-15 12:00:00'))
        );
    }

    public function test_creates_order_with_fixed_timestamp(): void
    {
        $response = $this->postJson('/api/orders', [
            'item' => 'widget',
        ]);

        $response->assertStatus(201)
            ->assertJson(['created_at' => '2025-01-15T12:00:00+00:00']);
    }
}
```

## Middleware Example

```php
use Cline\Clock\Contracts\ClockInterface;
use Closure;
use Illuminate\Http\Request;

class RateLimitMiddleware
{
    public function __construct(
        private readonly ClockInterface $clock,
        private readonly RateLimiter $limiter,
    ) {}

    public function handle(Request $request, Closure $next)
    {
        $key = $request->user()?->id ?? $request->ip();

        if ($this->limiter->tooManyAttempts($key)) {
            return response()->json([
                'error' => 'Too many requests',
                'retry_after' => $this->limiter->availableAt($key),
            ], 429);
        }

        $this->limiter->hit($key, $this->clock->now());

        return $next($request);
    }
}
```

## Service Class Example

```php
use Cline\Clock\Contracts\ClockInterface;

class SubscriptionService
{
    public function __construct(
        private readonly ClockInterface $clock
    ) {}

    public function isActive(Subscription $subscription): bool
    {
        $now = $this->clock->now();

        return $subscription->starts_at <= $now
            && $subscription->ends_at >= $now;
    }

    public function daysRemaining(Subscription $subscription): int
    {
        $now = $this->clock->now();

        if ($subscription->ends_at < $now) {
            return 0;
        }

        return $now->diff($subscription->ends_at)->days;
    }

    public function renew(Subscription $subscription): Subscription
    {
        $subscription->update([
            'ends_at' => $this->clock->now()->modify('+1 year'),
            'renewed_at' => $this->clock->now(),
        ]);

        return $subscription->fresh();
    }
}
```

## Artisan Command Example

```php
use Cline\Clock\Contracts\ClockInterface;
use Illuminate\Console\Command;

class CleanupExpiredOrdersCommand extends Command
{
    protected $signature = 'orders:cleanup';
    protected $description = 'Delete expired orders';

    public function __construct(
        private readonly ClockInterface $clock
    ) {
        parent::__construct();
    }

    public function handle(): int
    {
        $deleted = Order::query()
            ->where('expires_at', '<', $this->clock->now())
            ->delete();

        $this->info("Deleted {$deleted} expired orders");

        return self::SUCCESS;
    }
}
```

## Queued Job Example

```php
use Cline\Clock\Contracts\ClockInterface;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;

class ProcessSubscriptionRenewalJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable;

    public function __construct(
        private readonly int $subscriptionId
    ) {}

    public function handle(ClockInterface $clock): void
    {
        $subscription = Subscription::findOrFail($this->subscriptionId);

        if ($subscription->ends_at > $clock->now()) {
            // Not yet expired, reschedule
            $this->release($subscription->ends_at->diffInSeconds($clock->now()));
            return;
        }

        $subscription->update([
            'starts_at' => $clock->now(),
            'ends_at' => $clock->now()->modify('+1 year'),
        ]);
    }
}
```

## Event Listener Example

```php
use Cline\Clock\Contracts\ClockInterface;
use Illuminate\Contracts\Queue\ShouldQueue;

class SendOrderConfirmationEmail implements ShouldQueue
{
    public function __construct(
        private readonly ClockInterface $clock
    ) {}

    public function handle(OrderCreated $event): void
    {
        Mail::to($event->order->user)->send(
            new OrderConfirmation(
                order: $event->order,
                sentAt: $this->clock->now()
            )
        );

        Log::info("Order confirmation sent", [
            'order_id' => $event->order->id,
            'sent_at' => $this->clock->now()->format('Y-m-d H:i:s'),
        ]);
    }
}
```

## Testing Queued Jobs

```php
use Cline\Clock\Clocks\FrozenClock;
use Cline\Clock\Contracts\ClockInterface;

test('processes renewal at correct time', function () {
    $fixedTime = new DateTimeImmutable('2025-01-15 12:00:00');

    $this->app->singleton(
        ClockInterface::class,
        fn() => new FrozenClock($fixedTime)
    );

    $subscription = Subscription::factory()->create([
        'ends_at' => new DateTimeImmutable('2025-01-14 12:00:00'),
    ]);

    ProcessSubscriptionRenewalJob::dispatch($subscription->id);
    $this->artisan('queue:work --once');

    $subscription->refresh();

    expect($subscription->starts_at)->toEqual($fixedTime);
    expect($subscription->ends_at)->toEqual($fixedTime->modify('+1 year'));
});
```

## Best Practices

1. **Always use dependency injection** - Don't call `app(ClockInterface::class)` in business logic
2. **Override in tests** - Use `FrozenClock` or `MockClock` in test setup
3. **Type hint the interface** - Use `ClockInterface`, not concrete implementations
4. **Use the facade sparingly** - Prefer constructor injection for better testability
5. **Test time-dependent logic** - Always write tests for code that depends on time

Testing time-dependent code can be challenging. The clock package provides multiple strategies to make your tests deterministic, fast, and reliable.

## Basic Testing with FrozenClock

The simplest approach is fixing time at a specific point:

```php
use Cline\Clock\Clocks\FrozenClock;
use DateTimeImmutable;

test('order expires after 24 hours', function () {
    $fixedTime = new DateTimeImmutable('2025-01-15 12:00:00');
    $clock = new FrozenClock($fixedTime);

    $order = new Order($clock);
    $order->setExpiresAt(new DateTimeImmutable('2025-01-15 13:00:00'));

    expect($order->isExpired())->toBeFalse();
});
```

## Dependency Injection Pattern

Always inject the clock as a dependency:

```php
class OrderService
{
    public function __construct(
        private readonly ClockInterface $clock
    ) {}

    public function createOrder(array $data): Order
    {
        return new Order(
            data: $data,
            createdAt: $this->clock->now(),
        );
    }
}

// Test
test('creates order with current timestamp', function () {
    $fixedTime = new DateTimeImmutable('2025-01-15 12:00:00');
    $clock = new FrozenClock($fixedTime);
    $service = new OrderService($clock);

    $order = $service->createOrder(['id' => 1]);

    expect($order->createdAt)->toEqual($fixedTime);
});
```

## Testing Time Progression

Use `MockClock` for scenarios involving time advancement:

```php
use Cline\Clock\Clocks\MockClock;

test('session expires after timeout', function () {
    $clock = new MockClock(new DateTimeImmutable('2025-01-15 12:00:00'));
    $session = new Session($clock, timeoutSeconds: 3600);

    $session->start();
    expect($session->isActive())->toBeTrue();

    // Advance 30 minutes
    $clock->advance('+30 minutes');
    expect($session->isActive())->toBeTrue();

    // Advance another 31 minutes (total 61 minutes)
    $clock->advance('+31 minutes');
    expect($session->isActive())->toBeFalse();
});
```

## Testing Ordered Operations

Use `SequenceClock` when testing operations in sequence:

```php
use Cline\Clock\Clocks\SequenceClock;

test('processes batch with incremental timestamps', function () {
    $times = [
        new DateTimeImmutable('2025-01-15 10:00:00'),
        new DateTimeImmutable('2025-01-15 10:01:00'),
        new DateTimeImmutable('2025-01-15 10:02:00'),
    ];

    $clock = new SequenceClock($times);
    $processor = new BatchProcessor($clock);

    $results = $processor->processBatch([
        ['id' => 1],
        ['id' => 2],
        ['id' => 3],
    ]);

    expect($results[0]->processedAt)->toEqual($times[0]);
    expect($results[1]->processedAt)->toEqual($times[1]);
    expect($results[2]->processedAt)->toEqual($times[2]);
});
```

## Testing with TickClock

For regular interval testing:

```php
use Cline\Clock\Clocks\TickClock;

test('scheduler runs tasks at intervals', function () {
    $clock = new TickClock(new DateTimeImmutable('2025-01-15 12:00:00'));
    $scheduler = new TaskScheduler($clock);

    $scheduler->schedule('cleanup', fn() => null, intervalSeconds: 900);

    // First run - should execute
    expect($scheduler->run())->toContain('cleanup');

    // 10 minutes later - should not run (interval is 15 min)
    $clock->tick('+10 minutes');
    expect($scheduler->run())->not()->toContain('cleanup');

    // 5 more minutes - should run
    $clock->tick('+5 minutes');
    expect($scheduler->run())->toContain('cleanup');
});
```

## Testing Future/Past Scenarios

Use `OffsetClock` without complex date math:

```php
use Cline\Clock\Clocks\OffsetClock;
use Cline\Clock\Clocks\FrozenClock;

test('coupon expires in the future', function () {
    $baseClock = new FrozenClock(new DateTimeImmutable('2025-01-15 12:00:00'));
    $futureClock = new OffsetClock($baseClock, '+7 days');

    $coupon = new Coupon(
        expiresAt: new DateTimeImmutable('2025-01-20 12:00:00')
    );

    expect($coupon->isValidAt($baseClock->now()))->toBeTrue();
    expect($coupon->isValidAt($futureClock->now()))->toBeFalse();
});
```

## Laravel Integration Testing

Override the clock binding in Laravel tests:

```php
use Cline\Clock\Clocks\FrozenClock;
use Cline\Clock\Contracts\ClockInterface;

test('creates order with frozen time', function () {
    $fixedTime = new DateTimeImmutable('2025-01-15 12:00:00');

    $this->app->singleton(
        ClockInterface::class,
        fn() => new FrozenClock($fixedTime)
    );

    $response = $this->postJson('/api/orders', ['item' => 'widget']);

    $response->assertStatus(201)
        ->assertJson([
            'created_at' => '2025-01-15T12:00:00+00:00'
        ]);
});
```

## Feature Tests with Time Progression

```php
use Cline\Clock\Clocks\MockClock;
use Cline\Clock\Contracts\ClockInterface;

test('session expires after timeout', function () {
    $clock = new MockClock(new DateTimeImmutable('2025-01-15 12:00:00'));
    $this->app->singleton(ClockInterface::class, fn() => $clock);

    $this->post('/login', [
        'email' => 'user@example.com',
        'password' => 'password',
    ])->assertOk();

    expect($this->isAuthenticated())->toBeTrue();

    // Advance past session timeout
    $clock->advance('+2 hours');

    $this->get('/dashboard')->assertRedirect('/login');
});
```

## Testing Timezone Behavior

```php
use Cline\Clock\Clocks\CarbonImmutableClock;

test('handles timezone conversion', function () {
    $nyClock = new CarbonImmutableClock(new DateTimeZone('America/New_York'));
    $utcClock = new Cline\Clock\Clocks\UtcClock();

    $nyTime = $nyClock->now();
    $utcTime = $utcClock->now();

    // Same moment, different representation
    expect($nyTime->getTimestamp())->toBe($utcTime->getTimestamp());
    expect($nyTime->getTimezone()->getName())->not()->toBe('UTC');
});
```

## Complete Workflow Test

```php
use Cline\Clock\Clocks\MockClock;

test('complete subscription workflow', function () {
    $clock = new MockClock(new DateTimeImmutable('2025-01-01 00:00:00'));

    $subscription = new Subscription(
        clock: $clock,
        startsAt: new DateTimeImmutable('2025-01-15 00:00:00'),
        endsAt: new DateTimeImmutable('2026-01-15 00:00:00'),
    );

    // Before start
    expect($subscription->isPending())->toBeTrue();
    expect($subscription->isActive())->toBeFalse();

    // Fast forward to start
    $clock->freezeAt('2025-01-15 00:00:00');
    expect($subscription->isActive())->toBeTrue();
    expect($subscription->daysRemaining())->toBe(365);

    // Fast forward 6 months
    $clock->advance('+180 days');
    expect($subscription->daysRemaining())->toBe(185);

    // Near expiration
    $clock->advance('+178 days');
    expect($subscription->daysRemaining())->toBe(7);

    // Renew
    $renewed = $subscription->renew();
    expect($renewed->isActive())->toBeTrue();
});
```

## Best Practices

### Always Use Dependency Injection

```php
// Good
class OrderService
{
    public function __construct(
        private readonly ClockInterface $clock
    ) {}
}

// Bad - Hard to test
class OrderService
{
    public function getCurrentTime(): DateTimeImmutable
    {
        return new DateTimeImmutable();
    }
}
```

### Type Hint the Interface

```php
public function __construct(
    private readonly ClockInterface $clock // PSR-20 interface
) {}
```

### Choose the Right Clock

| Test Type | Clock |
|-----------|-------|
| Fixed timestamp | `FrozenClock` |
| Time progression | `MockClock` |
| Ordered operations | `SequenceClock` |
| Regular intervals | `TickClock` |
| Future/past | `OffsetClock` |

### Test Edge Cases

```php
test('handles leap year', function () {
    $clock = new FrozenClock(new DateTimeImmutable('2024-02-29 12:00:00'));
    expect($clock->now()->format('Y-m-d'))->toBe('2024-02-29');
});

test('handles year boundary', function () {
    $clock = new FrozenClock(new DateTimeImmutable('2024-12-31 23:59:59'));
    expect($clock->now()->format('Y'))->toBe('2024');
});

test('handles daylight saving transition', function () {
    $clock = new CarbonImmutableClock(new DateTimeZone('America/New_York'));
    // Spring forward - 2:00 AM becomes 3:00 AM
    $frozen = new FrozenClock(new DateTimeImmutable('2025-03-09 01:59:59', new DateTimeZone('America/New_York')));
    // Test your DST-sensitive logic here
});
```

The clock package includes several utilities to simplify common operations.

## Helper Function

The `clock()` function provides quick access to clock instances:

```php
use function Cline\Clock\clock;
use Cline\Clock\Clocks\FrozenClock;
use DateTimeImmutable;
use DateTimeZone;

// Default CarbonImmutableClock
$clock = clock();
$now = $clock->now();

// With timezone
$clock = clock(timezone: new DateTimeZone('America/New_York'));

// Pass existing clock (returned as-is)
$frozen = new FrozenClock(new DateTimeImmutable('2025-01-15'));
$same = clock($frozen); // Returns $frozen

// Create specific clock type
$clock = clock(FrozenClock::class, frozenTime: new DateTimeImmutable('2025-01-15'));
```

## Clock Registry

Global registry for managing named clock instances:

```php
use Cline\Clock\Support\ClockRegistry;
use Cline\Clock\Clocks\CarbonImmutableClock;
use Cline\Clock\Clocks\UtcClock;
use Cline\Clock\Clocks\FrozenClock;

// Register clocks
ClockRegistry::set('local', new CarbonImmutableClock());
ClockRegistry::set('utc', new UtcClock());

// Set default
ClockRegistry::setDefault('local');

// Retrieve
$local = ClockRegistry::get('local');
$default = ClockRegistry::getDefault();

// Check existence
ClockRegistry::has('utc'); // true
ClockRegistry::hasDefault(); // true

// List all registered
ClockRegistry::registered(); // ['local', 'utc']

// Remove
ClockRegistry::remove('utc');

// Clear all (useful in tests)
ClockRegistry::clear();
```

### Registry Exceptions

```php
// Throws RuntimeException
ClockRegistry::get('nonexistent');

// Throws RuntimeException
ClockRegistry::setDefault('nonexistent');

// Throws RuntimeException
ClockRegistry::getDefault(); // When no default set
```

### Multi-Clock Application

```php
use Cline\Clock\Support\ClockRegistry;
use Cline\Clock\Clocks\CarbonImmutableClock;
use Cline\Clock\Clocks\UtcClock;
use DateTimeZone;

// Bootstrap in service provider
ClockRegistry::set('app', new CarbonImmutableClock());
ClockRegistry::set('utc', new UtcClock());
ClockRegistry::set('tokyo', new CarbonImmutableClock(new DateTimeZone('Asia/Tokyo')));
ClockRegistry::setDefault('app');

// Use throughout application
class OrderService
{
    public function createOrder(array $data): Order
    {
        return new Order(
            data: $data,
            createdAt: ClockRegistry::getDefault()->now(),
            createdAtUtc: ClockRegistry::get('utc')->now(),
        );
    }
}
```

## Clock Comparison Trait

The `ClockComparison` trait adds comparison methods to clock implementations:

```php
use Cline\Clock\Contracts\ClockInterface;
use Cline\Clock\Support\ClockComparison;
use DateTimeImmutable;

final class MyCustomClock implements ClockInterface
{
    use ClockComparison;

    public function now(): DateTimeImmutable
    {
        return new DateTimeImmutable();
    }
}

$clock = new MyCustomClock();
$reference = new DateTimeImmutable('2025-01-15 12:00:00');

// Comparisons
$clock->isAfter($reference);  // true if now > reference
$clock->isBefore($reference); // true if now < reference
$clock->isSameAs($reference); // true if same timestamp
$clock->isBetween($start, $end); // true if within range (inclusive)

// Differences
$clock->diffInSeconds($reference);
$clock->diffInMinutes($reference);
$clock->diffInHours($reference);
$clock->diffInDays($reference);
```

### Comparison Methods

| Method | Returns | Description |
|--------|---------|-------------|
| `isAfter($time)` | `bool` | Clock time is after given time |
| `isBefore($time)` | `bool` | Clock time is before given time |
| `isSameAs($time)` | `bool` | Same Unix timestamp |
| `isBetween($start, $end)` | `bool` | Within range (inclusive) |
| `diffInSeconds($time)` | `int` | Absolute difference in seconds |
| `diffInMinutes($time)` | `int` | Absolute difference in minutes |
| `diffInHours($time)` | `int` | Absolute difference in hours |
| `diffInDays($time)` | `int` | Absolute difference in days |

### Example Usage

```php
use Cline\Clock\Clocks\FrozenClock;
use Cline\Clock\Support\ClockComparison;
use DateTimeImmutable;

// Create a clock that uses the comparison trait
final class ComparableFrozenClock extends FrozenClock
{
    use ClockComparison;
}

$clock = new ComparableFrozenClock(new DateTimeImmutable('2025-01-15 12:00:00'));

$past = new DateTimeImmutable('2025-01-10 12:00:00');
$future = new DateTimeImmutable('2025-01-20 12:00:00');

// Time comparisons
$clock->isAfter($past);   // true
$clock->isBefore($future); // true
$clock->isBetween($past, $future); // true

// Differences
$clock->diffInDays($past);   // 5
$clock->diffInDays($future); // 5 (absolute)
```

## Freezable Interface

Production clocks implement `FreezableInterface` for creating frozen snapshots:

```php
use Cline\Clock\Clocks\CarbonImmutableClock;

$clock = new CarbonImmutableClock();

// Create frozen snapshot
$frozen = $clock->freeze();

// Original continues
sleep(1);
$clock->now();  // Current time
$frozen->now(); // Time when freeze() was called
```

### Implementing Freezable

```php
use Cline\Clock\Contracts\ClockInterface;
use Cline\Clock\Contracts\FreezableInterface;
use Cline\Clock\Clocks\FrozenClock;
use DateTimeImmutable;

final readonly class MyCustomClock implements ClockInterface, FreezableInterface
{
    public function now(): DateTimeImmutable
    {
        return new DateTimeImmutable();
    }

    public function freeze(): FrozenClock
    {
        return new FrozenClock($this->now());
    }
}
```
