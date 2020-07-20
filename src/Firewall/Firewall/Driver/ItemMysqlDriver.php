<?php
/*
 * This file is part of the Shieldon package.
 *
 * (c) Terry L. <contact@terryl.in>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
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
     * @return RedisDriver|null
     */
    public static function get(array $setting)
    {
        $instance = null;

        try {

            // Create a PDO instance.
            $pdoInstance = new PDO(
                'mysql:host=' 
                    . $setting['host']   . ';dbname=' 
                    . $setting['dbname'] . ';charset=' 
                    . $setting['charset']
                , (string) $setting['user']
                , (string) $setting['pass']
            );

            // Use MySQL data driver.
            $instance = new MysqlDriver($pdoInstance);

        // @codeCoverageIgnoreStart

        } catch(PDOException $e) {
            echo $e->getMessage();
        }

        // @codeCoverageIgnoreEnd

        return $instance;
    }
}