<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use Cline\Clock\Clocks\CarbonClock;
use Cline\Clock\Clocks\CarbonImmutableClock;
use Cline\Clock\Clocks\DateTimeClock;
use Cline\Clock\Clocks\DateTimeImmutableClock;
use Cline\Clock\Clocks\FrozenClock;
use Cline\Clock\Clocks\UtcClock;
use Illuminate\Support\Sleep;

test('CarbonClock can be frozen', function (): void {
    $clock = new CarbonClock();
    $frozen = $clock->freeze();

    expect($frozen)->toBeInstanceOf(FrozenClock::class);
});

test('CarbonImmutableClock can be frozen', function (): void {
    $clock = new CarbonImmutableClock();
    $frozen = $clock->freeze();

    expect($frozen)->toBeInstanceOf(FrozenClock::class);
});

test('DateTimeClock can be frozen', function (): void {
    $clock = new DateTimeClock();
    $frozen = $clock->freeze();

    expect($frozen)->toBeInstanceOf(FrozenClock::class);
});

test('DateTimeImmutableClock can be frozen', function (): void {
    $clock = new DateTimeImmutableClock();
    $frozen = $clock->freeze();

    expect($frozen)->toBeInstanceOf(FrozenClock::class);
});

test('UtcClock can be frozen', function (): void {
    $clock = new UtcClock();
    $frozen = $clock->freeze();

    expect($frozen)->toBeInstanceOf(FrozenClock::class);
});

test('frozen clock returns same time', function (): void {
    $clock = new CarbonImmutableClock();
    $frozen = $clock->freeze();

    $time1 = $frozen->now();
    Sleep::usleep(1_000);
    $time2 = $frozen->now();

    expect($time1)->toBe($time2);
});
