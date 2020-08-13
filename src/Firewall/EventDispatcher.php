<?php
/**
 * This file is part of the Shieldon package.
 *
 * (c) Terry L. <contact@terryl.in>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * php version 7.1.0
 *
 * @category  Web-security
 * @package   Shieldon
 * @author    Terry Lin <contact@terryl.in>
 * @copyright 2019 terrylinooo
 * @license   https://github.com/terrylinooo/shieldon/blob/2.x/LICENSE MIT
 * @link      https://github.com/terrylinooo/shieldon
 * @see       https://shieldon.io
 */

declare(strict_types=1);

namespace Shieldon\Firewall;

/**
 * This is just a simple event dispatcher for Shieldon firewall.
 */
class EventDispatcher
{
    /**
     * Singleton pattern based instance.
     *
     * @var self|null
     */
    public static $instance;
    
    /**
     * The collection of events.
     *
     * @var array|null
     */
    public static $events;

    /**
     * Constructer.
     */
    private function __construct()
    {
        self::$instance = null;

        self::$events = [];
    }

    /**
     * Singleton.
     *
     * @return self
     */
    public static function instance(): self
    {
        if (!self::$instance) {
            self::$instance = new EventDispatcher();
        }

        return self::$instance;
    }

    /**
     * Add a listener.
     *
     * @param string        $name      The name of an event.
     * @param string|array  $func      Callable function or class.
     * @param int           $priority  The execution priority.
     *
     * @return bool
     */
    public function addListener($name, $func, $priority = 10)
    {
        // The priority postion has been taken.
        if (isset(self::$events[$name][$priority])) {
            return false;
        }

        // $func should be a function name or a callable function.
        self::$events[$name][$priority] = $func;

        // Or, it is an array contains Class and method name.
        if (is_array($func)) {

            self::$events[$name][$priority] = [
                $func[0],
                $func[1],
            ];
        }
        
        return true;
    }

    /**
     * Execute the listener.
     *
     * @param string $name The name of an event.
     * @param array  $args The arguments.
     *
     * @return mixed
     */
    public function doDispatch(string $name, $args = [])
    {
        if (!isset(self::$events[$name])) {
            return;
        }

        $return = null;

        ksort(self::$events[$name]);
        
        foreach (self::$events[$name] as $action) {

            if (is_string($action) && function_exists($action)) {
                $return = call_user_func_array(
                    $action, // Callable function.
                    $args
                );

            } elseif (is_array($action)) {
                $return = call_user_func_array(
                    [
                        $action[0], // Class.
                        $action[1], // The method of that class.
                    ],
                    $args
                );
            } elseif (is_callable($action)) {
                $return = $action($args);
            }
        }

        return $return;
    }
}

