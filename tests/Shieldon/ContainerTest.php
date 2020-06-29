<?php declare(strict_types=1);
/*
 * This file is part of the Shieldon package.
 *
 * (c) Terry L. <contact@terryl.in>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Shieldon;

use PHPUnit\Framework\TestCase;

class ContainerTest extends TestCase
{
    public function testAll()
    {
        $firewall = new \Shieldon\Firewall();
        $firewall->configure(BOOTSTRAP_DIR . '/../tmp/shieldon');

        $firewall = \Shieldon\Container::get('firewall');

        if ($firewall instanceof \Shieldon\Firewall) {
            $this->assertTrue(true);
        } else {
            $this->assertTrue(false);
        }

        $typo = \Shieldon\Container::get('firewall_typo');
        $this->assertEquals($typo, null);

        $result = \Shieldon\Container::has('firewall');
        $this->assertTrue($result);

        \Shieldon\Container::remove('firewall');
        $result = \Shieldon\Container::has('firewall');
        $this->assertFalse($result);
    }
}