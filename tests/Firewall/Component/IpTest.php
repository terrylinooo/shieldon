<?php
/**
 * This file is part of the Shieldon package.
 *
 * (c) Terry L. <contact@terryl.in>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * php version 7.1.0
 *
 * @category  Web-security
 * @package   Shieldon
 * @author    Terry Lin <contact@terryl.in>
 * @copyright 2019 terrylinooo
 * @license   https://github.com/terrylinooo/shieldon/blob/2.x/LICENSE MIT
 * @link      https://github.com/terrylinooo/shieldon
 * @see       https://shieldon.io
 */

declare(strict_types=1);

namespace Shieldon\FirewallTest\Component;

class IpTest extends \Shieldon\FirewallTest\ShieldonTestCase
{
    public function testSetStrict()
    {
        $ipComponent = new \Shieldon\Firewall\Component\Ip();
        $ipComponent->setStrict(false);

        $reflection = new \ReflectionObject($ipComponent);
        $t = $reflection->getProperty('strictMode');
        $t->setAccessible(true);
  
        $this->assertEquals('strictMode', $t->name);
        $this->assertFalse($t->getValue($ipComponent));
    }

    public function testCheck()
    {
        // Test 1. Check incorrect IP address.
        $ipComponent = new \Shieldon\Firewall\Component\Ip();
        $t = $ipComponent->check('128.232.234.256');
        $this->assertIsArray($t);
        $this->assertEquals(3, count($t));
        $this->assertEquals('deny', $t['status']);
        unset($ipComponent, $t);

        // Test 2. Check denied list.
        $ipComponent = new \Shieldon\Firewall\Component\Ip();
        $ipComponent->setDeniedItem('127.0.55.44');
        $t = $ipComponent->check('127.0.55.44');

        $this->assertIsArray($t);
        $this->assertEquals(3, count($t));
        $this->assertEquals('deny', $t['status']);
        unset($ipComponent, $t);

        // Test 3. Check  allowed list.
        $ipComponent = new \Shieldon\Firewall\Component\Ip();
        $ipComponent->setAllowedItem('39.9.197.241');
        $t = $ipComponent->check('39.9.197.241');

        $this->assertIsArray($t);
        $this->assertEquals(3, count($t));
        $this->assertEquals('allow', $t['status']);
        unset($ipComponent, $t);

        // Test 4. Check IP is if in denied IP range.
        $ipComponent = new \Shieldon\Firewall\Component\Ip();
        $ipComponent->setDeniedItem('127.0.55.0/16');
        $t = $ipComponent->check('127.0.33.1');
        $this->assertIsArray($t);
        $this->assertEquals(3, count($t));
        $this->assertEquals('deny', $t['status']);
        unset($ipComponent, $t);

        // Test 5. Check IP is if in allowed IP range.
        $ipComponent = new \Shieldon\Firewall\Component\Ip();
        $ipComponent->setDeniedItem('127.0.55.0/16');
        $ipComponent->setAllowedItem('127.0.55.0/16');
        $t = $ipComponent->check('127.0.33.1');
        $this->assertIsArray($t);
        $this->assertEquals(3, count($t));
        $this->assertEquals('allow', $t['status']);
        unset($ipComponent, $t);

        // Test 6. Test denyAll
        $ipComponent = new \Shieldon\Firewall\Component\Ip();
        $ipComponent->denyAll();
        $t = $ipComponent->check('127.0.33.1');
        $this->assertIsArray($t);
        $this->assertEquals(3, count($t));
        $this->assertEquals('deny', $t['status']);
        unset($ipComponent, $t);
    }

    public function testInRange()
    {
        // Test 1. Check IP is if in D class subnet.
        $ipComponent = new \Shieldon\Firewall\Component\Ip();
        $t = $ipComponent->inRange('127.0.0.44', '127.0.0.0/24');
        $this->assertEquals(true, $t);
        $t = $ipComponent->inRange('127.0.250.44', '127.0.250.250/24');
        $this->assertEquals(true, $t);
        $t = $ipComponent->inRange('127.0.1.44', '127.0.0.0/24');
        $this->assertEquals(false, $t);

        // Single Ip
        $t = $ipComponent->inRange('127.0.0.1', '127.0.0.1');
        $this->assertEquals(true, $t);

        // Test 2. Check IP is if in C class subnet.
        $ipComponent = new \Shieldon\Firewall\Component\Ip();
        $t = $ipComponent->inRange('127.0.33.33', '127.0.0.0/16');
        $this->assertEquals(true, $t);
        $t = $ipComponent->inRange('127.0.33.33', '127.0.250.0/16');
        $this->assertEquals(true, $t);
        $t = $ipComponent->inRange('127.1.33.33', '127.0.0.0/16');
        $this->assertEquals(false, $t);

        // Test 3. Check IP is if in B class subnet.
        $ipComponent = new \Shieldon\Firewall\Component\Ip();
        $t = $ipComponent->inRange('127.33.250.33', '127.0.0.0/8');
        $this->assertEquals(true, $t);
        $t = $ipComponent->inRange('127.33.33.33', '127.0.0.0/8');
        $this->assertEquals(true, $t);
        $t = $ipComponent->inRange('128.33.250.33', '127.0.0.0/8');
        $this->assertEquals(false, $t);

        // Test 4. Check IPv6
        $ipComponent = new \Shieldon\Firewall\Component\Ip();
        $t = $ipComponent->inRange('2001:db8:ffff:ffff:ffff:ffff:ffff:ffff', '2001:db8::/32');
        $this->assertEquals(true, $t);

        $t = $ipComponent->inRange('2001:db8:ffff:ffff:ffff:ffff:ffff:ffff', '2001:db8::0/32');
        $this->assertEquals(false, $t);

        // Test 5. Check Invalid IP
        $ipComponent = new \Shieldon\Firewall\Component\Ip();
        $t = $ipComponent->inRange('127.0.333.33', '127.0.250.0/16');
        $this->assertEquals(false, $t);
    }

    public function testDecimalIpv6()
    {
        $ipComponent = new \Shieldon\Firewall\Component\Ip();
        $t = $ipComponent->decimalIpv6('FE80:0000:0000:0000:0202:B3FF:FE1E:8329');
        $t = $ipComponent->decimalIpv6Confirm('FE80:0000:0000:0000:0202:B3FF:FE1E:8329');
        $this->assertSame('338288524927261046600406220626806860202', $t);
        $this->assertSame('338288524927261046600406220626806860202', $t);
       
        $t = $ipComponent->decimalIpv6('2001:DB8:2de::e13');
        $t = $ipComponent->decimalIpv6Confirm('2001:DB8:2de::e13');
        $this->assertSame('42540766412169952080266446484866804624', $t);
        $this->assertSame('42540766412169952080266446484866804624', $t);
    }

    public function testSetAllowedList()
    {
        $ipComponent = new \Shieldon\Firewall\Component\Ip();
        $s = ['127.33.33.33', '127.33.33.34', '127.33.33.35'];
        $ipComponent->setAllowedItems($s);
        $t = $ipComponent->getAllowedItems();
        if ($s === $t) {
            $this->assertTrue(true);
        }
    }

    public function testSetAllowedIp()
    {
        $ipComponent = new \Shieldon\Firewall\Component\Ip();
        $s = '127.33.33.33';
        $t = $ipComponent->setAllowedItem($s);
        if ($s === $t) {
            $this->assertTrue(true);
        }
    }

    public function testGetAllowedList()
    {
        $ipComponent = new \Shieldon\Firewall\Component\Ip();
        $t = $ipComponent->getAllowedItems();
        $this->assertIsArray($t);
    }

    public function testSetDeniedList()
    {
        $ipComponent = new \Shieldon\Firewall\Component\Ip();
        $s = ['127.33.33.33', '127.33.33.34', '127.33.33.35'];
        $ipComponent->setDeniedItems($s);
        $t = $ipComponent->getDeniedItems();
        if ($s === $t) {
            $this->assertTrue(true);
        }
    }

    public function testSetDeniedItem()
    {
        $ipComponent = new \Shieldon\Firewall\Component\Ip();
        $s = '127.33.33.33';
        $t = $ipComponent->setDeniedItem($s);
        if ($s === $t) {
            $this->assertTrue(true);
        }
    }

    public function testGetDeniedList()
    {
        $ipComponent = new \Shieldon\Firewall\Component\Ip();
        $t = $ipComponent->getDeniedItems();
        $this->assertIsArray($t);
    }

    public function testRemoveItem()
    {
        $ipComponent = new \Shieldon\Firewall\Component\Ip();
        $s = ['127.33.33.33', '127.33.33.34', '127.33.33.35'];
        $ipComponent->setAllowedItems($s);
        $ipComponent->removeAllowedItem('127.33.33.33');
        $t = $ipComponent->getAllowedItems();
        if (!in_array('127.33.33.33', $t)) {
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
        $ipComponent = new \Shieldon\Firewall\Component\Ip();
        $ipComponent->denyAll();

        $reflection = new \ReflectionObject($ipComponent);
        $t = $reflection->getProperty('isDenyAll');
        $t->setAccessible(true);
  
        $this->assertEquals('isDenyAll', $t->name);
        $this->assertTrue($t->getValue($ipComponent));
    }

    public function testGetDenyStatusCode()
    {
        $ipComponent = new \Shieldon\Firewall\Component\Ip();
        $statusCode = $ipComponent->getDenyStatusCode();

        $this->assertSame(81, $statusCode);
    }
}
