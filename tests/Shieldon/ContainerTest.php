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


class ContainerTest extends \PHPUnit\Framework\TestCase
{
    public function test_get()
    {
        $firewall = new \Shieldon\Firewall(BOOTSTRAP_DIR . '/../tmp/shieldon');

        $firewall = \Shieldon\Container::get('firewall');

        if ($firewall instanceof \Shieldon\Firewall) {
            $this->assertTrue(true);
        } else {
            $this->assertTrue(false);
        }
    }
}