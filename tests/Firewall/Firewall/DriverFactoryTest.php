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

namespace Shieldon\Firewall\Tests\Firewall;

class DriverFactoryTest extends \PHPUnit\Framework\TestCase
{
    public function testGetInstance()
    {
        $instance = new \Shieldon\Firewall\Firewall\Driver\DriverFactory();
        $fileDriver = $instance::getInstance('file', ['directory_path' => '/']);

        if ($fileDriver instanceof \Shieldon\Firewall\Driver\FileDriver) {
            $this->assertTrue(true);
        } else {
            $this->assertTrue(false);
        }
    }
}