<?php

declare(strict_types=1);

/**
 * Laravel Integration
 *
 * This example demonstrates how to use the clock package
 * with Laravel's service container and facades.
 */

// The service provider and facade are automatically registered
// via Laravel's package auto-discovery in composer.json

// Example 1: Dependency Injection
class OrderProcessor
{
    public function __construct(
        private readonly \Cline\Clock\Contracts\ClockInterface $clock,
    ) {}

    public function processOrder(array $orderData): array
    {
        return [
            'id' => $orderData['id'],
            'processed_at' => $this->clock->now()->format('Y-m-d H:i:s'),
            'status' => 'processed',
        ];
    }
}

// Example 2: Using the Facade
// use Cline\Clock\Facades\Clock;
//
// $currentTime = Clock::now();
// echo "Current time: " . $currentTime->format('Y-m-d H:i:s');

// Example 3: Testing with Frozen Clock
// In your test:
// $this->app->singleton(
//     \Cline\Clock\Contracts\ClockInterface::class,
//     fn() => new \Cline\Clock\Clocks\FrozenClock(
//         new \DateTimeImmutable('2025-01-15 12:00:00')
//     )
// );
//
// $processor = app(OrderProcessor::class);
// $result = $processor->processOrder(['id' => 1]);
// $this->assertEquals('2025-01-15 12:00:00', $result['processed_at']);

echo "See comments in this file for Laravel integration examples." . PHP_EOL;
