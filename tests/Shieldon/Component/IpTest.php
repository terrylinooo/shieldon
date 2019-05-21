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
        $ipComponent->setDeniedIp('127.0.55.44');
        $t = $ipComponent->check('127.0.55.44');
        $this->assertIsArray($t);
        $this->assertEquals(3 , count($t));
        $this->assertEquals('deny' , $t['status']);
        unset($ipComponent, $t);

        // Test 3. Check  allowed list.
        $ipComponent = new Ip();
        $ipComponent->setAllowedIp('127.0.55.55');
        $t = $ipComponent->check('127.0.55.55');
        $this->assertIsArray($t);
        $this->assertEquals(3 , count($t));
        $this->assertEquals('allow' , $t['status']);
        unset($ipComponent, $t);

        // Test 4. Check IP is if in denied IP range.
        $ipComponent = new Ip();
        $ipComponent->setDeniedIp('127.0.55.0/16');
        $t = $ipComponent->check('127.0.33.1');
        $this->assertIsArray($t);
        $this->assertEquals(3 , count($t));
        $this->assertEquals('deny' , $t['status']);
        unset($ipComponent, $t);

        // Test 5. Check IP is if in allowed IP range.
        $ipComponent = new Ip();
        $ipComponent->setDeniedIp('127.0.55.0/16');
        $ipComponent->setAllowedIp('127.0.55.0/16');
        $t = $ipComponent->check('127.0.33.1');
        $this->assertIsArray($t);
        $this->assertEquals(3 , count($t));
        $this->assertEquals('allow' , $t['status']);
        unset($ipComponent, $t);

        // Test 6. Test callback.
        $ipComponent = new Ip();
        $t = $ipComponent->check('127.1.1.1', function() {
            return [
                'ip' => '127.1.1.1',
                'type' => 1,
                'reason' =>  1234,
            ];
        });

        $this->assertIsArray($t);
        $this->assertEquals(4 , count($t));
        $this->assertEquals('allow' , $t['status']);
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
        unset($ipComponent, $t);

        // Test 2. Check IP is if in C class subnet.
        $ipComponent = new Ip();
        $t = $ipComponent->inRange('127.0.33.33', '127.0.0.0/16');
        $this->assertEquals(true , $t);
        $t = $ipComponent->inRange('127.0.33.33', '127.0.250.0/16');
        $this->assertEquals(true , $t);
        $t = $ipComponent->inRange('127.1.33.33', '127.0.0.0/16');
        $this->assertEquals(false , $t);
        unset($ipComponent, $t);

        // Test 2. Check IP is if in B class subnet.
        $ipComponent = new Ip();
        $t = $ipComponent->inRange('127.33.250.33', '127.0.0.0/8');
        $this->assertEquals(true , $t);
        $t = $ipComponent->inRange('127.33.33.33', '127.0.0.0/8');
        $this->assertEquals(true , $t); 
        $t = $ipComponent->inRange('128.33.250.33', '127.0.0.0/8');
        $this->assertEquals(false , $t);
        unset($ipComponent, $t);
    }

    public function testDecimalIpv6()
    {
        $ipComponent = new Ip();
        $t = $ipComponent->decimalIpv6('FE80:0000:0000:0000:0202:B3FF:FE1E:8329');
        $t = $ipComponent->_decimalIpv6('FE80:0000:0000:0000:0202:B3FF:FE1E:8329');
        $this->assertSame('338288524927261046600406220626806860202' , $t);
        $this->assertSame('338288524927261046600406220626806860202' , $t);
        unset($ipComponent, $t);
    }

    public function testSetAllowedList()
    {

    }

    public function testSetAllowedIp()
    {

    }

    public function testGetAllowedList()
    {
        
    }

    public function testSetDeniedList()
    {

    }

    public function testSetDeniedIp()
    {
        
    }

    public function testGetDeniedList()
    {
        
    }

    public function testRemoveIp()
    {
        
    }
}