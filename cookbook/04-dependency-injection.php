<?php

declare(strict_types=1);

/**
 * Dependency Injection
 *
 * This example shows how to use clock implementations with
 * dependency injection for testable code.
 */

use Cline\Clock\Clocks\FrozenClock;
use Cline\Clock\Contracts\ClockInterface;
use function Cline\Clock\clock;

require __DIR__.'/../vendor/autoload.php';

class OrderService
{
    public function __construct(
        private readonly ClockInterface $clock,
    ) {}

    public function createOrder(string $productName): array
    {
        return [
            'product' => $productName,
            'ordered_at' => $this->clock->now()->format('Y-m-d H:i:s'),
        ];
    }

    public function isOrderExpired(DateTimeImmutable $orderedAt, int $expiryHours = 24): bool
    {
        $expiryTime = $orderedAt->modify("+{$expiryHours} hours");

        return $this->clock->now() >= $expiryTime;
    }
}

// Production: Use real clock
$productionService = new OrderService(clock());
$order = $productionService->createOrder('Laptop');
echo 'Production order: '.json_encode($order).PHP_EOL;

// Testing: Use frozen clock
$testClock = new FrozenClock(new DateTimeImmutable('2025-01-15 12:00:00'));
$testService = new OrderService($testClock);

$testOrder = $testService->createOrder('Mouse');
echo 'Test order: '.json_encode($testOrder).PHP_EOL;

// Test expiry check
$orderTime = new DateTimeImmutable('2025-01-14 12:00:00');
$isExpired = $testService->isOrderExpired($orderTime, 24);
echo 'Order expired: '.($isExpired ? 'Yes' : 'No').PHP_EOL;
