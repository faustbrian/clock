<?php

declare(strict_types=1);

/**
 * Advanced Testing with Mock and Sequence Clocks
 *
 * This example demonstrates advanced testing scenarios using
 * MockClock and SequenceClock.
 */

use Cline\Clock\Clocks\MockClock;
use Cline\Clock\Clocks\SequenceClock;

require __DIR__.'/../vendor/autoload.php';

echo "=== Advanced Testing Scenarios ===" . PHP_EOL . PHP_EOL;

// MockClock - freeze, advance, and sequence
echo "1. MockClock - Combined Features" . PHP_EOL;
$mock = new MockClock();

$mock->freezeAt('2025-01-15 09:00:00');
echo "   Frozen at 09:00: " . $mock->now()->format('H:i:s') . PHP_EOL;

$mock->advance('+3 hours');
echo "   After +3h:       " . $mock->now()->format('H:i:s') . PHP_EOL;

$mock->advance('-30 minutes');
echo "   After -30m:      " . $mock->now()->format('H:i:s') . PHP_EOL . PHP_EOL;

// SequenceClock - multiple time points
echo "2. SequenceClock - Simulating Time Progression" . PHP_EOL;
$sequence = new SequenceClock([
    new \DateTimeImmutable('2025-01-15 09:00:00'),
    new \DateTimeImmutable('2025-01-15 12:00:00'),
    new \DateTimeImmutable('2025-01-15 17:00:00'),
]);

echo "   First call:  " . $sequence->now()->format('H:i:s') . PHP_EOL;
echo "   Second call: " . $sequence->now()->format('H:i:s') . PHP_EOL;
echo "   Third call:  " . $sequence->now()->format('H:i:s') . PHP_EOL;
echo "   Has next:    " . ($sequence->hasNext() ? 'Yes' : 'No') . PHP_EOL . PHP_EOL;

// MockClock with sequence
echo "3. MockClock with Sequence" . PHP_EOL;
$mock->useSequence([
    new \DateTimeImmutable('2025-01-01 00:00:00'),
    new \DateTimeImmutable('2025-06-01 12:00:00'),
    new \DateTimeImmutable('2025-12-31 23:59:59'),
]);

echo "   New Year:  " . $mock->now()->format('Y-m-d H:i:s') . PHP_EOL;
echo "   Mid Year:  " . $mock->now()->format('Y-m-d H:i:s') . PHP_EOL;
echo "   Year End:  " . $mock->now()->format('Y-m-d H:i:s') . PHP_EOL;
