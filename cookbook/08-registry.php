<?php

declare(strict_types=1);

/**
 * Clock Registry
 *
 * This example shows how to use ClockRegistry to manage
 * multiple named clock instances.
 */

use Cline\Clock\Clocks\FrozenClock;
use Cline\Clock\Clocks\UtcClock;
use Cline\Clock\Support\ClockRegistry;

require __DIR__.'/../vendor/autoload.php';

echo "=== Clock Registry ===" . PHP_EOL . PHP_EOL;

// Register multiple clocks
echo "1. Registering Clocks" . PHP_EOL;
ClockRegistry::set('utc', new UtcClock());
ClockRegistry::set('test', new FrozenClock(new \DateTimeImmutable('2025-01-15 12:00:00')));

echo "   Registered: " . implode(', ', ClockRegistry::registered()) . PHP_EOL . PHP_EOL;

// Retrieve clocks
echo "2. Retrieving Clocks" . PHP_EOL;
$utcClock = ClockRegistry::get('utc');
$testClock = ClockRegistry::get('test');

echo "   UTC time:  " . $utcClock->now()->format('Y-m-d H:i:s T') . PHP_EOL;
echo "   Test time: " . $testClock->now()->format('Y-m-d H:i:s') . PHP_EOL . PHP_EOL;

// Default clock
echo "3. Default Clock" . PHP_EOL;
ClockRegistry::setDefault('utc');
$defaultClock = ClockRegistry::getDefault();

echo "   Default: " . $defaultClock->now()->format('Y-m-d H:i:s T') . PHP_EOL . PHP_EOL;

// Check existence
echo "4. Checking Existence" . PHP_EOL;
echo "   Has 'utc': " . (ClockRegistry::has('utc') ? 'Yes' : 'No') . PHP_EOL;
echo "   Has 'missing': " . (ClockRegistry::has('missing') ? 'Yes' : 'No') . PHP_EOL;
