<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use Carbon\CarbonImmutable;
use Cline\Clock\Clocks\FrozenClock;
use Illuminate\Support\Sleep;

test('it returns frozen time', function (): void {
    $frozenTime = CarbonImmutable::parse('2025-01-15 12:00:00');
    $clock = new FrozenClock($frozenTime);

    expect($clock->now())->toBe($frozenTime);
});

test('it returns same time on successive calls', function (): void {
    $frozenTime = CarbonImmutable::parse('2025-01-15 12:00:00');
    $clock = new FrozenClock($frozenTime);

    $time1 = $clock->now();
    Sleep::usleep(1_000);
    $time2 = $clock->now();

    expect($time1)->toBe($time2);
});

test('it creates from string', function (): void {
    $clock = FrozenClock::fromString('2025-01-15 12:00:00');
    $now = $clock->now();

    expect($now->format('Y-m-d H:i:s'))->toBe('2025-01-15 12:00:00');
});

test('it maintains immutability', function (): void {
    $frozenTime = CarbonImmutable::parse('2025-01-15 12:00:00');
    $clock = new FrozenClock($frozenTime);

    $time = $clock->now();
    $modifiedTime = $time->modify('+1 hour');

    expect($clock->now())->toBe($frozenTime)
        ->and($modifiedTime)->not->toBe($time);
});
