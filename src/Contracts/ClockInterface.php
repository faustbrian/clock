<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cline\Clock\Contracts;

use DateTimeImmutable;
use Psr\Clock\ClockInterface as PsrClockInterface;

/**
 * Defines the contract for clock implementations that provide current time.
 *
 * This interface extends PSR-20 ClockInterface to provide a standardized
 * way to retrieve the current time as an immutable DateTimeImmutable object.
 * Implementing classes can provide different time sources such as system time,
 * frozen time for testing, or decorated implementations with caching or logging.
 *
 * @author Brian Faust <brian@cline.sh>
 */
interface ClockInterface extends PsrClockInterface
{
    /**
     * Returns the current time as an immutable DateTime object.
     *
     * @return DateTimeImmutable The current point in time
     */
    public function now(): DateTimeImmutable;
}
