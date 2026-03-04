<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use Carbon\CarbonImmutable;
use Cline\Clock\Clocks\FrozenClock;
use Tests\Fixtures\ComparisonClock;

test('it checks if after', function (): void {
    $clock = new ComparisonClock(
        new FrozenClock(CarbonImmutable::parse('2025-01-15 12:00:00')),
    );
    $earlier = CarbonImmutable::parse('2025-01-15 11:00:00');
    $later = CarbonImmutable::parse('2025-01-15 13:00:00');

    expect($clock->isAfter($earlier))->toBeTrue()
        ->and($clock->isAfter($later))->toBeFalse();
});

test('it checks if before', function (): void {
    $clock = new ComparisonClock(
        new FrozenClock(CarbonImmutable::parse('2025-01-15 12:00:00')),
    );
    $earlier = CarbonImmutable::parse('2025-01-15 11:00:00');
    $later = CarbonImmutable::parse('2025-01-15 13:00:00');

    expect($clock->isBefore($later))->toBeTrue()
        ->and($clock->isBefore($earlier))->toBeFalse();
});

test('it checks if between', function (): void {
    $clock = new ComparisonClock(
        new FrozenClock(CarbonImmutable::parse('2025-01-15 12:00:00')),
    );
    $start = CarbonImmutable::parse('2025-01-15 11:00:00');
    $end = CarbonImmutable::parse('2025-01-15 13:00:00');

    expect($clock->isBetween($start, $end))->toBeTrue();
});

test('it checks if not between', function (): void {
    $clock = new ComparisonClock(
        new FrozenClock(CarbonImmutable::parse('2025-01-15 12:00:00')),
    );
    $start = CarbonImmutable::parse('2025-01-15 13:00:00');
    $end = CarbonImmutable::parse('2025-01-15 14:00:00');

    expect($clock->isBetween($start, $end))->toBeFalse();
});

test('it checks if same as', function (): void {
    $clock = new ComparisonClock(
        new FrozenClock(CarbonImmutable::parse('2025-01-15 12:00:00')),
    );
    $same = CarbonImmutable::parse('2025-01-15 12:00:00');
    $different = CarbonImmutable::parse('2025-01-15 13:00:00');

    expect($clock->isSameAs($same))->toBeTrue()
        ->and($clock->isSameAs($different))->toBeFalse();
});

test('it calculates diff in seconds', function (): void {
    $clock = new ComparisonClock(
        new FrozenClock(CarbonImmutable::parse('2025-01-15 12:00:00')),
    );
    $other = CarbonImmutable::parse('2025-01-15 12:00:30');

    expect($clock->diffInSeconds($other))->toBe(30);
});

test('it calculates diff in minutes', function (): void {
    $clock = new ComparisonClock(
        new FrozenClock(CarbonImmutable::parse('2025-01-15 12:00:00')),
    );
    $other = CarbonImmutable::parse('2025-01-15 12:05:00');

    expect($clock->diffInMinutes($other))->toBe(5);
});

test('it calculates diff in hours', function (): void {
    $clock = new ComparisonClock(
        new FrozenClock(CarbonImmutable::parse('2025-01-15 12:00:00')),
    );
    $other = CarbonImmutable::parse('2025-01-15 15:00:00');

    expect($clock->diffInHours($other))->toBe(3);
});

test('it calculates diff in days', function (): void {
    $clock = new ComparisonClock(
        new FrozenClock(CarbonImmutable::parse('2025-01-15 12:00:00')),
    );
    $other = CarbonImmutable::parse('2025-01-18 12:00:00');

    expect($clock->diffInDays($other))->toBe(3);
});

test('diff methods return absolute values', function (): void {
    $clock = new ComparisonClock(
        new FrozenClock(CarbonImmutable::parse('2025-01-15 12:00:00')),
    );
    $earlier = CarbonImmutable::parse('2025-01-15 11:00:00');

    expect($clock->diffInHours($earlier))->toBe(1);
});
