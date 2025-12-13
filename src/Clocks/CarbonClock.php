<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cline\Clock\Clocks;

use Cline\Clock\Contracts\ClockInterface;
use Cline\Clock\Contracts\FreezableInterface;
use DateTimeImmutable;
use DateTimeZone;
use Illuminate\Support\Facades\Date;

/**
 * Clock implementation using Laravel's Carbon facade for mutable datetime instances.
 *
 * Provides current time using Laravel's Date facade, which returns Carbon instances
 * that are converted to DateTimeImmutable. Useful when working in Laravel applications
 * where Carbon is the primary datetime library.
 *
 * @author Brian Faust <brian@cline.sh>
 *
 * @psalm-immutable
 */
final readonly class CarbonClock implements ClockInterface, FreezableInterface
{
    /**
     * Create a new Carbon-based clock instance.
     *
     * @param null|DateTimeZone $timezone Optional timezone for the clock. When null, uses
     *                                    the application's default timezone configured in
     *                                    Laravel. When provided, all datetime instances will
     *                                    be created in the specified timezone.
     */
    public function __construct(
        private ?DateTimeZone $timezone = null,
    ) {}

    /**
     * Returns the current datetime using Laravel's Date facade.
     *
     * @return DateTimeImmutable current time as an immutable datetime instance, respecting
     *                           the configured timezone if provided during construction
     */
    public function now(): DateTimeImmutable
    {
        if ($this->timezone instanceof DateTimeZone) {
            return Date::now($this->timezone)->toDateTimeImmutable();
        }

        return Date::now()->toDateTimeImmutable();
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
