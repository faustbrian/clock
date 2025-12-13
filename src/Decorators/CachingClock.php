<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cline\Clock\Decorators;

use Cline\Clock\Contracts\ClockInterface;
use DateTimeImmutable;
use Illuminate\Support\Facades\Date;

/**
 * Decorator that caches clock time values for a configurable TTL period.
 *
 * This decorator wraps another clock implementation and caches the time value
 * for a specified number of seconds. Subsequent calls to now() within the TTL
 * window return the cached value, reducing overhead from repeated time calculations
 * or system calls. This is useful for performance optimization in scenarios where
 * multiple time checks occur in rapid succession and microsecond precision is not required.
 *
 * @author Brian Faust <brian@cline.sh>
 */
final class CachingClock implements ClockInterface
{
    /** @var null|DateTimeImmutable Cached time value from the wrapped clock */
    private ?DateTimeImmutable $cached = null;

    /** @var null|int Unix timestamp when the cache was last populated */
    private ?int $cachedAt = null;

    /**
     * Create a new caching clock decorator.
     *
     * @param ClockInterface $clock      The underlying clock implementation to wrap and cache
     * @param int            $ttlSeconds Time-to-live in seconds for cached values. Once elapsed,
     *                                   the next call to now() will fetch a fresh value from the
     *                                   underlying clock and reset the cache timer. Defaults to 1 second.
     */
    public function __construct(
        private readonly ClockInterface $clock,
        private readonly int $ttlSeconds = 1,
    ) {}

    /**
     * Returns the current time, using cached value if still valid.
     *
     * Checks if a cached time exists and is still within the TTL window.
     * If the cache is expired or empty, fetches a fresh value from the
     * underlying clock and updates the cache.
     *
     * @return DateTimeImmutable The current time or cached time if within TTL
     */
    public function now(): DateTimeImmutable
    {
        if (!$this->cached instanceof DateTimeImmutable || $this->isCacheExpired()) {
            $this->cached = $this->clock->now();
            $this->cachedAt = Date::now()->getTimestamp();
        }

        return $this->cached;
    }

    /**
     * Clears the cached time value and forces next call to fetch fresh time.
     *
     * This method is useful when you need to ensure that the next call to now()
     * returns a fresh value from the underlying clock, bypassing the cache entirely.
     */
    public function clear(): void
    {
        $this->cached = null;
        $this->cachedAt = null;
    }

    /**
     * Determines if the cached time value has exceeded the TTL.
     *
     * @return bool True if cache is expired or never populated, false otherwise
     */
    private function isCacheExpired(): bool
    {
        if ($this->cachedAt === null) {
            return true;
        }

        $elapsed = Date::now()->getTimestamp() - $this->cachedAt;

        return $elapsed >= $this->ttlSeconds;
    }
}
