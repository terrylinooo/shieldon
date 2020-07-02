<?php
/*
 * This file is part of the Shieldon package.
 *
 * (c) Terry L. <contact@terryl.in>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 * 
 */

declare(strict_types=1);

namespace Shieldon\Firewall\Utils;

/**
 * This is a very, very simple container.
 * For storing Shieldon releated instances.
 */
class Container
{
    /**
     * @var array
     */
    private static $instances = [];

    /**
     * Find an entry of the container by its identifier and returns it.
     *
     * @param string $id Identifier of the entry to look for.
     *
     * @return mixed Entry.
     */
    public static function get(string $id)
    {
        if (self::has($id)) {
            return self::$instances[$id];
        }

        return null;
    }

    /**
     * Return true if the container can return an entry for the given identifier.
     * Return false otherwise.
     *
     * @param string $id Identifier of the entry to look for.
     *
     * @return bool
     */
    public static function has(string $id): bool
    {
        return isset(self::$instances[$id]);
    }

    /**
     * Set an entry into container.
     *
     * @param object $id        Identifier of the entry to look for.
     * @param mixed  $entry     Entry.
     * @param bool   $overwrite Overwrite it even exists.
     *
     * @return void
     */
    public static function set(string $id, $entry, bool $overwrite = true): void
    {
        if (!self::has($id) || $overwrite) {
            self::$instances[$id] = $entry;
        }
    }

    /**
     * Unset an entry of the Container.
     *
     * @param string $id Identifier of the entry to look for.
     *
     * @return void
     */
    public static function remove(string $id): void
    {
        if (self::has($id)) {
            self::$instances[$id] = null;
            unset(self::$instances[$id]);
        }
    }
}
