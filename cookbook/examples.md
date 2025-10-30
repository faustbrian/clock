# Real-World Examples

Practical examples demonstrating common use cases for the clock package.

## Rate Limiting

Implement rate limiting with time-based token bucket:

```php
use Cline\Clock\Contracts\ClockInterface;

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

        $now = $this->clock->now();

        return $now->diff($this->endsAt)->days;
    }

    public function renew(int $months = 12): self
    {
        $newStartsAt = $this->isExpired()
            ? $this->clock->now()
            : $this->endsAt;

        $newEndsAt = $newStartsAt->modify("+{$months} months");

        return new self($this->clock, $newStartsAt, $newEndsAt);
    }

    public function willRenewAt(DateTimeImmutable $time): bool
    {
        // Check if renewal would happen within 7 days of expiration
        $renewalWindow = $this->endsAt->modify('-7 days');

        return $time >= $renewalWindow && $time <= $this->endsAt;
    }
}
```

## Coupon System

Implement time-sensitive discount coupons:

```php
use Cline\Clock\Contracts\ClockInterface;

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

        $now = $this->clock->now();
        $diff = $now->diff($this->validUntil);

        return ($diff->days * 24) + $diff->h;
    }

    public function use(): void
    {
        if (!$this->isValid()) {
            throw new InvalidCouponException("Coupon is not valid");
        }

        $this->usageCount++;
    }
}
```

## Task Scheduler

Schedule and execute tasks at specific intervals:

```php
use Cline\Clock\Contracts\ClockInterface;

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

        $interval = $this->tasks[$id]['interval'];

        return $this->lastRun[$id]->modify("+{$interval} seconds");
    }
}
```

## Cache with TTL

Implement time-aware caching:

```php
use Cline\Clock\Contracts\ClockInterface;

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

    public function forget(string $key): void
    {
        unset($this->store[$key]);
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
        $now = $this->clock->now();

        return max(0, $expiresAt->getTimestamp() - $now->getTimestamp());
    }
}
```

## Event Logger with Timestamps

Log events with precise timing:

```php
use Cline\Clock\Contracts\ClockInterface;

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

    public function countEventsSince(DateTimeImmutable $since): int
    {
        return count($this->getEvents(since: $since));
    }
}
```

## Retry Logic with Backoff

Implement retry with exponential backoff:

```php
use Cline\Clock\Contracts\ClockInterface;

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

        $lastAttempt = end($attempts);
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

## Testing Example

Comprehensive test demonstrating multiple patterns:

```php
use Cline\Clock\Clocks\MockClock;

test('complete subscription workflow', function () {
    $clock = new MockClock(new DateTimeImmutable('2025-01-01 00:00:00'));

    // Create pending subscription
    $subscription = new Subscription(
        clock: $clock,
        startsAt: new DateTimeImmutable('2025-01-15 00:00:00'),
        endsAt: new DateTimeImmutable('2026-01-15 00:00:00'),
    );

    expect($subscription->isPending())->toBeTrue();
    expect($subscription->isActive())->toBeFalse();

    // Fast forward to start date
    $clock->advance(days: 15);
    expect($subscription->isActive())->toBeTrue();
    expect($subscription->daysRemaining())->toBe(365);

    // Fast forward 6 months
    $clock->advance(days: 180);
    expect($subscription->isActive())->toBeTrue();
    expect($subscription->daysRemaining())->toBe(185);

    // Fast forward to near expiration
    $clock->advance(days: 178);
    expect($subscription->daysRemaining())->toBe(7);
    expect($subscription->willRenewAt($clock->now()))->toBeTrue();

    // Renew subscription
    $renewed = $subscription->renew();
    expect($renewed->isActive())->toBeTrue();
});
```
