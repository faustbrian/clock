<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use Carbon\CarbonImmutable;
use Cline\Clock\Clocks\FrozenClock;
use Cline\Clock\Decorators\LoggingClock;
use Psr\Log\LoggerInterface;

test('it logs clock calls', function (): void {
    $logger = mock(LoggerInterface::class);
    $frozenTime = CarbonImmutable::parse('2025-01-15 12:00:00');
    $baseClock = new FrozenClock($frozenTime);
    $clock = new LoggingClock($baseClock, $logger);

    $logger->expects('log')
        ->once()
        ->withArgs(
            fn (string $level, string $message, array $context): bool => $level === 'debug'
            && $message === 'Clock returned time'
            && isset($context['timestamp'], $context['timezone'], $context['clock_class']),
        );

    $clock->now();
});

test('it returns same time as decorated clock', function (): void {
    $logger = mock(LoggerInterface::class);
    $frozenTime = CarbonImmutable::parse('2025-01-15 12:00:00');
    $baseClock = new FrozenClock($frozenTime);
    $clock = new LoggingClock($baseClock, $logger);

    $logger->allows('log');

    expect($clock->now())->toBe($frozenTime);
});

test('it logs with custom level', function (): void {
    $logger = mock(LoggerInterface::class);
    $frozenTime = CarbonImmutable::parse('2025-01-15 12:00:00');
    $baseClock = new FrozenClock($frozenTime);
    $clock = new LoggingClock($baseClock, $logger, 'info');

    $logger->expects('log')
        ->once()
        ->with('info', 'Clock returned time', Mockery::type('array'));

    $clock->now();
});

test('it includes clock class in context', function (): void {
    $logger = mock(LoggerInterface::class);
    $frozenTime = CarbonImmutable::parse('2025-01-15 12:00:00');
    $baseClock = new FrozenClock($frozenTime);
    $clock = new LoggingClock($baseClock, $logger);

    $logger->expects('log')
        ->once()
        ->withArgs(fn (string $level, string $message, array $context): bool => $context['clock_class'] === FrozenClock::class);

    $clock->now();
});
