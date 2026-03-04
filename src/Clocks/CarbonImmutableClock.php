<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cline\Clock\Clocks;

use Carbon\CarbonImmutable;
use Cline\Clock\Contracts\ClockInterface;
use Cline\Clock\Contracts\FreezableInterface;
use DateTimeImmutable;
use DateTimeZone;

/**
 * Clock implementation using Carbon's immutable datetime instances.
 *
 * Provides current time using CarbonImmutable directly, ensuring all datetime
 * operations are immutable. Preferred over CarbonClock when immutability is
 * required throughout the application lifecycle.
 *
 * @author Brian Faust <brian@cline.sh>
 *
 * @psalm-immutable
 */
final readonly class CarbonImmutableClock implements ClockInterface, FreezableInterface
{
    /**
     * Create a new CarbonImmutable-based clock instance.
     *
     * @param null|DateTimeZone $timezone Optional timezone for the clock. When null, uses
     *                                    the system's default timezone. When provided, all
     *                                    datetime instances will be created in the specified
     *                                    timezone for consistent time handling across zones.
     */
    public function __construct(
        private ?DateTimeZone $timezone = null,
    ) {}

    /**
     * Returns the current datetime using CarbonImmutable.
     *
     * @return DateTimeImmutable current time as an immutable datetime instance, respecting
     *                           the configured timezone if provided during construction
     */
    public function now(): DateTimeImmutable
    {
        if ($this->timezone instanceof DateTimeZone) {
            return CarbonImmutable::now($this->timezone)->toDateTimeImmutable();
        }

        return CarbonImmutable::now()->toDateTimeImmutable();
    }

    /**
     * Creates a frozen clock that always returns the current time.
     *
     * Captures the current moment and returns a FrozenClock that will always
     * return this exact datetime, useful for testing time-dependent code.
     *
     * @return FrozenClock frozen clock instance with the current time locked
     */
    public function freeze(): FrozenClock
    {
        return new FrozenClock($this->now());
    }
}
