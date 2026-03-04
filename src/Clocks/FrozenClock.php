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
use Exception;

/**
 * Clock that always returns a fixed point in time.
 *
 * Useful for testing time-dependent code by providing a deterministic datetime
 * that never changes. Once frozen, the clock will always return the same moment,
 * allowing predictable testing of time-sensitive business logic.
 *
 * @author Brian Faust <brian@cline.sh>
 *
 * @psalm-immutable
 */
final readonly class FrozenClock implements ClockInterface
{
    /**
     * Create a new frozen clock instance.
     *
     * @param DateTimeImmutable $frozenTime The fixed point in time that this clock will
     *                                      always return. This datetime is immutable and
     *                                      will never change throughout the clock's lifecycle.
     */
    public function __construct(
        private DateTimeImmutable $frozenTime,
    ) {}

    /**
     * Creates a frozen clock from a datetime string.
     *
     * Convenience factory method for creating frozen clocks from string representations
     * of datetime values, using any format supported by PHP's DateTimeImmutable constructor.
     *
     * ```php
     * $clock = FrozenClock::fromString('2024-01-15 14:30:00');
     * ```
     *
     * @param string $datetime String representation of the datetime in any format
     *                         recognized by DateTimeImmutable (e.g., 'now', '2024-01-15',
     *                         '2024-01-15 14:30:00', '+1 day').
     *
     * @throws Exception when the datetime string cannot be parsed by DateTimeImmutable
     *
     * @return self frozen clock instance locked to the parsed datetime
     */
    public static function fromString(string $datetime): self
    {
        return new self(
            new DateTimeImmutable($datetime),
        );
    }

    /**
     * Returns the frozen datetime.
     *
     * @return DateTimeImmutable The fixed point in time that was set during construction.
     *                           This value never changes across multiple calls.
     */
    public function now(): DateTimeImmutable
    {
        return $this->frozenTime;
    }
}
