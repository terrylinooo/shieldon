<?php declare(strict_types=1);
/*
 * This file is part of the Shieldon package.
 *
 * (c) Terry L. <contact@terryl.in>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Shieldon\Driver;


class AbstractDriverTest extends \PHPUnit\Framework\TestCase
{
    public function testHas()
    {
        $mock = $this->getMockForAbstractClass('Shieldon\Driver\AbstractDriver');
        $this->assertFalse($mock->has('22.22.22.22'));
    }
}
