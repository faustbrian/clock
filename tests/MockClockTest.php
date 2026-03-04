<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use Carbon\CarbonImmutable;
use Cline\Clock\Clocks\MockClock;

test('it returns current time by default', function (): void {
    $clock = new MockClock();
    $now = $clock->now();

    expect($now)->toBeInstanceOf(DateTimeImmutable::class);
});

test('it freezes at specific time with DateTimeImmutable', function (): void {
    $clock = new MockClock();
    $freezeTime = CarbonImmutable::parse('2025-01-15 12:00:00');

    $clock->freezeAt($freezeTime);

    expect($clock->now()->format('Y-m-d H:i:s'))->toBe('2025-01-15 12:00:00');
});

test('it freezes at specific time with string', function (): void {
    $clock = new MockClock();

    $clock->freezeAt('2025-06-01 14:30:00');

    expect($clock->now()->format('Y-m-d H:i:s'))->toBe('2025-06-01 14:30:00');
});

test('it advances time with string modifier', function (): void {
    $startTime = CarbonImmutable::parse('2025-01-15 12:00:00');
    $clock = new MockClock($startTime);

    $clock->advance('+2 hours');

    expect($clock->now()->format('Y-m-d H:i:s'))->toBe('2025-01-15 14:00:00');
});

test('it advances time with DateInterval', function (): void {
    $startTime = CarbonImmutable::parse('2025-01-15 12:00:00');
    $clock = new MockClock($startTime);
    $interval = new DateInterval('P3D');

    $clock->advance($interval);

    expect($clock->now()->format('Y-m-d H:i:s'))->toBe('2025-01-18 12:00:00');
});

test('it uses sequence of times', function (): void {
    $clock = new MockClock();
    $sequence = [
        CarbonImmutable::parse('2025-01-01 12:00:00'),
        CarbonImmutable::parse('2025-01-02 12:00:00'),
        CarbonImmutable::parse('2025-01-03 12:00:00'),
    ];

    $clock->useSequence($sequence);

    expect($clock->now()->format('Y-m-d'))->toBe('2025-01-01')
        ->and($clock->now()->format('Y-m-d'))->toBe('2025-01-02')
        ->and($clock->now()->format('Y-m-d'))->toBe('2025-01-03');
});

test('it returns to frozen time after sequence exhausted', function (): void {
    $startTime = CarbonImmutable::parse('2025-01-15 12:00:00');
    $clock = new MockClock($startTime);
    $sequence = [CarbonImmutable::parse('2025-01-01 12:00:00')];

    $clock->useSequence($sequence);
    $clock->now();

    expect($clock->now()->format('Y-m-d H:i:s'))->toBe('2025-01-15 12:00:00');
});

test('it switches from sequence to frozen', function (): void {
    $clock = new MockClock();
    $sequence = [CarbonImmutable::parse('2025-01-01 12:00:00')];

    $clock->useSequence($sequence);
    expect($clock->now()->format('Y-m-d'))->toBe('2025-01-01');

    $clock->freezeAt('2025-06-01 12:00:00');
    expect($clock->now()->format('Y-m-d'))->toBe('2025-06-01');
});

test('it can be reset', function (): void {
    $startTime = CarbonImmutable::parse('2025-01-15 12:00:00');
    $clock = new MockClock($startTime);

    $clock->advance('+5 hours');
    $clock->freezeAt('2025-06-01 12:00:00');

    $resetTime = CarbonImmutable::parse('2025-01-01 00:00:00');
    $clock->reset($resetTime);

    expect($clock->now()->format('Y-m-d H:i:s'))->toBe('2025-01-01 00:00:00');
});

test('it combines freeze and advance', function (): void {
    $clock = new MockClock();

    $clock->freezeAt('2025-01-15 12:00:00');

    expect($clock->now()->format('H:i:s'))->toBe('12:00:00');

    $clock->advance('+3 hours');
    expect($clock->now()->format('H:i:s'))->toBe('15:00:00');

    $clock->advance('-1 hour');
    expect($clock->now()->format('H:i:s'))->toBe('14:00:00');
});
