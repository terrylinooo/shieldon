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

class ItemFileDriverTest extends \PHPUnit\Framework\TestCase
{
    public function testGet()
    {
        $instance = new \Shieldon\Firewall\Firewall\Driver\ItemFileDriver();
        $fileDriver = $instance::get(['directory_path' => '/']);

        if ($fileDriver instanceof \Shieldon\Firewall\Driver\FileDriver) {
            $this->assertTrue(true);
        } else {
            $this->assertTrue(false);
        }
    }
}