<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cline\Clock\Laravel;

use Cline\Clock\Clocks\CarbonImmutableClock;
use Cline\Clock\Contracts\ClockInterface;
use Illuminate\Support\ServiceProvider;
use Override;

/**
 * Laravel service provider for clock functionality.
 *
 * Registers the clock implementation as a singleton in the service container,
 * making it available for dependency injection throughout the application.
 * The default implementation uses CarbonImmutableClock for Laravel integration.
 *
 * @author Brian Faust <brian@cline.sh>
 */
final class ClockServiceProvider extends ServiceProvider
{
    /**
     * Register clock services in the container.
     *
     * Binds the ClockInterface to a singleton instance of CarbonImmutableClock,
     * ensuring that the same clock instance is used throughout the application
     * lifecycle. Also creates an alias 'clock' for convenient resolution.
     */
    #[Override()]
    public function register(): void
    {
        $this->app->singleton(fn ($app): ClockInterface => new CarbonImmutableClock());

        $this->app->alias(ClockInterface::class, 'clock');
    }

    /**
     * Get the services provided by this service provider.
     *
     * Returns the list of services that this provider makes available,
     * allowing Laravel to defer provider loading until these services
     * are actually needed.
     *
     * @return array<int, string> Array of service identifiers provided by this provider
     */
    #[Override()]
    public function provides(): array
    {
        return [
            ClockInterface::class,
            'clock',
        ];
    }
}
