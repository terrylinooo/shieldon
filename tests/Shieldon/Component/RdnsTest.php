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

    public function testSetDeniedList()
    {
        $list = ['.example.com', '.hello.com'];

        $rdnsComponent = new Rdns();
        $rdnsComponent->setDeniedItems($list);

        $deniedList = $rdnsComponent->getDeniedItems();

        $this->assertSame($deniedList, $list);
    }


    public function testSetDeniedItem()
    {
        $string = '.baidu.com';

        $rdnsComponent = new Rdns();
        $rdnsComponent->setDeniedItem($string);

        $deniedList = $rdnsComponent->getDeniedItems();

        if (in_array($string, $deniedList)) {
            $this->assertTrue(true);
        } else {
            $this->assertTrue(false);
        }
    }

    public function testGetDeniedList()
    {
        $rdnsComponent = new Rdns();
        $deniedList = $rdnsComponent->getDeniedItems();

        $this->assertSame($deniedList, [
            'unknown_1' => '.webcrawler.link']
        );
    }

    public function testRemoveItem()
    {
        $rdnsComponent = new Rdns();
        $rdnsComponent->setDeniedItem('.yahoo.com', 'yahoo');

        $deniedList = $rdnsComponent->getDeniedItems();

        $this->assertSame($deniedList, [
            'unknown_1' => '.webcrawler.link',
            'yahoo' => '.yahoo.com'
        ]);

        $rdnsComponent->removeDeniedItem('yahoo');
        $deniedList = $rdnsComponent->getDeniedItems();

        $this->assertSame($deniedList, [
            'unknown_1' => '.webcrawler.link'
        ]);
    }

    public function testIsDenied()
    {
        $rdnsComponent = new Rdns();
        $rdnsComponent->setRdns('.webcrawler.link');
        $result = $rdnsComponent->isDenied();
        $this->assertTrue($result);

        $rdnsComponent = new Rdns();
        $rdnsComponent->setStrict(true);
        $rdnsComponent->setRdns('');
        $result = $rdnsComponent->isDenied();
        $this->assertTrue($result);

        $rdnsComponent = new Rdns();
        $rdnsComponent->setStrict(true);
        $rdnsComponent->setRdns('crawl-66-249-66-1.googlebot.com');
        $rdnsComponent->setIp('66.249.66.1');
        $result = $rdnsComponent->isDenied();
        $this->assertFalse($result);

        // IP address and its RDNS is not matched.
        $rdnsComponent = new Rdns();
        $rdnsComponent->setStrict(true);
        $rdnsComponent->setRdns('crawl-66-249-66-1.googlebot.com');
        $rdnsComponent->setIp('66.249.66.2');
        $result = $rdnsComponent->isDenied();
        $this->assertTrue($result);
    }

    public function testIsDenied_2()
    {
        // IP address and its RDNS is the same. We don't allow it.
        $rdnsComponent = new Rdns();
        $rdnsComponent->setStrict(true);
        $rdnsComponent->setRdns('66.249.66.2');
        $rdnsComponent->setIp('66.249.66.2');
        $result = $rdnsComponent->isDenied();
        $this->assertTrue($result);
    }

    public function testIsDenied_3()
    {
        // RDNS is not a FQDN.
        $rdnsComponent = new Rdns();
        $rdnsComponent->setStrict(true);
        $rdnsComponent->setRdns('localhost');
        $rdnsComponent->setIp('66.249.66.2');
        $result = $rdnsComponent->isDenied();
        $this->assertTrue($result);
    }

    public function testGetDenyStatusCode()
    {
        $rdnsComponent = new Rdns();
        $statusCode = $rdnsComponent->getDenyStatusCode();

        $this->assertSame(82, $statusCode);
    }
}
