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

namespace Shieldon\FirewallTest;

class ContainerTest extends \Shieldon\FirewallTest\ShieldonTestCase
{
    public function testAll()
    {
        $firewall = new \Shieldon\Firewall\Firewall();
        $firewall->configure(BOOTSTRAP_DIR . '/../tmp/shieldon');

        $firewall = \Shieldon\Firewall\Container::get('firewall');

        if ($firewall instanceof \Shieldon\Firewall\Firewall) {
            $this->assertTrue(true);
        } else {
            $this->assertTrue(false);
        }

        $typo = \Shieldon\Firewall\Container::get('firewall_typo');
        $this->assertEquals($typo, null);

        $result = \Shieldon\Firewall\Container::has('firewall');
        $this->assertTrue($result);

        \Shieldon\Firewall\Container::remove('firewall');
        $result = \Shieldon\Firewall\Container::has('firewall');
        $this->assertFalse($result);
    }
}
