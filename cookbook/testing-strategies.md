# Testing Strategies

Testing time-dependent code can be challenging. The clock package provides multiple strategies to make your tests deterministic, fast, and reliable.

## Basic Testing with FrozenClock

The simplest approach is using `FrozenClock` to fix time at a specific point.

```php
use Cline\Clock\Clocks\FrozenClock;
use DateTimeImmutable;

test('order expiration check', function () {
    $fixedTime = new DateTimeImmutable('2025-01-15 12:00:00');
    $clock = new FrozenClock($fixedTime);

    $order = new Order($clock);
    $order->setExpiresAt(new DateTimeImmutable('2025-01-15 13:00:00'));

    expect($order->isExpired())->toBeFalse();
});
```

## Dependency Injection Pattern

Always inject the clock as a dependency for testable code.

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

## Laravel Integration Testing

Override the clock binding in Laravel tests.

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

## Testing Time Progression

Use `MockClock` to test scenarios involving time advancement.

```php
use Cline\Clock\Clocks\MockClock;

test('session expires after timeout', function () {
    $clock = new MockClock(new DateTimeImmutable('2025-01-15 12:00:00'));
    $session = new Session($clock, timeout: 3600); // 1 hour timeout

    $session->start();
    expect($session->isActive())->toBeTrue();

    // Advance 30 minutes
    $clock->advance(minutes: 30);
    expect($session->isActive())->toBeTrue();

    // Advance another 31 minutes (total 61 minutes)
    $clock->advance(minutes: 31);
    expect($session->isActive())->toBeFalse();
});
```

## Testing Ordered Operations

Use `SequenceClock` when testing operations that happen in sequence.

```php
use Cline\Clock\Clocks\SequenceClock;

test('processes batch with incremental timestamps', function () {
    $times = [
        new DateTimeImmutable('2025-01-15 10:00:00'),
        new DateTimeImmutable('2025-01-15 10:01:00'),
        new DateTimeImmutable('2025-01-15 10:02:00'),
    ];

    $clock = new SequenceClock(...$times);
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

## Testing Time-Based Calculations

Test difference calculations with fixed times.

```php
use Cline\Clock\Clocks\FrozenClock;

test('calculates subscription days remaining', function () {
    $clock = new FrozenClock(new DateTimeImmutable('2025-01-15 12:00:00'));

    $subscription = new Subscription(
        clock: $clock,
        expiresAt: new DateTimeImmutable('2025-01-30 12:00:00')
    );

    expect($subscription->daysRemaining())->toBe(15);
});
```

## Testing Timezone Behavior

Test timezone-sensitive operations with explicit timezones.

```php
use Cline\Clock\Clocks\CarbonImmutableClock;

test('converts order time to local timezone', function () {
    $nyClock = new CarbonImmutableClock(timezone: 'America/New_York');
    $tokyoClock = new CarbonImmutableClock(timezone: 'Asia/Tokyo');

    $order = new Order($nyClock);
    $nyTime = $order->getCreatedAt();

    $tokyoTime = $nyTime->setTimezone(new DateTimeZone('Asia/Tokyo'));

    expect($nyTime->format('H:i'))->not()->toBe($tokyoTime->format('H:i'));
});
```

## Testing with TickClock

Test scenarios requiring regular time intervals.

```php
use Cline\Clock\Clocks\TickClock;

test('scheduler runs tasks at intervals', function () {
    $clock = new TickClock(
        start: new DateTimeImmutable('2025-01-15 12:00:00'),
        interval: new DateInterval('PT15M') // 15 minutes
    );

    $scheduler = new TaskScheduler($clock);
    $results = [];

    for ($i = 0; $i < 4; $i++) {
        $results[] = $scheduler->runNextTask();
        $clock->tick();
    }

    expect($results)->toHaveCount(4);
    expect($results[0]->runAt)->toEqual(new DateTimeImmutable('2025-01-15 12:00:00'));
    expect($results[3]->runAt)->toEqual(new DateTimeImmutable('2025-01-15 12:45:00'));
});
```

## Testing with OffsetClock

Test future or past scenarios without complex date math.

```php
use Cline\Clock\Clocks\OffsetClock;
use Cline\Clock\Clocks\FrozenClock;

test('validates coupon expiration', function () {
    $baseClock = new FrozenClock(new DateTimeImmutable('2025-01-15 12:00:00'));

    // Test 7 days in the future
    $futureClock = new OffsetClock(
        clock: $baseClock,
        offset: new DateInterval('P7D')
    );

    $coupon = new Coupon(
        expiresAt: new DateTimeImmutable('2025-01-20 12:00:00')
    );

    expect($coupon->isValidAt($baseClock->now()))->toBeTrue();
    expect($coupon->isValidAt($futureClock->now()))->toBeFalse();
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

// Bad
class OrderService
{
    public function getCurrentTime(): DateTimeImmutable
    {
        return new DateTimeImmutable(); // Hard to test!
    }
}
```

### Use Type Hints

```php
public function __construct(
    private readonly ClockInterface $clock // PSR-20 interface
) {}
```

### Avoid Global State

```php
// Bad
function processOrder(): void
{
    $now = clock()->now(); // Global state
}

// Good
function processOrder(ClockInterface $clock): void
{
    $now = $clock->now(); // Injected dependency
}
```

### Choose the Right Clock

- **Simple tests**: `FrozenClock`
- **Time progression**: `MockClock` or `TickClock`
- **Ordered operations**: `SequenceClock`
- **Future/past testing**: `OffsetClock`

### Test Edge Cases

```php
test('handles leap year correctly', function () {
    $clock = new FrozenClock(new DateTimeImmutable('2024-02-29 12:00:00'));
    $service = new DateService($clock);

    expect($service->isLeapYear())->toBeTrue();
});

test('handles year boundary', function () {
    $clock = new FrozenClock(new DateTimeImmutable('2024-12-31 23:59:59'));
    $service = new DateService($clock);

    expect($service->getCurrentYear())->toBe(2024);
});
```
