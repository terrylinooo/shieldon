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

class RdnsTest extends \Shieldon\FirewallTest\ShieldonTestCase
{
    public function testSetStrict()
    {
        $rdnsComponent = new \Shieldon\Firewall\Component\Rdns();
        $rdnsComponent->setStrict(false);

        $reflection = new \ReflectionObject($rdnsComponent);
        $t = $reflection->getProperty('strictMode');
        $t->setAccessible(true);
  
        $this->assertEquals('strictMode', $t->name);
        $this->assertFalse($t->getValue($rdnsComponent));
    }

    public function testSetDeniedList()
    {
        $list = ['.example.com', '.hello.com'];

        $rdnsComponent = new \Shieldon\Firewall\Component\Rdns();
        $rdnsComponent->setDeniedItems($list);

        $deniedList = $rdnsComponent->getDeniedItems();

        $this->assertSame($deniedList, $list);
    }


    public function testSetDeniedItem()
    {
        $string = '.baidu.com';

        $rdnsComponent = new \Shieldon\Firewall\Component\Rdns();
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
        $rdnsComponent = new \Shieldon\Firewall\Component\Rdns();
        $deniedList = $rdnsComponent->getDeniedItems();

        $this->assertSame(
            $deniedList,
            ['unknown_1' => '.webcrawler.link'],
        );
    }

    public function testRemoveItem()
    {
        $rdnsComponent = new \Shieldon\Firewall\Component\Rdns();
        $rdnsComponent->setDeniedItem('.yahoo.com', 'yahoo');

        $deniedList = $rdnsComponent->getDeniedItems();

        $this->assertSame($deniedList, [
            'unknown_1' => '.webcrawler.link',
            'yahoo' => '.yahoo.com'
        ]);

        $rdnsComponent->removeDeniedItem('yahoo');
        $deniedList = $rdnsComponent->getDeniedItems();

        $this->assertSame(
            $deniedList,
            ['unknown_1' => '.webcrawler.link']
        );
    }

    public function testIsDenied()
    {
        $rdnsComponent = new \Shieldon\Firewall\Component\Rdns();
        $rdnsComponent->setRdns('.webcrawler.link');
        $result = $rdnsComponent->isDenied();
        $this->assertTrue($result);

        $rdnsComponent = new \Shieldon\Firewall\Component\Rdns();
        $rdnsComponent->setStrict(true);
        $rdnsComponent->setRdns('');
        $result = $rdnsComponent->isDenied();
        $this->assertTrue($result);

        $rdnsComponent = new \Shieldon\Firewall\Component\Rdns();
        $rdnsComponent->setStrict(true);
        $rdnsComponent->setRdns('crawl-66-249-66-1.googlebot.com');
        $rdnsComponent->setIp('66.249.66.1');
        $result = $rdnsComponent->isDenied();
        $this->assertFalse($result);

        // IP address and its RDNS is not matched.
        $rdnsComponent = new \Shieldon\Firewall\Component\Rdns();
        $rdnsComponent->setStrict(true);
        $rdnsComponent->setRdns('crawl-66-249-66-1.googlebot.com');
        $rdnsComponent->setIp('66.249.66.2');
        $result = $rdnsComponent->isDenied();
        $this->assertTrue($result);
    }

    public function testIsDenied_2()
    {
        // IP address and its RDNS is the same. We don't allow it.
        $rdnsComponent = new \Shieldon\Firewall\Component\Rdns();
        $rdnsComponent->setStrict(true);
        $rdnsComponent->setRdns('66.249.66.2');
        $rdnsComponent->setIp('66.249.66.2');
        $result = $rdnsComponent->isDenied();
        $this->assertTrue($result);
    }

    public function testIsDenied_3()
    {
        // RDNS is not a FQDN.
        $rdnsComponent = new \Shieldon\Firewall\Component\Rdns();
        $rdnsComponent->setStrict(true);
        $rdnsComponent->setRdns('localhost');
        $rdnsComponent->setIp('66.249.66.2');
        $result = $rdnsComponent->isDenied();
        $this->assertTrue($result);
    }

    public function testGetDenyStatusCode()
    {
        $rdnsComponent = new \Shieldon\Firewall\Component\Rdns();
        $statusCode = $rdnsComponent->getDenyStatusCode();

        $this->assertSame(82, $statusCode);
    }
}
