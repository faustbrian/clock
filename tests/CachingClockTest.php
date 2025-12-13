<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use Carbon\CarbonImmutable;
use Cline\Clock\Clocks\TickClock;
use Cline\Clock\Decorators\CachingClock;
use Illuminate\Support\Sleep;

test('it caches clock result', function (): void {
    $baseClock = new TickClock(CarbonImmutable::parse('2025-01-15 12:00:00'));
    $clock = new CachingClock($baseClock, 2);

    $time1 = $clock->now();
    $baseClock->tick('+1 hour');
    $time2 = $clock->now();

    expect($time1->format('Y-m-d H:i:s'))->toBe($time2->format('Y-m-d H:i:s'))
        ->and($time1->format('H:i:s'))->toBe('12:00:00');
});

test('it expires cache after TTL', function (): void {
    $baseClock = new TickClock(CarbonImmutable::parse('2025-01-15 12:00:00'));
    $clock = new CachingClock($baseClock, 1);

    $time1 = $clock->now();
    $baseClock->tick('+1 hour');
    Sleep::sleep(2);
    $time2 = $clock->now();

    expect($time1)->not->toBe($time2)
        ->and($time1->format('H:i:s'))->toBe('12:00:00')
        ->and($time2->format('H:i:s'))->toBe('13:00:00');
});

test('it can clear cache manually', function (): void {
    $baseClock = new TickClock(CarbonImmutable::parse('2025-01-15 12:00:00'));
    $clock = new CachingClock($baseClock, 60);

    $time1 = $clock->now();
    $baseClock->tick('+1 hour');
    $clock->clear();
    $time2 = $clock->now();

    expect($time1)->not->toBe($time2);
});

test('it caches for default 1 second', function (): void {
    $baseClock = new TickClock(CarbonImmutable::parse('2025-01-15 12:00:00'));
    $clock = new CachingClock($baseClock);

    $time1 = $clock->now();
    $baseClock->tick('+1 hour');
    $time2 = $clock->now();

    expect($time1->format('Y-m-d H:i:s'))->toBe($time2->format('Y-m-d H:i:s'))
        ->and($time1->format('H:i:s'))->toBe('12:00:00');
});

test('it handles cache expiration when cachedAt is null but cached is set', function (): void {
    $baseClock = new TickClock(CarbonImmutable::parse('2025-01-15 12:00:00'));
    $clock = new CachingClock($baseClock, 60);

    // Prime the cache
    $time1 = $clock->now();

    // Use reflection to simulate edge case: cached is set but cachedAt is null
    $reflection = new ReflectionClass($clock);
    $cachedAtProperty = $reflection->getProperty('cachedAt');
    $cachedAtProperty->setValue($clock, null);

    // This should detect expired cache and fetch new time
    $time2 = $clock->now();

    expect($time2)->toBeInstanceOf(DateTimeImmutable::class);
});
