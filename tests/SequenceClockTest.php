<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use Carbon\CarbonImmutable;
use Cline\Clock\Clocks\SequenceClock;

test('it returns times in sequence', function (): void {
    $times = [
        CarbonImmutable::parse('2025-01-01 12:00:00'),
        CarbonImmutable::parse('2025-01-02 12:00:00'),
        CarbonImmutable::parse('2025-01-03 12:00:00'),
    ];
    $clock = new SequenceClock($times);

    expect($clock->now()->format('Y-m-d'))->toBe('2025-01-01')
        ->and($clock->now()->format('Y-m-d'))->toBe('2025-01-02')
        ->and($clock->now()->format('Y-m-d'))->toBe('2025-01-03');
});

test('it throws when exhausted', function (): void {
    $times = [CarbonImmutable::parse('2025-01-01 12:00:00')];
    $clock = new SequenceClock($times);

    $clock->now();

    expect(fn (): DateTimeImmutable => $clock->now())->toThrow(RuntimeException::class, 'SequenceClock has exhausted all times');
});

test('it can be reset', function (): void {
    $times = [
        CarbonImmutable::parse('2025-01-01 12:00:00'),
        CarbonImmutable::parse('2025-01-02 12:00:00'),
    ];
    $clock = new SequenceClock($times);

    expect($clock->now()->format('Y-m-d'))->toBe('2025-01-01')
        ->and($clock->now()->format('Y-m-d'))->toBe('2025-01-02');

    $clock->reset();

    expect($clock->now()->format('Y-m-d'))->toBe('2025-01-01');
});

test('it throws when created with empty array', function (): void {
    expect(fn (): SequenceClock => new SequenceClock([]))->toThrow(RuntimeException::class, 'SequenceClock requires at least one time');
});

test('it tracks if has next', function (): void {
    $times = [
        CarbonImmutable::parse('2025-01-01 12:00:00'),
        CarbonImmutable::parse('2025-01-02 12:00:00'),
    ];
    $clock = new SequenceClock($times);

    expect($clock->hasNext())->toBeTrue();
    $clock->now();
    expect($clock->hasNext())->toBeTrue();
    $clock->now();
    expect($clock->hasNext())->toBeFalse();
});
