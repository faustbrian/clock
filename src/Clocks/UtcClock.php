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

/**
 * Clock that always returns time in UTC timezone.
 *
 * Provides current time explicitly in UTC, ensuring timezone consistency regardless
 * of system or application timezone configuration. Useful for applications requiring
 * timezone-agnostic time handling or when storing datetime values in a database where
 * UTC is the standard representation.
 *
 * @author Brian Faust <brian@cline.sh>
 *
 * @psalm-immutable
 */
final readonly class UtcClock implements ClockInterface, FreezableInterface
{
    /**
     * The UTC timezone used for all datetime operations.
     */
    private DateTimeZone $timezone;

    /**
     * Create a new UTC clock instance.
     *
     * Initializes the clock with UTC timezone, ensuring all datetime values
     * returned will be in Coordinated Universal Time.
     */
    public function __construct()
    {
        $this->timezone = new DateTimeZone('UTC');
    }

    /**
     * Returns the current datetime in UTC timezone.
     *
     * @return DateTimeImmutable current time as an immutable datetime instance,
     *                           always in UTC timezone regardless of system settings
     */
    public function now(): DateTimeImmutable
    {
        return new DateTimeImmutable('now', $this->timezone);
    }

    /**
     * Creates a frozen clock at the current UTC time.
     *
     * Captures the current UTC moment and returns a FrozenClock that will always
     * return this exact datetime, useful for testing time-dependent code.
     *
     * @return FrozenClock frozen clock instance with the current UTC time locked
     */
    public function freeze(): FrozenClock
    {
        return new FrozenClock($this->now());
    }
}
