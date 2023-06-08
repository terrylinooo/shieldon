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

use Shieldon\Firewall\Driver\MysqlDriver;
use PDO;
use PDOException;

/**
 * Get MySQL driver.
 */
class ItemMysqlDriver
{
    /**
     * Initialize and get the instance.
     *
     * @param array $setting The configuration of that driver.
     *
     * @return MysqlDriver|null
     */
    public static function get(array $setting)
    {
        $instance = null;

        try {
            $host = 'mysql' .
                ':host='    . $setting['host'] .
                ';dbname='  . $setting['dbname'] .
                ';charset=' . $setting['charset'];

            $user = (string) $setting['user'];
            $pass = (string) $setting['pass'];

            // Create a PDO instance.
            $pdoInstance = new PDO($host, $user, $pass);

            // Use MySQL data driver.
            $instance = new MysqlDriver($pdoInstance);

            // @codeCoverageIgnoreStart
        } catch (PDOException $e) {
            echo $e->getMessage();
        }

        // @codeCoverageIgnoreEnd
        return $instance;
    }
}
