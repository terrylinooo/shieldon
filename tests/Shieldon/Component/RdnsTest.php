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

    public function testSetDeniedList()
    {
        $list = ['.example.com', '.hello.com'];

        $rdnsComponent = new Rdns();
        $rdnsComponent->setDeniedList($list);

        $deniedList = $rdnsComponent->getDeniedList();

        $this->assertSame($deniedList, $list);
    }


    public function testSetDeniedItem()
    {
        $string = '.baidu.com';

        $rdnsComponent = new Rdns();
        $rdnsComponent->setDeniedItem($string);

        $deniedList = $rdnsComponent->getDeniedList();

        if (in_array($string, $deniedList)) {
            $this->assertTrue(true);
        } else {
            $this->assertTrue(false);
        }
    }

    public function testGetDeniedList()
    {
        $rdnsComponent = new Rdns();
        $deniedList = $rdnsComponent->getDeniedList();

        $this->assertSame($deniedList, ['.webcrawler.link']);
    }

    public function testRemoveItem()
    {
        $string = '.yahoo.com';

        $rdnsComponent = new Rdns();
        $rdnsComponent->setDeniedItem($string);

        $deniedList = $rdnsComponent->getDeniedList();

        $this->assertSame($deniedList, ['.webcrawler.link', '.yahoo.com']);

        $rdnsComponent->removeItem('.yahoo.com');
        $deniedList = $rdnsComponent->getDeniedList();

        $this->assertSame($deniedList, ['.webcrawler.link']);
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

        $rdnsComponent = new Rdns();
        $rdnsComponent->setStrict(true);
        $rdnsComponent->setRdns('crawl-66-249-66-1.googlebot.com');
        $rdnsComponent->setIp('66.249.66.2');
        $result = $rdnsComponent->isDenied();
        $this->assertTrue($result);
    }
}
