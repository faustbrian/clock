<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use Carbon\CarbonImmutable;
use Cline\Clock\Clocks\FrozenClock;
use Cline\Clock\Clocks\OffsetClock;

test('it offsets time with string modifier', function (): void {
    $baseTime = CarbonImmutable::parse('2025-01-15 12:00:00');
    $baseClock = new FrozenClock($baseTime);
    $offsetClock = new OffsetClock($baseClock, '+1 hour');

    expect($offsetClock->now()->format('Y-m-d H:i:s'))->toBe('2025-01-15 13:00:00');
});

test('it offsets time backward', function (): void {
    $baseTime = CarbonImmutable::parse('2025-01-15 12:00:00');
    $baseClock = new FrozenClock($baseTime);
    $offsetClock = new OffsetClock($baseClock, '-2 hours');

    expect($offsetClock->now()->format('Y-m-d H:i:s'))->toBe('2025-01-15 10:00:00');
});

test('it offsets time with DateInterval', function (): void {
    $baseTime = CarbonImmutable::parse('2025-01-15 12:00:00');
    $baseClock = new FrozenClock($baseTime);
    $interval = new DateInterval('P1D');
    $offsetClock = new OffsetClock($baseClock, $interval);

    expect($offsetClock->now()->format('Y-m-d H:i:s'))->toBe('2025-01-16 12:00:00');
});

test('it offsets time with complex modifier', function (): void {
    $baseTime = CarbonImmutable::parse('2025-01-15 12:00:00');
    $baseClock = new FrozenClock($baseTime);
    $offsetClock = new OffsetClock($baseClock, '+1 day +3 hours -30 minutes');

    expect($offsetClock->now()->format('Y-m-d H:i:s'))->toBe('2025-01-16 14:30:00');
});

test('it can be frozen', function (): void {
    $baseTime = CarbonImmutable::parse('2025-01-15 12:00:00');
    $baseClock = new FrozenClock($baseTime);
    $offsetClock = new OffsetClock($baseClock, '+1 hour');
    $frozen = $offsetClock->freeze();

    expect($frozen)->toBeInstanceOf(FrozenClock::class)
        ->and($frozen->now()->format('Y-m-d H:i:s'))->toBe('2025-01-15 13:00:00');
});
