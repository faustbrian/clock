<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use Carbon\CarbonImmutable;
use Cline\Clock\Clocks\TickClock;

test('it returns current time', function (): void {
    $startTime = CarbonImmutable::parse('2025-01-15 12:00:00');
    $clock = new TickClock($startTime);

    expect($clock->now()->format('Y-m-d H:i:s'))->toBe('2025-01-15 12:00:00');
});

test('it ticks forward with string modifier', function (): void {
    $startTime = CarbonImmutable::parse('2025-01-15 12:00:00');
    $clock = new TickClock($startTime);

    $clock->tick('+1 hour');

    expect($clock->now()->format('Y-m-d H:i:s'))->toBe('2025-01-15 13:00:00');
});

test('it ticks backward with string modifier', function (): void {
    $startTime = CarbonImmutable::parse('2025-01-15 12:00:00');
    $clock = new TickClock($startTime);

    $clock->tick('-30 minutes');

    expect($clock->now()->format('Y-m-d H:i:s'))->toBe('2025-01-15 11:30:00');
});

test('it ticks with DateInterval', function (): void {
    $startTime = CarbonImmutable::parse('2025-01-15 12:00:00');
    $clock = new TickClock($startTime);
    $interval = new DateInterval('P2D');

    $clock->tick($interval);

    expect($clock->now()->format('Y-m-d H:i:s'))->toBe('2025-01-17 12:00:00');
});

test('it can be set to specific time', function (): void {
    $startTime = CarbonImmutable::parse('2025-01-15 12:00:00');
    $clock = new TickClock($startTime);
    $newTime = CarbonImmutable::parse('2025-06-01 14:30:00');

    $clock->setTo($newTime);

    expect($clock->now()->format('Y-m-d H:i:s'))->toBe('2025-06-01 14:30:00');
});

test('it can be reset', function (): void {
    $startTime = CarbonImmutable::parse('2025-01-15 12:00:00');
    $clock = new TickClock($startTime);

    $clock->tick('+5 hours');

    expect($clock->now()->format('H:i:s'))->toBe('17:00:00');

    $resetTime = CarbonImmutable::parse('2025-01-01 00:00:00');
    $clock->reset($resetTime);

    expect($clock->now()->format('Y-m-d H:i:s'))->toBe('2025-01-01 00:00:00');
});

test('it can tick multiple times', function (): void {
    $startTime = CarbonImmutable::parse('2025-01-15 12:00:00');
    $clock = new TickClock($startTime);

    $clock->tick('+1 hour');
    $clock->tick('+30 minutes');
    $clock->tick('+15 minutes');

    expect($clock->now()->format('H:i:s'))->toBe('13:45:00');
});
