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

class MysqlDriverTest extends \Shieldon\FirewallTest\ShieldonTestCase
{
    public function test__construct()
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
            $db = new \Shieldon\Firewall\Driver\MysqlDriver($pdoInstance);
        } catch (\PDOException $e) {
            $this->assertTrue(false);
        }

        if ($db instanceof MysqlDriver) {
            $this->assertTrue(true);
        }
    }
}
