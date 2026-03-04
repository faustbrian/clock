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
 * Clock implementation using PHP's native immutable datetime class.
 *
 * Provides current time using native PHP DateTimeImmutable when a timezone is
 * specified, otherwise falls back to CarbonImmutable. Useful for applications
 * that prefer native PHP datetime handling over third-party libraries.
 *
 * @author Brian Faust <brian@cline.sh>
 *
 * @psalm-immutable
 */
final readonly class DateTimeImmutableClock implements ClockInterface, FreezableInterface
{
    /**
     * Create a new DateTimeImmutable-based clock instance.
     *
     * @param null|DateTimeZone $timezone Optional timezone for the clock. When provided, uses
     *                                    native PHP DateTimeImmutable with the specified timezone.
     *                                    When null, falls back to CarbonImmutable for enhanced
     *                                    datetime functionality and Laravel integration.
     */
    public function __construct(
        private ?DateTimeZone $timezone = null,
    ) {}

    /**
     * Returns the current datetime using PHP's native DateTimeImmutable.
     *
     * Uses native DateTimeImmutable when a timezone is configured, otherwise
     * falls back to CarbonImmutable for additional features and Laravel compatibility.
     *
     * @return DateTimeImmutable current time as an immutable datetime instance, using
     *                           the configured timezone or CarbonImmutable default
     */
    public function now(): DateTimeImmutable
    {
        if ($this->timezone instanceof DateTimeZone) {
            return new DateTimeImmutable('now', $this->timezone);
        }

        return CarbonImmutable::now();
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
