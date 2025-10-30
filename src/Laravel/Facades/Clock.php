<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cline\Clock\Laravel\Facades;

use Cline\Clock\Contracts\ClockInterface;
use Illuminate\Support\Facades\Facade;

/**
 * Laravel facade for accessing clock functionality.
 *
 * Provides static access to the ClockInterface implementation registered
 * in the service container. This facade allows convenient time retrieval
 * throughout the application without manual dependency injection.
 *
 * ```php
 * use Cline\Clock\Laravel\Facades\Clock;
 *
 * $now = Clock::now(); // Returns current DateTimeImmutable
 * ```
 *
 * @author Brian Faust <brian@cline.sh>
 *
 * @method static \DateTimeImmutable now() Returns the current time as an immutable DateTime object
 *
 * @see ClockInterface
 */
final class Clock extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string The service container binding key for the clock implementation
     */
    protected static function getFacadeAccessor(): string
    {
        return ClockInterface::class;
    }
}
