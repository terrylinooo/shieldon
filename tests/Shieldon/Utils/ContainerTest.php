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

namespace Shieldon\Utils;

class ContainerTest extends \PHPUnit\Framework\TestCase
{
    public function testAll()
    {
        $firewall = new \Shieldon\Firewall();
        $firewall->configure(BOOTSTRAP_DIR . '/../tmp/shieldon');

        $firewall = \Shieldon\Utils\Container::get('firewall');

        if ($firewall instanceof \Shieldon\Firewall) {
            $this->assertTrue(true);
        } else {
            $this->assertTrue(false);
        }

        $typo = \Shieldon\Utils\Container::get('firewall_typo');
        $this->assertEquals($typo, null);

        $result = \Shieldon\Utils\Container::has('firewall');
        $this->assertTrue($result);

        \Shieldon\Utils\Container::remove('firewall');
        $result = \Shieldon\Utils\Container::has('firewall');
        $this->assertFalse($result);
    }
}