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

namespace Shieldon\FirewallTest\Driver;

class SqliteDriverTest extends \Shieldon\FirewallTest\ShieldonTestCase
{
    public function test__construct()
    {
        try {
            $pdoInstance = new \PDO('sqlite::memory:');
            $db = new \Shieldon\Firewall\Driver\SqliteDriver($pdoInstance);
        } catch (\PDOException $e) {
            $this->assertTrue(false);
        }

        if ($db instanceof MysqlDriver) {
            $this->assertTrue(true);
        }
    }
    

    public function testInstallSql()
    {
        $dbLocation = $this->getWritableTestFilePath('shieldon_unittest.sqlite3');
        $pdoInstance = new \PDO('sqlite:' . $dbLocation);
        $db = new \Shieldon\Firewall\Driver\SqliteDriver($pdoInstance);

        try {
            $class = new \ReflectionObject($db);
            $method = $class->getMethod('installSql');
            $method->setAccessible(true);
            $method->invoke($db);
            $this->assertTrue(true);
        } catch (\Exception $e) {
            $this->assertTrue(false);
        }
    }

    public function testCheckTableExists()
    {
        $dbLocation = $this->getWritableTestFilePath('shieldon_unittest.sqlite3');
        $pdoInstance = new \PDO('sqlite:' . $dbLocation);
        $db = new \Shieldon\Firewall\Driver\SqliteDriver($pdoInstance);

        try {
            $class = new \ReflectionObject($db);
            $method = $class->getMethod('checkTableExists');
            $method->setAccessible(true);
            $result = $method->invoke($db);
            $this->assertTrue($result);
        } catch (\Exception $e) {
            $this->assertTrue(false);
        }
    }
}
