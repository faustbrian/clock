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
use Psr\Log\LoggerInterface;

/**
 * Decorator that logs every time retrieval operation to a PSR-3 logger.
 *
 * This decorator wraps another clock implementation and logs detailed information
 * about each call to now(), including the timestamp, timezone, and underlying clock
 * class. This is useful for debugging time-related issues, auditing time access patterns,
 * or monitoring clock usage in production environments.
 *
 * @author Brian Faust <brian@cline.sh>
 *
 * @psalm-immutable
 */
final readonly class LoggingClock implements ClockInterface
{
    /**
     * Create a new logging clock decorator.
     *
     * @param ClockInterface  $clock  The underlying clock implementation to wrap and log
     * @param LoggerInterface $logger PSR-3 logger instance that receives time retrieval log entries
     * @param string          $level  PSR-3 log level for time retrieval messages. Should be one of:
     *                                'emergency', 'alert', 'critical', 'error', 'warning', 'notice',
     *                                'info', or 'debug'. Defaults to 'debug' for minimal production impact.
     */
    public function __construct(
        private ClockInterface $clock,
        private LoggerInterface $logger,
        private string $level = 'debug',
    ) {}

    /**
     * Returns the current time and logs the operation.
     *
     * Retrieves the current time from the underlying clock and logs the timestamp,
     * timezone, and clock class name at the configured log level. The log context
     * includes microsecond precision for detailed timing analysis.
     *
     * @return DateTimeImmutable The current time from the wrapped clock
     */
    public function now(): DateTimeImmutable
    {
        $now = $this->clock->now();

        $this->logger->log(
            $this->level,
            'Clock returned time',
            [
                'timestamp' => $now->format('Y-m-d H:i:s.u'),
                'timezone' => $now->getTimezone()->getName(),
                'clock_class' => $this->clock::class,
            ],
        );

        return $now;
    }
}
