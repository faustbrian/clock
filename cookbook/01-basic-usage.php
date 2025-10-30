<?php

declare(strict_types=1);

/**
 * Basic Clock Usage
 *
 * This example demonstrates the basic usage of the clock() function
 * and different clock implementations.
 */

use Cline\Clock\Clocks\CarbonImmutableClock;
use function Cline\Clock\clock;

require __DIR__.'/../vendor/autoload.php';

// Get default clock (CarbonImmutableClock)
$clock = clock();
echo 'Default clock: '.$clock->now()->format('Y-m-d H:i:s').PHP_EOL;

// Instantiate specific clock using class string
$carbonClock = clock(CarbonImmutableClock::class);
echo 'Carbon Immutable: '.$carbonClock->now()->format('Y-m-d H:i:s').PHP_EOL;
