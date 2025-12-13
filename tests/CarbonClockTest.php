<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use Cline\Clock\Clocks\CarbonClock;
use Illuminate\Support\Sleep;

test('it returns current time', function (): void {
    $clock = new CarbonClock();
    $now = $clock->now();

    expect($now)->toBeInstanceOf(DateTimeImmutable::class);
});

test('it returns time with timezone', function (): void {
    $timezone = new DateTimeZone('America/New_York');
    $clock = new CarbonClock($timezone);
    $now = $clock->now();

    expect($now->getTimezone()->getName())->toBe('America/New_York');
});

test('it returns different times on successive calls', function (): void {
    $clock = new CarbonClock();
    $time1 = $clock->now();
    Sleep::usleep(1_000);
    $time2 = $clock->now();

    expect($time2)->toBeGreaterThanOrEqual($time1);
});
