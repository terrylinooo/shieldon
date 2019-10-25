<?php declare(strict_types=1);
/*
 * This file is part of the Shieldon package.
 *
 * (c) Terry L. <contact@terryl.in>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Shieldon\Driver;

class SqliteDriverTest extends \PHPUnit\Framework\TestCase
{
    public function test__construct()
    {
        try {
            $pdoInstance = new \PDO('sqlite::memory:');
            $db = new SqliteDriver($pdoInstance);
        } catch(\PDOException $e) {
            $this->assertTrue(false);
        }

        if ($db instanceof MysqlDriver) {
            $this->assertTrue(true);
        }
    }
    

    public function testInstallSql()
    {
        $dbLocation = save_testing_file('shieldon_unittest.sqlite3');
        $pdoInstance = new \PDO('sqlite:' . $dbLocation);
        $db = new SqliteDriver($pdoInstance);

        try {
            $class = new \ReflectionObject($db);
            $method = $class->getMethod('installSql');
            $method->setAccessible(true);
            $method->invoke($db);
            $this->assertTrue(true);
        } catch(\Exception $e) {
            $this->assertTrue(false);
        }
    }

    public function testCheckTableExists()
    {
        $dbLocation = save_testing_file('shieldon_unittest.sqlite3');
        $pdoInstance = new \PDO('sqlite:' . $dbLocation);
        $db = new SqliteDriver($pdoInstance);

        try {
            $class = new \ReflectionObject($db);
            $method = $class->getMethod('checkTableExists');
            $method->setAccessible(true);
            $result = $method->invoke($db);
            $this->assertTrue($result);
        } catch(\Exception $e) {
            $this->assertTrue(false);
        }
    }
}