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
use DateTime;
use DateTimeImmutable;
use DateTimeZone;
use Illuminate\Support\Facades\Date;

/**
 * Clock implementation using PHP's native mutable DateTime class.
 *
 * Creates mutable DateTime instances and converts them to DateTimeImmutable
 * for the clock interface. Falls back to Laravel's Date facade when no timezone
 * is specified, otherwise uses native PHP DateTime with the provided timezone.
 *
 * @author Brian Faust <brian@cline.sh>
 *
 * @psalm-immutable
 */
final readonly class DateTimeClock implements ClockInterface, FreezableInterface
{
    /**
     * Create a new DateTime-based clock instance.
     *
     * @param null|DateTimeZone $timezone Optional timezone for the clock. When null, falls back
     *                                    to Laravel's Date facade for timezone configuration.
     *                                    When provided, uses native PHP DateTime with the
     *                                    specified timezone for all datetime operations.
     */
    public function __construct(
        private ?DateTimeZone $timezone = null,
    ) {}

    /**
     * Returns the current datetime using PHP's native DateTime.
     *
     * Creates a mutable DateTime instance and converts it to DateTimeImmutable
     * to maintain immutability guarantees of the clock interface.
     *
     * @return DateTimeImmutable current time as an immutable datetime instance, respecting
     *                           the configured timezone or falling back to Laravel's Date
     */
    public function now(): DateTimeImmutable
    {
        if ($this->timezone instanceof DateTimeZone) {
            return DateTimeImmutable::createFromMutable(
                new DateTime('now', $this->timezone),
            );
        }

        return DateTimeImmutable::createFromMutable(Date::now());
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
