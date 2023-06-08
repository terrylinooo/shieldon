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

namespace Shieldon\Firewall\Tests\Firewall\Driver;

class ItemMysqlDriverTest extends \Shieldon\FirewallTest\ShieldonTestCase
{
    public function testGet()
    {
        $setting['host'] = 'localhost';
        $setting['dbname'] = 'shieldon_unittest';
        $setting['charset'] = 'utf8';
        $setting['user'] = 'shieldon';
        $setting['pass'] = 'taiwan';

        $instance = new \Shieldon\Firewall\Firewall\Driver\ItemMysqlDriver();
        $mysqlDriver = $instance::get($setting);

        if ($mysqlDriver instanceof \Shieldon\Firewall\Driver\MysqlDriver) {
            $this->assertTrue(true);
        } else {
            $this->assertTrue(false);
        }
    }

    public function testGetWithInvalidSetting()
    {
        $setting['host'] = 'localhost';
        $setting['dbname'] = 'shieldon_unittest';
        $setting['charset'] = 'utf8';
        $setting['user'] = 'user_not_exist';
        $setting['pass'] = 'taiwan';

        $this->console('Test invalid MySQL database settings.', 'info');

        $instance = new \Shieldon\Firewall\Firewall\Driver\ItemMysqlDriver();
        $mysqlDriver = $instance::get($setting);

        $this->assertEquals($mysqlDriver, null);
    }
}
