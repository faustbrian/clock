<?php

declare(strict_types=1);

/**
 * Frozen Clock for Testing
 *
 * This example demonstrates how to use FrozenClock for testing
 * time-dependent code with fixed timestamps.
 */

use Cline\Clock\Clocks\FrozenClock;
use function Cline\Clock\clock;

require __DIR__.'/../vendor/autoload.php;

// Create a frozen clock at a specific time
$frozenTime = new DateTimeImmutable('2025-01-15 12:00:00');
$frozenClock = clock(FrozenClock::class, frozenTime: $frozenTime);

echo 'Frozen time: '.$frozenClock->now()->format('Y-m-d H:i:s').PHP_EOL;

// Time never advances
sleep(2);
echo 'After 2 seconds: '.$frozenClock->now()->format('Y-m-d H:i:s').PHP_EOL;

// Using the static factory method
$frozenClock2 = FrozenClock::fromString('2024-12-25 00:00:00');
echo 'Christmas: '.$frozenClock2->now()->format('Y-m-d H:i:s').PHP_EOL;

// Pass frozen clock instance directly
$specificTime = new DateTimeImmutable('2025-06-01 14:30:00');
$clock = clock(new FrozenClock($specificTime));
echo 'Specific time: '.$clock->now()->format('Y-m-d H:i:s').PHP_EOL;
