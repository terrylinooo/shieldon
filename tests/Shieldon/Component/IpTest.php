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

class IpTest extends \PHPUnit\Framework\TestCase
{
    public function testSetStrict()
    {
        $ipComponent = new Ip();
        $ipComponent->setStrict(false);

        $reflection = new \ReflectionObject($ipComponent);
        $t = $reflection->getProperty('strictMode');
        $t->setAccessible(true);
  
        $this->assertEquals('strictMode' , $t->name);
        $this->assertFalse($t->getValue($ipComponent));
    }

    public function testCheck()
    {
        // Test 1. Check incorrect IP address.
        $ipComponent = new Ip();
        $t = $ipComponent->check('128.232.234.256');
        $this->assertIsArray($t);
        $this->assertEquals(3 , count($t));
        $this->assertEquals('deny' , $t['status']);
        unset($ipComponent, $t);

        // Test 2. Check denied list.
        $ipComponent = new Ip();
        $ipComponent->setDeniedItem('127.0.55.44');
        $t = $ipComponent->check('127.0.55.44');

        $this->assertIsArray($t);
        $this->assertEquals(3 , count($t));
        $this->assertEquals('deny' , $t['status']);
        unset($ipComponent, $t);

        // Test 3. Check  allowed list.
        $ipComponent = new Ip();
        $ipComponent->setAllowedItem('39.9.197.241');
        $t = $ipComponent->check('39.9.197.241');

        $this->assertIsArray($t);
        $this->assertEquals(3 , count($t));
        $this->assertEquals('allow' , $t['status']);
        unset($ipComponent, $t);

        // Test 4. Check IP is if in denied IP range.
        $ipComponent = new Ip();
        $ipComponent->setDeniedItem('127.0.55.0/16');
        $t = $ipComponent->check('127.0.33.1');
        $this->assertIsArray($t);
        $this->assertEquals(3 , count($t));
        $this->assertEquals('deny' , $t['status']);
        unset($ipComponent, $t);

        // Test 5. Check IP is if in allowed IP range.
        $ipComponent = new Ip();
        $ipComponent->setDeniedItem('127.0.55.0/16');
        $ipComponent->setAllowedItem('127.0.55.0/16');
        $t = $ipComponent->check('127.0.33.1');
        $this->assertIsArray($t);
        $this->assertEquals(3 , count($t));
        $this->assertEquals('allow' , $t['status']);
        unset($ipComponent, $t);

        // Test 6. Test denyAll
        $ipComponent = new Ip();
        $ipComponent->denyAll();
        $t = $ipComponent->check('127.0.33.1');
        $this->assertIsArray($t);
        $this->assertEquals(3 , count($t));
        $this->assertEquals('deny' , $t['status']);
        unset($ipComponent, $t);
    }

    public function testInRange()
    {
        // Test 1. Check IP is if in D class subnet.
        $ipComponent = new Ip();
        $t = $ipComponent->inRange('127.0.0.44', '127.0.0.0/24');
        $this->assertEquals(true , $t);
        $t = $ipComponent->inRange('127.0.250.44', '127.0.250.250/24');
        $this->assertEquals(true , $t);
        $t = $ipComponent->inRange('127.0.1.44', '127.0.0.0/24');
        $this->assertEquals(false , $t);

        // Single Ip
        $t = $ipComponent->inRange('127.0.0.1', '127.0.0.1');
        $this->assertEquals(true , $t);

        // Test 2. Check IP is if in C class subnet.
        $ipComponent = new Ip();
        $t = $ipComponent->inRange('127.0.33.33', '127.0.0.0/16');
        $this->assertEquals(true , $t);
        $t = $ipComponent->inRange('127.0.33.33', '127.0.250.0/16');
        $this->assertEquals(true , $t);
        $t = $ipComponent->inRange('127.1.33.33', '127.0.0.0/16');
        $this->assertEquals(false , $t);

        // Test 3. Check IP is if in B class subnet.
        $ipComponent = new Ip();
        $t = $ipComponent->inRange('127.33.250.33', '127.0.0.0/8');
        $this->assertEquals(true , $t);
        $t = $ipComponent->inRange('127.33.33.33', '127.0.0.0/8');
        $this->assertEquals(true , $t); 
        $t = $ipComponent->inRange('128.33.250.33', '127.0.0.0/8');
        $this->assertEquals(false , $t);

        // Test 4. Check IPv6
        $ipComponent = new Ip();
        $t = $ipComponent->inRange('2001:db8:ffff:ffff:ffff:ffff:ffff:ffff', '2001:db8::/32');
        $this->assertEquals(true , $t);

        $t = $ipComponent->inRange('2001:db8:ffff:ffff:ffff:ffff:ffff:ffff', '2001:db8::0/32');
        $this->assertEquals(false , $t);

        // Test 5. Check Invalid IP
        $ipComponent = new Ip();
        $t = $ipComponent->inRange('127.0.333.33', '127.0.250.0/16');
        $this->assertEquals(false , $t);
    }

    public function testDecimalIpv6()
    {
        $ipComponent = new Ip();
        $t = $ipComponent->decimalIpv6('FE80:0000:0000:0000:0202:B3FF:FE1E:8329');
        $t = $ipComponent->_decimalIpv6('FE80:0000:0000:0000:0202:B3FF:FE1E:8329');
        $this->assertSame('338288524927261046600406220626806860202', $t);
        $this->assertSame('338288524927261046600406220626806860202', $t);
       
        $t = $ipComponent->decimalIpv6('2001:DB8:2de::e13');
        $t = $ipComponent->_decimalIpv6('2001:DB8:2de::e13');
        $this->assertSame('42540766412169952080266446484866804624', $t);
        $this->assertSame('42540766412169952080266446484866804624', $t);
    }

    public function testSetAllowedList()
    {
        $ipComponent = new Ip();
        $s = ['127.33.33.33', '127.33.33.34', '127.33.33.35'];
        $ipComponent->setAllowedList($s);
        $t = $ipComponent->getAllowedList();
        if ($s === $t) {
            $this->assertTrue(true);
        }
    }

    public function testSetAllowedIp()
    {
        $ipComponent = new Ip();
        $s = '127.33.33.33';
        $t = $ipComponent->setAllowedItem($s);
        if ($s === $t) {
            $this->assertTrue(true);
        }
    }

    public function testGetAllowedList()
    {
        $ipComponent = new Ip();
        $t = $ipComponent->getAllowedList();
        $this->assertIsArray($t);
    }

    public function testSetDeniedList()
    {
        $ipComponent = new Ip();
        $s = ['127.33.33.33', '127.33.33.34', '127.33.33.35'];
        $ipComponent->setDeniedList($s);
        $t = $ipComponent->getDeniedList();
        if ($s === $t) {
            $this->assertTrue(true);
        }
    }

    public function testSetDeniedItem()
    {
        $ipComponent = new Ip();
        $s = '127.33.33.33';
        $t = $ipComponent->setDeniedItem($s);
        if ($s === $t) {
            $this->assertTrue(true);
        }
    }

    public function testGetDeniedList()
    {
        $ipComponent = new Ip();
        $t = $ipComponent->getDeniedList();
        $this->assertIsArray($t);
    }

    public function testRemoveItem()
    {
        $ipComponent = new Ip();
        $s = ['127.33.33.33', '127.33.33.34', '127.33.33.35'];
        $ipComponent->setAllowedList($s);
        $ipComponent->removeItem('127.33.33.33');
        $t = $ipComponent->getAllowedList();
        if (! in_array('127.33.33.33', $t)) {
            $this->assertTrue(true);
        }
        if (in_array('127.33.33.34', $t)) {
            $this->assertTrue(true);
        }
        if (in_array('127.33.33.35', $t)) {
            $this->assertTrue(true);
        }
    }

    public function testDenyAll()
    {
        $ipComponent = new Ip();
        $ipComponent->denyAll();

        $reflection = new \ReflectionObject($ipComponent);
        $t = $reflection->getProperty('isDenyAll');
        $t->setAccessible(true);
  
        $this->assertEquals('isDenyAll' , $t->name);
        $this->assertTrue($t->getValue($ipComponent));
    }
}