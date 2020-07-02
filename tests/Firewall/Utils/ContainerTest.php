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

namespace Shieldon\Firewall\Utils;

class ContainerTest extends \PHPUnit\Framework\TestCase
{
    public function testAll()
    {
        $firewall = new \Shieldon\Firewall\Firewall();
        $firewall->configure(BOOTSTRAP_DIR . '/../tmp/shieldon');

        $firewall = \Shieldon\Firewall\Utils\Container::get('firewall');

        if ($firewall instanceof \Shieldon\Firewall\Firewall) {
            $this->assertTrue(true);
        } else {
            $this->assertTrue(false);
        }

        $typo = \Shieldon\Firewall\Utils\Container::get('firewall_typo');
        $this->assertEquals($typo, null);

        $result = \Shieldon\Firewall\Utils\Container::has('firewall');
        $this->assertTrue($result);

        \Shieldon\Firewall\Utils\Container::remove('firewall');
        $result = \Shieldon\Firewall\Utils\Container::has('firewall');
        $this->assertFalse($result);
    }
}