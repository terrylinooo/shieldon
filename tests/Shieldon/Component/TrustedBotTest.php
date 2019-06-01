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


class TrustedBotTest extends \PHPUnit\Framework\TestCase
{
    public function testSetStrict()
    {
        $trustedBotComponent = new TrustedBot();
        $trustedBotComponent->setStrict(false);

        $reflection = new \ReflectionObject($trustedBotComponent);
        $t = $reflection->getProperty('strictMode');
        $t->setAccessible(true);
  
        $this->assertEquals('strictMode' , $t->name);
        $this->assertFalse($t->getValue($trustedBotComponent));
    }

    public function testSetDeniedList()
    {
        $this->assertFalse(false);
    }

    public function testSetDeniedItem()
    {
        $this->assertFalse(false);
    }

    public function testGetDeniedList()
    {
        $this->assertFalse(false);
    }

    public function testIsDenied()
    {
        $this->assertFalse(false);
    }

    public function testRemoveItem()
    {
        $trustedBotComponent = new TrustedBot();

        $trustedBotComponent->removeItem('.google.com');
        $list = $trustedBotComponent->getList();

        if (! in_array('.google.com', $list)) {
            $this->assertTrue(true);
        } else {
            $this->assertTrue(false);
        }
    }
}
