# Laravel Integration

The clock package integrates seamlessly with Laravel through automatic service provider registration and facade support.

## Installation

The service provider and facade are automatically registered via Laravel's package auto-discovery:

```bash
composer require cline/clock
```

No manual configuration required!

## Using Dependency Injection

The preferred method is injecting `ClockInterface` into your classes:

```php
use Cline\Clock\Contracts\ClockInterface;
use DateTimeImmutable;

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

## Using the Facade

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
$clock = app(ClockInterface::class);
$now = $clock->now();

// Or using the alias
$clock = app('clock');
$now = $clock->now();
```

## Testing in Laravel

Override the clock binding in your tests:

```php
use Cline\Clock\Clocks\FrozenClock;
use Cline\Clock\Contracts\ClockInterface;
use Tests\TestCase;

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
            'quantity' => 1,
        ]);

        $response->assertStatus(201)
            ->assertJson([
                'created_at' => '2025-01-15T12:00:00+00:00'
            ]);
    }
}
```

## Feature Tests with Time Progression

Test scenarios that involve time passing:

```php
use Cline\Clock\Clocks\MockClock;
use Cline\Clock\Contracts\ClockInterface;

test('session expires after timeout', function () {
    $clock = new MockClock(new DateTimeImmutable('2025-01-15 12:00:00'));

    $this->app->singleton(ClockInterface::class, fn() => $clock);

    $response = $this->post('/login', [
        'email' => 'user@example.com',
        'password' => 'password',
    ]);

    $response->assertOk();
    expect($this->isAuthenticated())->toBeTrue();

    // Advance 2 hours (session timeout is 1 hour)
    $clock->advance(hours: 2);

    $response = $this->get('/dashboard');
    $response->assertRedirect('/login');
});
```

## Middleware Example

Create middleware that uses the clock:

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

        if ($this->limiter->tooManyAttempts($key, $this->clock->now())) {
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

Build services with testable time logic:

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

## Model Integration

Inject the clock into models when needed:

```php
use Cline\Clock\Contracts\ClockInterface;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    private ?ClockInterface $clock = null;

    public function setClock(ClockInterface $clock): void
    {
        $this->clock = $clock;
    }

    public function isExpired(): bool
    {
        $clock = $this->clock ?? app(ClockInterface::class);

        return $this->expires_at < $clock->now();
    }

    public function expiresIn(): string
    {
        $clock = $this->clock ?? app(ClockInterface::class);
        $now = $clock->now();

        if ($this->expires_at < $now) {
            return 'Expired';
        }

        $diff = $now->diff($this->expires_at);

        if ($diff->days > 0) {
            return "{$diff->days} days";
        }

        if ($diff->h > 0) {
            return "{$diff->h} hours";
        }

        return "{$diff->i} minutes";
    }
}
```

## Command Example

Use the clock in Artisan commands:

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
        $now = $this->clock->now();

        $deleted = Order::query()
            ->where('expires_at', '<', $now)
            ->delete();

        $this->info("Deleted {$deleted} expired orders");

        return self::SUCCESS;
    }
}
```

## Job Example

Use the clock in queued jobs:

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

        // Process renewal
        $subscription->update([
            'starts_at' => $clock->now(),
            'ends_at' => $clock->now()->modify('+1 year'),
        ]);
    }
}
```

## Event Listener Example

Handle events with clock awareness:

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
        $order = $event->order;

        Mail::to($order->user)->send(
            new OrderConfirmation(
                order: $order,
                sentAt: $this->clock->now()
            )
        );

        Log::info("Order confirmation sent", [
            'order_id' => $order->id,
            'sent_at' => $this->clock->now()->format('Y-m-d H:i:s'),
        ]);
    }
}
```

## Testing Jobs and Events

Test queued jobs with frozen time:

```php
use Cline\Clock\Clocks\FrozenClock;
use Cline\Clock\Contracts\ClockInterface;

test('processes renewal job at correct time', function () {
    $fixedTime = new DateTimeImmutable('2025-01-15 12:00:00');

    $this->app->singleton(
        ClockInterface::class,
        fn() => new FrozenClock($fixedTime)
    );

    $subscription = Subscription::factory()->create([
        'ends_at' => new DateTimeImmutable('2025-01-14 12:00:00'), // Expired
    ]);

    ProcessSubscriptionRenewalJob::dispatch($subscription->id);

    $this->artisan('queue:work --once');

    $subscription->refresh();

    expect($subscription->starts_at)->toEqual($fixedTime);
    expect($subscription->ends_at)->toEqual($fixedTime->modify('+1 year'));
});
```

## Configuration

The package uses sensible defaults, but you can customize the clock implementation by rebinding in a service provider:

```php
use Cline\Clock\Clocks\UtcClock;
use Cline\Clock\Contracts\ClockInterface;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // Use UTC clock instead of default CarbonImmutableClock
        $this->app->singleton(
            ClockInterface::class,
            fn() => new UtcClock()
        );
    }
}
```

## Best Practices

1. **Always use dependency injection** - Don't call `app(ClockInterface::class)` directly in business logic
2. **Override in tests** - Use `FrozenClock` or `MockClock` in test setup
3. **Type hint the interface** - Use `ClockInterface`, not concrete implementations
4. **Use the facade sparingly** - Prefer constructor injection for better testability
5. **Test time-dependent logic** - Always write tests for code that depends on time
