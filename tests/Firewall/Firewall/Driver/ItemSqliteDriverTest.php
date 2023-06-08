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

class ItemSqliteDriverTest extends \Shieldon\FirewallTest\ShieldonTestCase
{
    public function testGet()
    {
        $instance = new \Shieldon\Firewall\Firewall\Driver\ItemSqliteDriver();
        $sqliteDriver = $instance::get(
            [
                'directory_path' => BOOTSTRAP_DIR . '/../tmp',
            ]
        );

        if ($sqliteDriver instanceof \Shieldon\Firewall\Driver\SqliteDriver) {
            $this->assertTrue(true);
        } else {
            $this->assertTrue(false);
        }
    }

    public function testGetWithInvalidSetting()
    {
        $instance = new \Shieldon\Firewall\Firewall\Driver\ItemSqliteDriver();
        $sqliteDriver = $instance::get(['directory_path' => '']);

        $this->assertEquals($sqliteDriver, null);
    }
}
