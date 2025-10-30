<?php

declare(strict_types=1);

/**
 * Clock Decorators
 *
 * This example demonstrates using decorators to add functionality
 * to existing clocks.
 */

use Cline\Clock\Clocks\CarbonImmutableClock;
use Cline\Clock\Clocks\TickClock;
use Cline\Clock\Decorators\CachingClock;

require __DIR__.'/../vendor/autoload.php';

echo "=== Clock Decorators ===" . PHP_EOL . PHP_EOL;

// CachingClock - prevent excessive clock calls
echo "1. CachingClock - Performance Optimization" . PHP_EOL;
$baseClock = new TickClock(new \DateTimeImmutable('2025-01-15 12:00:00'));
$cachingClock = new CachingClock($baseClock, 2);

echo "   First call:  " . $cachingClock->now()->format('H:i:s') . PHP_EOL;

$baseClock->tick('+1 hour');
echo "   After tick:  " . $cachingClock->now()->format('H:i:s') . " (cached)" . PHP_EOL;

sleep(3);
echo "   After TTL:   " . $cachingClock->now()->format('H:i:s') . " (refreshed)" . PHP_EOL . PHP_EOL;

// Manual cache clearing
echo "2. Manual Cache Control" . PHP_EOL;
$cachingClock2 = new CachingClock(new CarbonImmutableClock(), 60);

$time1 = $cachingClock2->now();
echo "   Cached time: " . $time1->format('H:i:s') . PHP_EOL;

sleep(1);
$cachingClock2->clear();
$time2 = $cachingClock2->now();
echo "   After clear: " . $time2->format('H:i:s') . " (different)" . PHP_EOL;
