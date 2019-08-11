<?php declare(strict_types=1);

/*
 * This file is part of the Shieldon package.
 *
 * (c) Terry L. <contact@terryl.in>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use Shieldon\Shieldon;
use Exception;

/**
 * Global varible for storing Shieldon instances.
 * The key value is `Channel` name.
 *
 * @var array
 */
$_SHIELDON = [];

if (! function_exists('get_shieldon_instance')) {

    /**
     * Get a Shieldon instane from global variable.
     *
     * @param string $channel The name of Channel.
     *
     * @return object Shieldon instance.
     */
    function get_shieldon_instance(string $channel = '') {

        global $_SHIELDON;
    
        if ('' === $channel) {
            $channel = 'channel_not_set';
        }
    
        if (isset($_SHIELDON[$channel])) {
            return $_SHIELDON[$channel];
        }
    
        return new stdClass();
    }
}

if (! function_exists('set_shieldon_instance')) {

    /**
     * Set a Shieldon instane of global variable.
     *
     * @param object $instance Shieldon instance.
     * @param string $channel  The name of Channel.
     */
    function set_shieldon_instance($instance, string $channel = '') {

        global $_SHIELDON;
    
        if ('' === $channel) {
            $channel = 'channel_not_set';
        }

        if ($instance instanceof Shieldon) {
            $_SHIELDON[$channel] = $instance;
        } else {
            throw new Exception('Parameter 1 should be a Shieldon instance.');
        }
    }
}
