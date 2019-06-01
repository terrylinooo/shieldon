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


class RobotTest extends \PHPUnit\Framework\TestCase
{
    public function testSetStrict()
    {
        $robotComponent = new Robot();
        $robotComponent->setStrict(false);

        $reflection = new \ReflectionObject($robotComponent);
        $t = $reflection->getProperty('strictMode');
        $t->setAccessible(true);
  
        $this->assertEquals('strictMode' , $t->name);
        $this->assertFalse($t->getValue($robotComponent));
    }

    public function testIsDenied()
    {
        // Test 1. By default, we ban Wayback Machine robot.
        $robotComponent = new Robot();
        $robotComponent->setUserAgent('archive.org_bot');
        $t = $robotComponent->isDenied();
        $this->assertTrue($t);
        unset($robotComponent, $t);

        // Test 2. By default, we don't ban Baidu.
        $robotComponent = new Robot();
        $robotComponent->setUserAgent('Mozilla/5.0 (compatible; Baiduspider/2.0;+http://www.baidu.com/search/spider.html)');
        $t = $robotComponent->isDenied();
        $this->assertFalse($t);
        unset($robotComponent, $t);

        // Test 3. Let's ban Baidu.
        $robotComponent = new Robot();
        $robotComponent->setUserAgent('Mozilla/5.0 (compatible; Baiduspider/2.0;+http://www.baidu.com/search/spider.html)');
        $robotComponent->setAgentList(['baidu.com'], 'deny');
        $t = $robotComponent->isDenied();
        $this->assertTrue($t);
    }

    public function testIsAllowed()
    {
       // Test 1. By default, we welcome robots from Goolge! Love you.
       $robotComponent = new Robot();
       $robotComponent->setUserAgent('AdsBot-Google (+http://www.google.com/adsbot.html)');
       $robotComponent->setIp('66.249.66.1');
       $t = $robotComponent->isAllowed();
       $this->assertTrue($t);
       unset($robotComponent, $t);

       // Test 2. By default, we don't ban Baidu, but also not put it in the allowed list.
       $robotComponent = new Robot();
       $robotComponent->setUserAgent('Mozilla/5.0 (compatible; Baiduspider/2.0;+http://www.baidu.com/search/spider.html)');
       $t = $robotComponent->isAllowed();
       $this->assertFalse($t);
       unset($robotComponent, $t);

       // Test 3. Let's allow Baidu.
       $robotComponent = new Robot();
       $robotComponent->setUserAgent('Mozilla/5.0 (compatible; Baiduspider/2.0;+http://www.baidu.com/search/spider.html)');
       $robotComponent->setAgentList(['baidu.com'], 'allow');
       $t = $robotComponent->isAllowed();
       $this->assertFalse($t);
       // Not check IP resolved hostname. (Baibu bots don't have the correspondence hostname from baidi.com)
       // It's so easy to fake user-agent, so we don't recommened you allow "baidu" in user-agent string.
       $robotComponent->setStrict(false);
       $t = $robotComponent->isAllowed();
       $this->assertTrue($t);
    }

    public function testIsRobot()
    {
        $robotComponent = new Robot();
        $robotComponent->setUserAgent('AdsBot-Google (+http://www.google.com/adsbot.html)');
        $t = $robotComponent->isRobot();
        $this->assertTrue($t);
    }

    public function testIsGoogle()
    {
        $robotComponent = new Robot();
        $robotComponent->setUserAgent('AdsBot-Google (+http://www.google.com/adsbot.html)');
        $robotComponent->setIp('66.249.66.1');
        $t = $robotComponent->isGoogle();
        $this->assertTrue($t);
        unset($robotComponent, $t);

        $robotComponent = new Robot();
        $robotComponent->setUserAgent('Mozilla/5.0 (compatible; Baiduspider/2.0;+http://www.baidu.com/search/spider.html)');
        $t = $robotComponent->isGoogle();
        $this->assertFalse($t);
    }

    public function testIsYahoo()
    {
        $robotComponent = new Robot();
        $robotComponent->setUserAgent('Mozilla/5.0 (compatible; Yahoo! Slurp; http://help.yahoo.com/help/us/ysearch/slurp)');
        $t = $robotComponent->isYahoo();
        $this->assertFalse($t);
        $robotComponent->setIp('8.12.144.1');
        $robotComponent->setRdns('UNKNOWN-8-12-144-X.yahoo.com');
        $t = $robotComponent->isYahoo();
        $this->assertTrue($t);
    }

    public function testIsBing()
    {
        $robotComponent = new Robot();
        $robotComponent->setUserAgent('Mozilla/5.0 (compatible; bingbot/2.0; +http://www.bing.com/bingbot.htm)');
        $t = $robotComponent->isBing();
        $this->assertFalse($t);
        $robotComponent->setIp('40.77.169.1');
        $robotComponent->setRdns('msnbot-40-77-169-1.search.msn.com');
        $t = $robotComponent->isBing();
        $this->assertTrue($t);
    }

    public function testIsSearchbot()
    {
        $robotComponent = new Robot();
        $robotComponent->setUserAgent('AdsBot-Google (+http://www.google.com/adsbot.html)');
        $t = $robotComponent->isSearchbot();
        $this->assertFalse($t);
        $robotComponent->setIp('66.249.66.1');
        $robotComponent->setRdns('crawl-66-249-66-1.googlebot.com');
        $t = $robotComponent->isSearchbot();
        $this->assertTrue($t);
    }
}
