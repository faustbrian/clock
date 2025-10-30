<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cline\Clock\Support;

use Cline\Clock\Contracts\ClockInterface;
use RuntimeException;

use function array_key_exists;
use function array_keys;
use function sprintf;
use function throw_if;
use function throw_unless;

/**
 * Global registry for managing named clock instances.
 *
 * Provides a centralized location to register, retrieve, and manage multiple
 * clock instances by name. This is useful for applications that need to work
 * with multiple clocks simultaneously, such as testing scenarios with different
 * time sources or multi-timezone applications. The registry also supports
 * designating one clock as the default for convenient access.
 *
 * @author Brian Faust <brian@cline.sh>
 */
final class ClockRegistry
{
    /** @var array<string, ClockInterface> Map of clock names to clock instances */
    private static array $clocks = [];

    /** @var null|string Name of the default clock, or null if no default is set */
    private static ?string $default = null;

    /**
     * Registers a clock instance with the given name.
     *
     * If a clock with the same name already exists, it will be replaced.
     *
     * @param string         $name  Unique identifier for this clock instance
     * @param ClockInterface $clock The clock implementation to register
     */
    public static function set(string $name, ClockInterface $clock): void
    {
        self::$clocks[$name] = $clock;
    }

    /**
     * Retrieves a registered clock by name.
     *
     * @param string $name The name of the clock to retrieve
     *
     * @throws RuntimeException If no clock is registered with the given name
     *
     * @return ClockInterface The registered clock instance
     */
    public static function get(string $name): ClockInterface
    {
        throw_unless(array_key_exists($name, self::$clocks), RuntimeException::class, sprintf("Clock '%s' not registered", $name));

        return self::$clocks[$name];
    }

    /**
     * Checks if a clock is registered with the given name.
     *
     * @param  string $name The name to check
     * @return bool   True if a clock is registered with this name
     */
    public static function has(string $name): bool
    {
        return array_key_exists($name, self::$clocks);
    }

    /**
     * Removes a registered clock by name.
     *
     * If the removed clock was set as the default, the default is cleared.
     * Removing a non-existent clock has no effect.
     *
     * @param string $name The name of the clock to remove
     */
    public static function remove(string $name): void
    {
        unset(self::$clocks[$name]);

        if (self::$default === $name) {
            self::$default = null;
        }
    }

    /**
     * Sets the default clock by name.
     *
     * The specified clock must already be registered in the registry.
     *
     * @param string $name The name of the registered clock to set as default
     *
     * @throws RuntimeException If no clock is registered with the given name
     */
    public static function setDefault(string $name): void
    {
        throw_unless(array_key_exists($name, self::$clocks), RuntimeException::class, sprintf("Cannot set default to unregistered clock '%s'", $name));

        self::$default = $name;
    }

    /**
     * Retrieves the default clock instance.
     *
     * @throws RuntimeException If no default clock has been set
     *
     * @return ClockInterface The default clock instance
     */
    public static function getDefault(): ClockInterface
    {
        throw_if(self::$default === null, RuntimeException::class, 'No default clock set');

        return self::get(self::$default);
    }

    /**
     * Checks if a default clock has been set.
     *
     * @return bool True if a default clock is configured
     */
    public static function hasDefault(): bool
    {
        return self::$default !== null;
    }

    /**
     * Returns the names of all registered clocks.
     *
     * @return array<string> Array of registered clock names
     */
    public static function registered(): array
    {
        return array_keys(self::$clocks);
    }

    /**
     * Clears all registered clocks and the default setting.
     *
     * This method is primarily useful for testing to ensure a clean state
     * between test cases.
     */
    public static function clear(): void
    {
        self::$clocks = [];
        self::$default = null;
    }
}
