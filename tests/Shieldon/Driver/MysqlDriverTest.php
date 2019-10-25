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


class MysqlDriverTest extends \PHPUnit\Framework\TestCase
{
    public function  test__construct()
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

        try {
            $db = new MysqlDriver($pdoInstance);
        } catch(\PDOException $e) {
            $this->assertTrue(false);
        }

        if ($db instanceof MysqlDriver) {
            $this->assertTrue(true);
        }
    }
}