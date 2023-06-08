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

class SqlDriverProviderTest extends \Shieldon\FirewallTest\ShieldonTestCase
{
    public function test__construct()
    {
        try {
            $pdoInstance = new \PDO('sqlite::memory:');
            $db = new \Shieldon\Firewall\Driver\SqliteDriver($pdoInstance, true);
        } catch (\PDOException $e) {
            $this->assertTrue(false);
        }

        if ($db instanceof MysqlDriver) {
            $this->assertTrue(true);
        }
    }


    public function testDoInitialize()
    {
        $db = [
            'host' => '127.0.0.1',
            'dbname' => 'shieldon_unittest',
            'user' => 'shieldon',
            'pass' => 'taiwan',
            'charset' => 'utf8',
        ];
        
        $pdoInstance = new \PDO(
            'mysql:host=' . $db['host'] . ';dbname=' . $db['dbname'] . ';charset=' . $db['charset'],
            $db['user'],
            $db['pass']
        );

        $mockSqlDriver = new \Shieldon\Firewall\Driver\SqlDriverProvider($pdoInstance);
        $reflection = new \ReflectionObject($mockSqlDriver);
        $p1 = $reflection->getProperty('tableFilterLogs');
        $p1->setAccessible(true);
        $p2 = $reflection->getProperty('tableRuleList');
        $p2->setAccessible(true);
        $p3 = $reflection->getProperty('tableSessions');
        $p3->setAccessible(true);
        $p4 = $reflection->getProperty('isInitialized');
        $p4->setAccessible(true);

        $tableFilterLogs = $p1->getValue($mockSqlDriver);
        $tableRuleList = $p2->getValue($mockSqlDriver);
        $tableSessions = $p3->getValue($mockSqlDriver);
        $isInitialized = $p4->getValue($mockSqlDriver);

        $this->assertEmpty($isInitialized);

        $pdoInstance->query("DROP TABLE IF EXISTS `{$tableFilterLogs}`");
        $pdoInstance->query("DROP TABLE IF EXISTS `{$tableRuleList}`");
        $pdoInstance->query("DROP TABLE IF EXISTS `{$tableSessions}`");

        // testCheckTableExists - First
        $methodCheckTableExists = $reflection->getMethod('CheckTableExists');
        $methodCheckTableExists->setAccessible(true);
        $result = $methodCheckTableExists->invokeArgs($mockSqlDriver, []);
        $this->assertFalse($result);

        $methodDoInitialize = $reflection->getMethod('doInitialize');
        $methodDoInitialize->setAccessible(true);
        $methodDoInitialize->invokeArgs($mockSqlDriver, []);

        $isInitialized = $p4->getValue($mockSqlDriver);
        $this->assertTrue($isInitialized);

        // testCheckTableExists - Second.
        $result = $methodCheckTableExists->invokeArgs($mockSqlDriver, []);
        $this->assertTrue($result);
    }

    public function testDoFetch()
    {
        // Just for code coverage, for the section - session.
        $db = [
            'host' => '127.0.0.1',
            'dbname' => 'shieldon_unittest',
            'user' => 'shieldon',
            'pass' => 'taiwan',
            'charset' => 'utf8',
        ];
        
        $pdoInstance = new \PDO(
            'mysql:host=' . $db['host'] . ';dbname=' . $db['dbname'] . ';charset=' . $db['charset'],
            $db['user'],
            $db['pass']
        );

        $mockSqlDriver = new \Shieldon\Firewall\Driver\SqlDriverProvider($pdoInstance);
        $mockSqlDriver->init();
        $reflection = new \ReflectionObject($mockSqlDriver);
        $methodDoFetch = $reflection->getMethod('doFetch');
        $methodDoFetch->setAccessible(true);
        $methodDoFetch->invokeArgs($mockSqlDriver, ['qazxswedcvfrtgbnhyujmkio', 'session']);
    }


    public function testDoFetchAll()
    {
        // Just for code coverage, for the section - rule and log.
        $db = [
            'host' => '127.0.0.1',
            'dbname' => 'shieldon_unittest',
            'user' => 'shieldon',
            'pass' => 'taiwan',
            'charset' => 'utf8',
        ];
        
        $pdoInstance = new \PDO(
            'mysql:host=' . $db['host'] . ';dbname=' . $db['dbname'] . ';charset=' . $db['charset'],
            $db['user'],
            $db['pass']
        );

        $mockSqlDriver = new \Shieldon\Firewall\Driver\SqlDriverProvider($pdoInstance);
        $reflection = new \ReflectionObject($mockSqlDriver);
        $methodDoFetchAll = $reflection->getMethod('doFetchAll');
        $methodDoFetchAll->setAccessible(true);
        $methodDoFetchAll->invokeArgs($mockSqlDriver, ['rule']);
        $methodDoFetchAll->invokeArgs($mockSqlDriver, ['filter']);
    }

    public function testDoDeleteInvalidDataType()
    {
        $db = [
            'host' => '127.0.0.1',
            'dbname' => 'shieldon_unittest',
            'user' => 'shieldon',
            'pass' => 'taiwan',
            'charset' => 'utf8',
        ];
        
        $pdoInstance = new \PDO(
            'mysql:host=' . $db['host'] . ';dbname=' . $db['dbname'] . ';charset=' . $db['charset'],
            $db['user'],
            $db['pass']
        );

        $mockSqlDriver = new \Shieldon\Firewall\Driver\SqlDriverProvider($pdoInstance);
        $reflection = new \ReflectionObject($mockSqlDriver);
        $methodDoDelete = $reflection->getMethod('doDelete');
        $methodDoDelete->setAccessible(true);

        $this->expectException(\RuntimeException::class);

        $methodDoDelete->invokeArgs($mockSqlDriver, ['19.89.6.4', 'never_forget']);
    }

    public function testUpdate()
    {
        // Has been tested in other method.
    }

    public function testInsert()
    {
        // Has been tested in other method.
    }

    public function testRemove()
    {
        // Has been tested in other method.
    }

    public function testInstallSql()
    {
        // Has been tested in other method.
    }

    public function testRebuildSql()
    {
        // Has been tested in other method.
    }

    public function testCheckTableExists()
    {
        // Has been tested in other method.
    }

    public function testAssertPrepare()
    {
        $db = [
            'host' => '127.0.0.1',
            'dbname' => 'shieldon_unittest',
            'user' => 'shieldon',
            'pass' => 'taiwan',
            'charset' => 'utf8',
        ];
        
        $pdoInstance = new \PDO(
            'mysql:host=' . $db['host'] . ';dbname=' . $db['dbname'] . ';charset=' . $db['charset'],
            $db['user'],
            $db['pass']
        );

        $mockSqlDriver = new \Shieldon\Firewall\Driver\SqlDriverProvider($pdoInstance);
        $reflection = new \ReflectionObject($mockSqlDriver);
        $method = $reflection->getMethod('assertPrepare');
        $method->setAccessible(true);

        $this->expectException(\RuntimeException::class);

        $method->invokeArgs($mockSqlDriver, [false]);
    }
}
