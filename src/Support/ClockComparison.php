<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cline\Clock\Support;

use DateTimeImmutable;
use DateTimeInterface;

use function abs;

/**
 * Provides time comparison and difference calculation methods for clock implementations.
 *
 * This trait adds convenient comparison methods to any clock implementation,
 * allowing easy temporal comparisons and time difference calculations. All
 * comparisons are performed using the clock's now() method, ensuring consistency
 * with the clock's current time source.
 *
 * Trait is used in test fixtures (tests/Fixtures/ComparisonClock.php).
 *
 * @author Brian Faust <brian@cline.sh>
 *
 * @phpstan-ignore trait.unused
 */
trait ClockComparison
{
    /**
     * Determines if the clock's current time is after the given time.
     *
     * @param  DateTimeInterface $other The time to compare against
     * @return bool              True if clock's current time is after the given time
     */
    public function isAfter(DateTimeInterface $other): bool
    {
        return $this->now() > $other;
    }

    /**
     * Determines if the clock's current time is before the given time.
     *
     * @param  DateTimeInterface $other The time to compare against
     * @return bool              True if clock's current time is before the given time
     */
    public function isBefore(DateTimeInterface $other): bool
    {
        return $this->now() < $other;
    }

    /**
     * Determines if the clock's current time falls within the given range.
     *
     * The comparison is inclusive, meaning the clock time is considered between
     * if it equals either boundary or falls within them.
     *
     * @param  DateTimeInterface $start The start of the time range (inclusive)
     * @param  DateTimeInterface $end   The end of the time range (inclusive)
     * @return bool              True if clock's current time is between start and end
     */
    public function isBetween(DateTimeInterface $start, DateTimeInterface $end): bool
    {
        $now = $this->now();

        return $now >= $start && $now <= $end;
    }

    /**
     * Determines if the clock's current time equals the given time.
     *
     * Comparison is done using Unix timestamps, so times are considered equal
     * if they represent the same second, regardless of timezone or microseconds.
     *
     * @param  DateTimeInterface $other The time to compare against
     * @return bool              True if both times represent the same Unix timestamp
     */
    public function isSameAs(DateTimeInterface $other): bool
    {
        return $this->now()->getTimestamp() === $other->getTimestamp();
    }

    /**
     * Calculates the absolute difference in seconds between clock time and given time.
     *
     * @param  DateTimeInterface $other The time to compare against
     * @return int               The absolute difference in seconds (always positive)
     */
    public function diffInSeconds(DateTimeInterface $other): int
    {
        return abs($this->now()->getTimestamp() - $other->getTimestamp());
    }

    /**
     * Calculates the absolute difference in minutes between clock time and given time.
     *
     * The result is truncated to an integer, discarding any fractional minutes.
     *
     * @param  DateTimeInterface $other The time to compare against
     * @return int               The absolute difference in whole minutes (always positive)
     */
    public function diffInMinutes(DateTimeInterface $other): int
    {
        return (int) ($this->diffInSeconds($other) / 60);
    }

    /**
     * Calculates the absolute difference in hours between clock time and given time.
     *
     * The result is truncated to an integer, discarding any fractional hours.
     *
     * @param  DateTimeInterface $other The time to compare against
     * @return int               The absolute difference in whole hours (always positive)
     */
    public function diffInHours(DateTimeInterface $other): int
    {
        return (int) ($this->diffInSeconds($other) / 3_600);
    }

    /**
     * Calculates the absolute difference in days between clock time and given time.
     *
     * The result is truncated to an integer, discarding any fractional days.
     * Uses 86,400 seconds per day (24 hours) for calculation.
     *
     * @param  DateTimeInterface $other The time to compare against
     * @return int               The absolute difference in whole days (always positive)
     */
    public function diffInDays(DateTimeInterface $other): int
    {
        return (int) ($this->diffInSeconds($other) / 86_400);
    }

    /**
     * Returns the current time as an immutable DateTime object.
     *
     * This method must be implemented by classes using this trait,
     * typically by implementing ClockInterface.
     *
     * @return DateTimeImmutable The current point in time
     */
    abstract public function now(): DateTimeImmutable;
}
