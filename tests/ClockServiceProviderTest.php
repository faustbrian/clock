<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use Cline\Clock\Clocks\CarbonImmutableClock;
use Cline\Clock\Contracts\ClockInterface;
use Cline\Clock\Facades\Clock;
use Cline\Clock\Providers\ClockServiceProvider;
use Illuminate\Foundation\Application;

test('it binds ClockInterface to container', function (): void {
    $app = new Application();
    $provider = new ClockServiceProvider($app);
    $provider->register();

    expect($app->make(ClockInterface::class))->toBeInstanceOf(CarbonImmutableClock::class);
});

test('it provides clock alias', function (): void {
    $app = new Application();
    $provider = new ClockServiceProvider($app);
    $provider->register();

    expect($app->make('clock'))->toBeInstanceOf(ClockInterface::class);
});

test('facade resolves clock instance', function (): void {
    $app = new Application();
    $provider = new ClockServiceProvider($app);
    $provider->register();

    Clock::setFacadeApplication($app);

    $now = Clock::now();

    expect($now)->toBeInstanceOf(DateTimeImmutable::class);
});

test('it provides ClockInterface and clock alias', function (): void {
    $app = new Application();
    $provider = new ClockServiceProvider($app);

    $provides = $provider->provides();

    expect($provides)->toBe([ClockInterface::class, 'clock']);
});
