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

use Shieldon\Firewall\Driver\SqliteDriver;
use PDO;
use PDOException;

/**
 * Get SQLite driver.
 */
class ItemSqliteDriver
{
    /**
     * Initialize and get the instance.
     *
     * @param array $setting The configuration of that driver.
     *
     * @return SqliteDriver|null
     */
    public static function get(array $setting)
    {
        $instance = null;

        if (empty($setting['directory_path'])) {
            return null;
        }

        try {
            // Specify the sqlite file location.
            $sqliteLocation = $setting['directory_path'] . '/shieldon.sqlite3';
            $pdoInstance = new PDO('sqlite:' . $sqliteLocation);
            $instance = new SqliteDriver($pdoInstance);

            // @codeCoverageIgnoreStart
        } catch (PDOException $e) {
            echo $e->getMessage();
        }
        // @codeCoverageIgnoreEnd

        return $instance;
    }
}
