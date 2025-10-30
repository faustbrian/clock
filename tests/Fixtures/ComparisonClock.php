<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Tests\Fixtures;

use Cline\Clock\Clocks\FrozenClock;
use Cline\Clock\Contracts\ClockInterface;
use Cline\Clock\Support\ClockComparison;
use DateTimeImmutable;

/**
 * @author Brian Faust <brian@cline.sh>
 *
 * @psalm-immutable
 */
final readonly class ComparisonClock implements ClockInterface
{
    use ClockComparison;

    public function __construct(
        private FrozenClock $clock,
    ) {}

    public function now(): DateTimeImmutable
    {
        return $this->clock->now();
    }
}
