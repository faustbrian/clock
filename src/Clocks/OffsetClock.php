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
use DateInterval;
use DateTimeImmutable;

/**
 * Clock decorator that applies a time offset to another clock.
 *
 * Wraps any clock implementation and shifts all datetime values forward or backward
 * by a fixed interval. Useful for testing timezone scenarios, simulating future or
 * past dates, or adjusting times relative to a base clock without modifying the
 * underlying clock implementation.
 *
 * @author Brian Faust <brian@cline.sh>
 *
 * @psalm-immutable
 */
final readonly class OffsetClock implements ClockInterface, FreezableInterface
{
    /**
     * Create a new offset clock instance.
     *
     * @param ClockInterface      $clock  The underlying clock to offset. Can be any clock
     *                                    implementation, allowing offset stacking for complex
     *                                    time manipulation scenarios.
     * @param DateInterval|string $offset Time offset to apply to the base clock. When a
     *                                    DateInterval is provided, adds the interval to the
     *                                    base time. When a string is provided, uses datetime
     *                                    modification syntax (e.g., '+1 day', '-3 hours').
     */
    public function __construct(
        private ClockInterface $clock,
        private DateInterval|string $offset,
    ) {}

    /**
     * Returns the current time with the configured offset applied.
     *
     * Retrieves the current time from the wrapped clock and applies the offset,
     * effectively shifting the datetime forward or backward in time.
     *
     * @return DateTimeImmutable current time from the underlying clock plus the offset,
     *                           maintaining immutability throughout the operation
     */
    public function now(): DateTimeImmutable
    {
        $now = $this->clock->now();

        if ($this->offset instanceof DateInterval) {
            return $now->add($this->offset);
        }

        return $now->modify($this->offset);
    }

    /**
     * Creates a frozen clock at the current offset time.
     *
     * Captures the current moment with the offset applied and returns a FrozenClock
     * that will always return this exact datetime, useful for testing.
     *
     * @return FrozenClock frozen clock instance with the offset time locked
     */
    public function freeze(): FrozenClock
    {
        return new FrozenClock($this->now());
    }
}
