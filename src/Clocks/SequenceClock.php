<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cline\Clock\Clocks;

use Cline\Clock\Contracts\ClockInterface;
use DateTimeImmutable;
use RuntimeException;

use function count;
use function throw_if;

/**
 * Clock that returns a predetermined sequence of datetime values.
 *
 * Returns a fixed sequence of datetime values in order, advancing through the sequence
 * with each call to now(). Throws an exception when the sequence is exhausted. Ideal for
 * testing scenarios requiring specific datetime progressions or when multiple different
 * time values are needed in a predictable order.
 *
 * @author Brian Faust <brian@cline.sh>
 */
final class SequenceClock implements ClockInterface
{
    /**
     * Current position in the datetime sequence.
     */
    private int $index = 0;

    /**
     * Create a new sequence clock instance.
     *
     * @param array<DateTimeImmutable> $times Array of datetime instances to return sequentially.
     *                                        Must contain at least one datetime value. Each call
     *                                        to now() will consume the next datetime in this array,
     *                                        advancing the internal index until exhausted.
     *
     * @throws RuntimeException when the times array is empty, as at least one datetime is required
     */
    public function __construct(
        private readonly array $times,
    ) {
        throw_if($times === [], RuntimeException::class, 'SequenceClock requires at least one time');
    }

    /**
     * Returns the next datetime in the sequence.
     *
     * Retrieves the current datetime from the sequence and advances the internal
     * index. Each call consumes one datetime from the sequence.
     *
     * @throws RuntimeException when all datetime values in the sequence have been exhausted
     *                          and no more values remain to return
     *
     * @return DateTimeImmutable next datetime in the predetermined sequence
     */
    public function now(): DateTimeImmutable
    {
        throw_if($this->index >= count($this->times), RuntimeException::class, 'SequenceClock has exhausted all times');

        $time = $this->times[$this->index];
        ++$this->index;

        return $time;
    }

    /**
     * Resets the sequence back to the beginning.
     *
     * Returns the internal index to zero, allowing the sequence to be replayed
     * from the start without creating a new clock instance.
     */
    public function reset(): void
    {
        $this->index = 0;
    }

    /**
     * Checks if more datetime values remain in the sequence.
     *
     * @return bool true when additional datetime values are available to return,
     *              false when the sequence has been exhausted
     */
    public function hasNext(): bool
    {
        return $this->index < count($this->times);
    }
}
