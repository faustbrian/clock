<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use Cline\Clock\Clocks\FrozenClock;
use Cline\Clock\Clocks\UtcClock;
use Illuminate\Support\Sleep;

test('it returns current time in UTC', function (): void {
    $clock = new UtcClock();
    $now = $clock->now();

    expect($now)->toBeInstanceOf(DateTimeImmutable::class)
        ->and($now->getTimezone()->getName())->toBe('UTC');
});

test('it always returns UTC regardless of system timezone', function (): void {
    $clock = new UtcClock();
    $now = $clock->now();

    expect($now->getTimezone()->getName())->toBe('UTC');
});

test('it can be frozen', function (): void {
    $clock = new UtcClock();
    $frozen = $clock->freeze();

    expect($frozen)->toBeInstanceOf(FrozenClock::class)
        ->and($frozen->now()->getTimezone()->getName())->toBe('UTC');
});

test('it returns different times on successive calls', function (): void {
    $clock = new UtcClock();
    $time1 = $clock->now();
    Sleep::usleep(1_000);
    $time2 = $clock->now();

    expect($time2)->toBeGreaterThanOrEqual($time1);
});
