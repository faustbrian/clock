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
use DateInterval;
use DateTimeImmutable;

use function count;
use function is_string;

/**
 * Mutable clock for testing with time manipulation capabilities.
 *
 * Provides comprehensive testing utilities including freezing time at specific moments,
 * advancing time by intervals, and returning a predetermined sequence of datetime values.
 * Essential for testing time-dependent business logic with full control over temporal flow.
 *
 * @author Brian Faust <brian@cline.sh>
 */
final class MockClock implements ClockInterface
{
    /**
     * The current mocked time returned by the clock.
     */
    private DateTimeImmutable $currentTime;

    /**
     * Predefined sequence of datetime values to return sequentially.
     *
     * @var array<DateTimeImmutable>
     */
    private array $sequence = [];

    /**
     * Current position in the sequence array.
     */
    private int $sequenceIndex = 0;

    /**
     * Flag indicating whether the clock should use the sequence mode.
     */
    private bool $useSequence = false;

    /**
     * Create a new mock clock instance.
     *
     * @param null|DateTimeImmutable $startTime Initial time for the mock clock. When null,
     *                                          uses the current system time via CarbonImmutable.
     *                                          This becomes the base time for all subsequent
     *                                          time manipulations until explicitly changed.
     */
    public function __construct(?DateTimeImmutable $startTime = null)
    {
        $this->currentTime = $startTime ?? CarbonImmutable::now();
    }

    /**
     * Returns the current mocked time or next time in sequence.
     *
     * When sequence mode is active and times remain, returns the next datetime
     * from the sequence. Otherwise, returns the current frozen time.
     *
     * @return DateTimeImmutable current mocked time or next sequential datetime value
     */
    public function now(): DateTimeImmutable
    {
        if ($this->useSequence && $this->sequenceIndex < count($this->sequence)) {
            $time = $this->sequence[$this->sequenceIndex];
            ++$this->sequenceIndex;

            return $time;
        }

        return $this->currentTime;
    }

    /**
     * Freezes the clock at a specific point in time.
     *
     * Disables sequence mode and locks the clock to the specified datetime.
     * Subsequent calls to now() will return this fixed time until changed.
     *
     * @param DateTimeImmutable|string $time The datetime to freeze at. Accepts either
     *                                       a DateTimeImmutable instance or a string that
     *                                       can be parsed by DateTimeImmutable constructor.
     */
    public function freezeAt(DateTimeImmutable|string $time): void
    {
        $this->useSequence = false;

        if (is_string($time)) {
            $this->currentTime = new DateTimeImmutable($time);

            return;
        }

        $this->currentTime = $time;
    }

    /**
     * Advances the current time forward by the specified interval.
     *
     * Disables sequence mode and moves the frozen time forward. Useful for
     * testing scenarios that require progressing through time step by step.
     *
     * @param DateInterval|string $interval Time interval to advance. Accepts either a
     *                                      DateInterval instance or a string modifier
     *                                      recognized by DateTimeImmutable::modify()
     *                                      (e.g., '+1 day', '+3 hours', '+30 minutes').
     */
    public function advance(DateInterval|string $interval): void
    {
        $this->useSequence = false;

        if ($interval instanceof DateInterval) {
            $this->currentTime = $this->currentTime->add($interval);

            return;
        }

        $this->currentTime = $this->currentTime->modify($interval);
    }

    /**
     * Activates sequence mode with predefined datetime values.
     *
     * Configures the clock to return a predetermined sequence of datetime values
     * on successive calls to now(). Useful for testing scenarios that require
     * specific datetime progressions without manual time manipulation.
     *
     * @param array<DateTimeImmutable> $times Array of datetime instances to return sequentially.
     *                                        Each call to now() will return the next datetime
     *                                        in this array until exhausted, then falls back to
     *                                        the current frozen time.
     */
    public function useSequence(array $times): void
    {
        $this->sequence = $times;
        $this->sequenceIndex = 0;
        $this->useSequence = true;
    }

    /**
     * Resets the mock clock to initial state or a new starting time.
     *
     * Clears the sequence, disables sequence mode, and sets the current time
     * to either the provided datetime or the current system time.
     *
     * @param null|DateTimeImmutable $time Optional new starting time. When null, resets
     *                                     to the current system time via CarbonImmutable.
     *                                     When provided, becomes the new frozen time.
     */
    public function reset(?DateTimeImmutable $time = null): void
    {
        $this->currentTime = $time ?? CarbonImmutable::now();
        $this->sequence = [];
        $this->sequenceIndex = 0;
        $this->useSequence = false;
    }
}
