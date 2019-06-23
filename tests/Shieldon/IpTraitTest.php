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

class IpTraitTest extends \PHPUnit\Framework\TestCase
{
    public function testSetIp()
    {
        $mock = $this->getMockForTrait('Shieldon\IpTrait');
        $mock->setIp('192.168.1.1');

        $this->assertSame('192.168.1.1', $mock->getIp());

        $_SERVER['REMOTE_ADDR'] = '192.168.22.22';

        $mock->setIp();

        $this->assertNotSame('192.168.1.2', $mock->getIp());
    }

    public function testGetIp()
    {
        $mock = $this->getMockForTrait('Shieldon\IpTrait');
        $mock->setIp('192.168.3.3');

        $this->assertSame('192.168.3.3', $mock->getIp());
    }

    public function testSetRdns()
    {
        $mock = $this->getMockForTrait('Shieldon\IpTrait');
        $mock->setRdns('unitest.local');

        $this->assertSame('unitest.local', $mock->getRdns());
    }


    public function testGetRdns()
    {
        $mock = $this->getMockForTrait('Shieldon\IpTrait');
        $mock->setRdns('unitest.local2');

        $this->assertSame('unitest.local2', $mock->getRdns());
    }
}