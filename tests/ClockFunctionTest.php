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

use function Cline\Clock\clock;

test('it returns CarbonImmutableClock by default', function (): void {
    $clock = clock();

    expect($clock)->toBeInstanceOf(CarbonImmutableClock::class);
});

test('it returns custom clock instance when provided', function (): void {
    $frozenClock = new FrozenClock(
        new DateTimeImmutable('2025-01-15 12:00:00'),
    );
    $clock = clock($frozenClock);

    expect($clock)->toBe($frozenClock);
});

test('it passes timezone to default clock', function (): void {
    $timezone = new DateTimeZone('America/New_York');
    $clock = clock(timezone: $timezone);
    $now = $clock->now();

    expect($now->getTimezone()->getName())->toBe('America/New_York');
});

test('it instantiates CarbonClock from class string', function (): void {
    $clock = clock(CarbonClock::class);

    expect($clock)->toBeInstanceOf(CarbonClock::class);
});

test('it instantiates CarbonImmutableClock from class string', function (): void {
    $clock = clock(CarbonImmutableClock::class);

    expect($clock)->toBeInstanceOf(CarbonImmutableClock::class);
});

test('it instantiates DateTimeClock from class string', function (): void {
    $clock = clock(DateTimeClock::class);

    expect($clock)->toBeInstanceOf(DateTimeClock::class);
});

test('it instantiates DateTimeImmutableClock from class string', function (): void {
    $clock = clock(DateTimeImmutableClock::class);

    expect($clock)->toBeInstanceOf(DateTimeImmutableClock::class);
});

test('it instantiates FrozenClock from class string with frozen time', function (): void {
    $frozenTime = new DateTimeImmutable('2025-01-15 12:00:00');
    $clock = clock(FrozenClock::class, frozenTime: $frozenTime);

    expect($clock)->toBeInstanceOf(FrozenClock::class)
        ->and($clock->now())->toBe($frozenTime);
});

test('it passes timezone to clock from class string', function (): void {
    $timezone = new DateTimeZone('Europe/London');
    $clock = clock(DateTimeImmutableClock::class, $timezone);

    expect($clock->now()->getTimezone()->getName())->toBe('Europe/London');
});
