<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cline\Clock;

use Cline\Clock\Clocks\CarbonImmutableClock;
use Cline\Clock\Clocks\FrozenClock;
use Cline\Clock\Contracts\ClockInterface;
use DateTimeImmutable;
use DateTimeZone;

use function is_string;

/**
 * Creates or returns a clock instance with flexible configuration options.
 *
 * This helper function provides a convenient way to obtain clock instances
 * with various configurations. It supports returning existing instances,
 * creating new instances from class names, or defaulting to CarbonImmutableClock.
 *
 * ```php
 * // Get default CarbonImmutableClock
 * $clock = clock();
 *
 * // Get clock with specific timezone
 * $clock = clock(timezone: new DateTimeZone('America/New_York'));
 *
 * // Create FrozenClock at specific time
 * $clock = clock(FrozenClock::class, frozenTime: new DateTimeImmutable('2024-01-01'));
 *
 * // Return existing clock instance
 * $clock = clock($existingClock);
 * ```
 *
 * @template T of ClockInterface
 *
 * @param  null|class-string<T>|ClockInterface           $clock      The clock to use. Can be a ClockInterface instance
 *                                                                   (returned as-is), a class name string (instantiated),
 *                                                                   or null (defaults to CarbonImmutableClock).
 * @param  null|DateTimeZone                             $timezone   Optional timezone for the clock. Only used when creating
 *                                                                   new clock instances from class names or null.
 * @param  null|DateTimeImmutable                        $frozenTime Optional frozen time for FrozenClock instances. Only used
 *                                                                   when creating a FrozenClock from class name.
 * @return ($clock is class-string ? T : ClockInterface) Returns the provided instance, a new instance of the
 *                                                       specified class, or CarbonImmutableClock by default
 */
function clock(string|ClockInterface|null $clock = null, ?DateTimeZone $timezone = null, ?DateTimeImmutable $frozenTime = null): ClockInterface
{
    if ($clock instanceof ClockInterface) {
        return $clock;
    }

    if (is_string($clock)) {
        if ($clock === FrozenClock::class && $frozenTime instanceof DateTimeImmutable) {
            return new FrozenClock($frozenTime);
        }

        return new $clock($timezone);
    }

    return new CarbonImmutableClock($timezone);
}
