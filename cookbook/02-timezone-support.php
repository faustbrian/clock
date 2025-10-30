<?php

declare(strict_types=1);

/**
 * Timezone Support
 *
 * This example shows how to use clocks with different timezones.
 */

use Cline\Clock\Clocks\DateTimeImmutableClock;
use function Cline\Clock\clock;

require __DIR__.'/../vendor/autoload.php';

// Default timezone (system timezone)
$defaultClock = clock();
echo 'System time: '.$defaultClock->now()->format('Y-m-d H:i:s T').PHP_EOL;

// New York timezone
$nyClock = clock(timezone: new DateTimeZone('America/New_York'));
echo 'New York: '.$nyClock->now()->format('Y-m-d H:i:s T').PHP_EOL;

// London timezone
$londonClock = clock(DateTimeImmutableClock::class, new DateTimeZone('Europe/London'));
echo 'London: '.$londonClock->now()->format('Y-m-d H:i:s T').PHP_EOL;

// Tokyo timezone
$tokyoClock = clock(DateTimeImmutableClock::class, new DateTimeZone('Asia/Tokyo'));
echo 'Tokyo: '.$tokyoClock->now()->format('Y-m-d H:i:s T').PHP_EOL;
