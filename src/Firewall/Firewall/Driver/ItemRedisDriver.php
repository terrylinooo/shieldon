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

namespace Shieldon\Firewall\Firewall\Driver;

use Shieldon\Firewall\Driver\RedisDriver;
use Redis;
use Exception;

/**
 * Get Redis driver.
 */
class ItemRedisDriver
{
    /**
     * Initialize and get the instance.
     *
     * @param array $setting The configuration of that driver.
     *
     * @return RedisDriver|null
     */
    public static function get(array $setting)
    {
        $instance = null;

        try {
            $host = '127.0.0.1';
            $port = 6379;

            if (!empty($setting['host'])) {
                $host = $setting['host'];
            }

            if (!empty($setting['port'])) {
                $port = $setting['port'];
            }

            // Create a Redis instance.
            $redis = new Redis();
            if (empty($setting['port'])) {
                $redis->connect($host);
            } else {
                $redis->connect($host, $port);
            }

            if (!empty($setting['auth'])) {
                // @codeCoverageIgnoreStart
                $redis->auth($setting['auth']);
                // @codeCoverageIgnoreEnd
            }

            // Use Redis data driver.
            $instance = new RedisDriver($redis);

            // @codeCoverageIgnoreStart
        } catch (Exception $e) {
            echo $e->getMessage();
        }
        // @codeCoverageIgnoreEnd

        return $instance;
    }
}
