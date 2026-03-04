<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cline\Clock\Clocks;

use Cline\Clock\Contracts\ClockInterface;
use DateInterval;
use DateTimeImmutable;

/**
 * Mutable clock that can be manually advanced through time.
 *
 * Provides fine-grained control over time progression by allowing explicit advancement
 * through tick() method calls. Useful for testing scenarios where time should only advance
 * when explicitly triggered, such as simulating scheduled tasks or step-by-step time-based
 * workflows without automatic time progression.
 *
 * @author Brian Faust <brian@cline.sh>
 */
final class TickClock implements ClockInterface
{
    /**
     * Create a new tick clock instance.
     *
     * @param DateTimeImmutable $currentTime Initial time for the clock. This datetime becomes
     *                                       the starting point and will remain frozen until
     *                                       explicitly advanced via tick(), setTo(), or reset().
     */
    public function __construct(
        private DateTimeImmutable $currentTime,
    ) {}

    /**
     * Returns the current time of the clock.
     *
     * @return DateTimeImmutable current frozen time that only changes when explicitly
     *                           modified through tick(), setTo(), or reset() methods
     */
    public function now(): DateTimeImmutable
    {
        return $this->currentTime;
    }

    /**
     * Advances the clock forward by the specified interval.
     *
     * Moves the internal time forward by adding the interval to the current time.
     * This is the primary method for progressing time in a controlled manner.
     *
     * @param DateInterval|string $interval Time interval to advance. Accepts either a
     *                                      DateInterval instance or a string modifier
     *                                      recognized by DateTimeImmutable::modify()
     *                                      (e.g., '+1 day', '+3 hours', '+30 minutes').
     */
    public function tick(DateInterval|string $interval): void
    {
        if ($interval instanceof DateInterval) {
            $this->currentTime = $this->currentTime->add($interval);

            return;
        }

        $this->currentTime = $this->currentTime->modify($interval);
    }

    /**
     * Sets the clock to a specific point in time.
     *
     * Directly replaces the current time with the provided datetime, allowing
     * jumps to arbitrary points in time without relative progression.
     *
     * @param DateTimeImmutable $time The datetime to set the clock to. The clock will
     *                                return this time on subsequent now() calls until
     *                                changed again.
     */
    public function setTo(DateTimeImmutable $time): void
    {
        $this->currentTime = $time;
    }

    /**
     * Resets the clock to a new starting time.
     *
     * Functionally identical to setTo(), provided for semantic clarity when
     * reinitializing the clock state during test scenarios.
     *
     * @param DateTimeImmutable $time The datetime to reset the clock to. Subsequent calls
     *                                to now() will return this time until modified.
     */
    public function reset(DateTimeImmutable $time): void
    {
        $this->currentTime = $time;
    }
}
