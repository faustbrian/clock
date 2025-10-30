<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cline\Clock\Contracts;

use Cline\Clock\Clocks\FrozenClock;

/**
 * Defines the contract for clocks that can be frozen at a specific point in time.
 *
 * This interface allows clock implementations to create frozen snapshots that
 * always return the same time value. This is particularly useful for testing
 * scenarios where time-dependent behavior needs to be deterministic and predictable.
 *
 * @author Brian Faust <brian@cline.sh>
 */
interface FreezableInterface
{
    /**
     * Creates a frozen clock that always returns the current time.
     *
     * The returned frozen clock will continue to return the same time value
     * on every call to now(), effectively stopping time at the moment this
     * method was called.
     *
     * @return FrozenClock A new clock instance frozen at the current time
     */
    public function freeze(): FrozenClock;
}
