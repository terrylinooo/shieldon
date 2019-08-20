<?php declare(strict_types=1);
/*
 * This file is part of the Shieldon package.
 *
 * (c) Terry L. <contact@terryl.in>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 * 
 */

namespace Shieldon;

use Shieldon\Shieldon;
use Exception;

/**
 * For storing Shieldon instances.
 * 
 * @since 3.0.0
 */
class Instance
{
    /**
     * @var array
     */
    public static $instances = [];

    /**
     * Get a Shieldon instane from global variable.
     *
     * @param string $channel The name of Channel.
     *
     * @return object
     */
    public static function get(string $channel = ''): object
    {
        if ('' === $channel) {
            $channel = 'channel_not_set';
        }

        if (! isset(self::$instances[$channel])) {
            throw new Exception('Shieldon instance not found.');
        }

        return self::$instances[$channel];
    }

    /**
     * Set a Shieldon instane of global variable.
     *
     * @param object $instance Shieldon instance.
     * @param string $channel  The name of Channel.
     *
     * @return void
     */
    public static function set(object $instance, string $channel = ''): void
    {
        if ('' === $channel) {
            $channel = 'channel_not_set';
        }

        if ($instance instanceof Shieldon) {
            self::$instances[$channel] = $instance;
        } else {
            throw new Exception('Parameter $1 should be a Shieldon instance.');
        }
    }
}
