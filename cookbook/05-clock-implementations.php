<?php

declare(strict_types=1);

/**
 * Clock Implementations Overview
 *
 * This example demonstrates all available clock implementations
 * and their specific use cases.
 */

use Cline\Clock\Clocks\CarbonClock;
use Cline\Clock\Clocks\CarbonImmutableClock;
use Cline\Clock\Clocks\DateTimeClock;
use Cline\Clock\Clocks\DateTimeImmutableClock;
use Cline\Clock\Clocks\FrozenClock;

require __DIR__.'/../vendor/autoload.php';

echo "=== Clock Implementations ===" . PHP_EOL . PHP_EOL;

// CarbonClock - Uses Laravel's Date facade with Carbon
echo "1. CarbonClock (Laravel Date facade)" . PHP_EOL;
$carbonClock = new CarbonClock();
echo "   Current time: " . $carbonClock->now()->format('Y-m-d H:i:s') . PHP_EOL;
echo "   Use case: Laravel apps using Date facade" . PHP_EOL . PHP_EOL;

// CarbonImmutableClock - Uses CarbonImmutable (default)
echo "2. CarbonImmutableClock (CarbonImmutable)" . PHP_EOL;
$carbonImmutableClock = new CarbonImmutableClock();
echo "   Current time: " . $carbonImmutableClock->now()->format('Y-m-d H:i:s') . PHP_EOL;
echo "   Use case: Modern PHP with Carbon, immutability guaranteed" . PHP_EOL . PHP_EOL;

// DateTimeClock - Uses native DateTime (mutable)
echo "3. DateTimeClock (Native DateTime)" . PHP_EOL;
$dateTimeClock = new DateTimeClock();
echo "   Current time: " . $dateTimeClock->now()->format('Y-m-d H:i:s') . PHP_EOL;
echo "   Use case: Fallback to native PHP DateTime" . PHP_EOL . PHP_EOL;

// DateTimeImmutableClock - Uses native DateTimeImmutable
echo "4. DateTimeImmutableClock (Native DateTimeImmutable)" . PHP_EOL;
$dateTimeImmutableClock = new DateTimeImmutableClock();
echo "   Current time: " . $dateTimeImmutableClock->now()->format('Y-m-d H:i:s') . PHP_EOL;
echo "   Use case: Zero dependencies, PSR-20 compliant" . PHP_EOL . PHP_EOL;

// FrozenClock - Fixed time for testing
echo "5. FrozenClock (Fixed time)" . PHP_EOL;
$frozenClock = FrozenClock::fromString('2025-01-15 12:00:00');
echo "   Frozen time: " . $frozenClock->now()->format('Y-m-d H:i:s') . PHP_EOL;
echo "   Use case: Testing, reproducible timestamps" . PHP_EOL . PHP_EOL;
