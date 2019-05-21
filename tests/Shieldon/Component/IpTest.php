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
        $t1 = $ipComponent->check('128.232.234.256');
        $this->assertIsArray($t1);
        $this->assertEquals(3 , count($t1));
        $this->assertEquals('deny' , $t1['status']);
        $ipComponent = null;

         // Test 2. Check denied list.
         $ipComponent = new Ip();
         $ipComponent->setDeniedIp('127.0.55.44');
         $t2 = $ipComponent->check('127.0.55.44');
         $this->assertIsArray($t2);
         $this->assertEquals(3 , count($t2));
         $this->assertEquals('deny' , $t2['status']);
         $ipComponent = null;

        // Test 3. Check  allowed list.
        $ipComponent = new Ip();
        $ipComponent->setAllowedIp('127.0.55.55');
        $t3 = $ipComponent->check('127.0.55.55');
        $this->assertIsArray($t3);
        $this->assertEquals(3 , count($t3));
        $this->assertEquals('allow' , $t3['status']);
        $ipComponent = null;

        // Test 4. Check IP is if in denied IP range.
        $ipComponent = new Ip();
        $ipComponent->setDeniedIp('127.0.55.0/16');
        $t4 = $ipComponent->check('127.0.33.1');
        $this->assertIsArray($t4);
        $this->assertEquals(3 , count($t4));
        $this->assertEquals('deny' , $t4['status']);
        $ipComponent = null;

       // Test 5. Check IP is if in allowed IP range.
       $ipComponent = new Ip();
       $ipComponent->setDeniedIp('127.0.55.0/16');
       $ipComponent->setAllowedIp('127.0.55.0/16');
       $t5 = $ipComponent->check('127.0.33.1');
       $this->assertIsArray($t5);
       $this->assertEquals(3 , count($t5));
       $this->assertEquals('allow' , $t5['status']);
       $ipComponent = null;
    }

    public function testInRange()
    {
        // Test 1. Check IP is if in D class subnet.
        $ipComponent = new Ip();
        $t1 = $ipComponent->inRange('127.0.0.44', '127.0.0.0/24');
        $this->assertEquals(true , $t1);
        $t1 = $ipComponent->inRange('127.0.250.44', '127.0.250.250/24');
        $this->assertEquals(true , $t1);
        $t1 = $ipComponent->inRange('127.0.1.44', '127.0.0.0/24');
        $this->assertEquals(false , $t1);
        $ipComponent = null;

        // Test 2. Check IP is if in C class subnet.
        $ipComponent = new Ip();
        $t1 = $ipComponent->inRange('127.0.33.33', '127.0.0.0/16');
        $this->assertEquals(true , $t1);
        $t1 = $ipComponent->inRange('127.0.33.33', '127.0.250.0/16');
        $this->assertEquals(true , $t1);
        $t1 = $ipComponent->inRange('127.1.33.33', '127.0.0.0/16');
        $this->assertEquals(false , $t1);
        $ipComponent = null;

        // Test 2. Check IP is if in B class subnet.
        $ipComponent = new Ip();
        $t1 = $ipComponent->inRange('127.33.250.33', '127.0.0.0/8');
        $this->assertEquals(true , $t1);
        $t1 = $ipComponent->inRange('127.33.33.33', '127.0.0.0/8');
        $this->assertEquals(true , $t1); 
        $t1 = $ipComponent->inRange('128.33.250.33', '127.0.0.0/8');
        $this->assertEquals(false , $t1);
        $ipComponent = null;
    }
}