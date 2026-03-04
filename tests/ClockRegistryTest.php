<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use Carbon\CarbonImmutable;
use Cline\Clock\Clocks\FrozenClock;
use Cline\Clock\Clocks\UtcClock;
use Cline\Clock\Contracts\ClockInterface;
use Cline\Clock\Support\ClockRegistry;

beforeEach(function (): void {
    ClockRegistry::clear();
});

test('it registers and retrieves clock', function (): void {
    $clock = new UtcClock();
    ClockRegistry::set('utc', $clock);

    expect(ClockRegistry::get('utc'))->toBe($clock);
});

test('it checks if clock is registered', function (): void {
    $clock = new UtcClock();
    ClockRegistry::set('utc', $clock);

    expect(ClockRegistry::has('utc'))->toBeTrue()
        ->and(ClockRegistry::has('missing'))->toBeFalse();
});

test('it throws when getting unregistered clock', function (): void {
    expect(fn (): ClockInterface => ClockRegistry::get('missing'))
        ->toThrow(RuntimeException::class, "Clock 'missing' not registered");
});

test('it removes clock', function (): void {
    $clock = new UtcClock();
    ClockRegistry::set('utc', $clock);
    ClockRegistry::remove('utc');

    expect(ClockRegistry::has('utc'))->toBeFalse();
});

test('it sets and gets default clock', function (): void {
    $clock = new UtcClock();
    ClockRegistry::set('utc', $clock);
    ClockRegistry::setDefault('utc');

    expect(ClockRegistry::getDefault())->toBe($clock);
});

test('it throws when setting unregistered default', function (): void {
    expect(fn () => ClockRegistry::setDefault('missing'))
        ->toThrow(RuntimeException::class, "Cannot set default to unregistered clock 'missing'");
});

test('it throws when getting default without one set', function (): void {
    expect(fn (): ClockInterface => ClockRegistry::getDefault())
        ->toThrow(RuntimeException::class, 'No default clock set');
});

test('it checks if has default', function (): void {
    expect(ClockRegistry::hasDefault())->toBeFalse();

    $clock = new UtcClock();
    ClockRegistry::set('utc', $clock);
    ClockRegistry::setDefault('utc');

    expect(ClockRegistry::hasDefault())->toBeTrue();
});

test('it lists registered clocks', function (): void {
    ClockRegistry::set('utc', new UtcClock());
    ClockRegistry::set('frozen', new FrozenClock(
        CarbonImmutable::now(),
    ));

    expect(ClockRegistry::registered())->toBe(['utc', 'frozen']);
});

test('it clears all clocks', function (): void {
    ClockRegistry::set('utc', new UtcClock());
    ClockRegistry::setDefault('utc');

    ClockRegistry::clear();

    expect(ClockRegistry::registered())->toBe([])
        ->and(ClockRegistry::hasDefault())->toBeFalse();
});

test('removing default clock clears default', function (): void {
    $clock = new UtcClock();
    ClockRegistry::set('utc', $clock);
    ClockRegistry::setDefault('utc');
    ClockRegistry::remove('utc');

    expect(ClockRegistry::hasDefault())->toBeFalse();
});
