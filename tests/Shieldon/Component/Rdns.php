<?php declare(strict_types=1);
/*
 * This file is part of the Shieldon package.
 *
 * (c) Terry L. <contact@terryl.in>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Shieldon\Component;


class RdnsTest extends \PHPUnit\Framework\TestCase
{
    public function testSetStrict()
    {
        $rdnsComponent = new Rdns();
        $rdnsComponent->setStrict(false);

        $reflection = new \ReflectionObject($rdnsComponent);
        $t = $reflection->getProperty('strictMode');
        $t->setAccessible(true);
  
        $this->assertEquals('strictMode' , $t->name);
        $this->assertFalse($t->getValue($rdnsComponent));
    }
}
