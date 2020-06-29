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
        $trustedBotComponent = new TrustedBot();
        $result = $trustedBotComponent->isDenied();

        $this->assertFalse($result);
    }

    public function testIsAllowed()
    {
        $_SERVER['HTTP_USER_AGENT'] = 'Googlebot/2.1 (+http://www.google.com/bot.html)';

        $trustedBotComponent = new TrustedBot();
        $result = $trustedBotComponent->isAllowed();
        $this->assertFalse($result);

        $trustedBotComponent = new TrustedBot();
        $trustedBotComponent->setIp('66.249.66.1', true);
        $result = $trustedBotComponent->isAllowed();
        $this->assertTrue($result);

        $trustedBotComponent->setStrict(true);
        $trustedBotComponent->setIp('101.12.19.1');
        $trustedBotComponent->setRdns('crawl-66-249-66-1.googlebot.com');
        $result = $trustedBotComponent->isAllowed();
        $this->assertFalse($result);

        $trustedBotComponent->setList([]);
        $result = $trustedBotComponent->isAllowed();
        $this->assertFalse($result);
    }

    public function testRemoveItem()
    {
        $trustedBotComponent = new TrustedBot();

        $trustedBotComponent->removeItem('google');
        $list = $trustedBotComponent->getList();

        $result = array_column($list, 'google');

        if (empty($result)) {
            $this->assertTrue(true);
        } else {
            $this->assertTrue(false);
        }
    }

    public function testAddItem()
    {
        $trustedBotComponent = new TrustedBot();

        $trustedBotComponent->addItem('acer', '.acer-euro.com');
        $list = $trustedBotComponent->getList();

        $test = $list[count($list)-1];

        $this->assertSame($test['userAgent'] , 'acer');
        $this->assertSame($test['rdns'] , '.acer-euro.com');
       
    }

    public function testAddList()
    {
        $trustedBotComponent = new TrustedBot();
        $trustedBotComponent->setList([]);
        $trustedBotComponent->addList([
            ['userAgent' => 'hk', 'rdns' => 'free'],
            ['userAgent' => 'tw', 'rdns' => 'free'],
        ]);

        $reflection = new \ReflectionObject($trustedBotComponent);
        $t = $reflection->getProperty('trustedBotList');
        $t->setAccessible(true);

        $v = $t->getValue($trustedBotComponent);

        $testArr = [
            ['userAgent' => 'hk', 'rdns' => 'free'],
            ['userAgent' => 'tw', 'rdns' => 'free'],
        ];

        $this->assertSame($testArr, $v);
    }

    public function testIsGoogle()
    {
        $trustedBotComponent = new TrustedBot();
        $trustedBotComponent->setIp('66.249.66.1', true);

        if ($trustedBotComponent->isGoogle()) {
            $this->assertTrue(true);
        } else {
            $this->assertTrue(false);
        }

        $trustedBotComponent->setRdns('UNKNOWN-8-12-144-X.yahoo.com');

        if (! $trustedBotComponent->isGoogle()) {
            $this->assertFalse(false);
        } else {
            $this->assertFalse(true);
        }
    }

    public function testIsYahoo()
    {
        $trustedBotComponent = new TrustedBot();
        $trustedBotComponent->setIp('8.12.144.1', true);
        if ($trustedBotComponent->isYahoo()) {
            $this->assertTrue(true);
        } else {
            $this->assertTrue(false);
        }

        $trustedBotComponent->setRdns('msnbot-40-77-169-1.search.msn.com');

        if (! $trustedBotComponent->isYahoo()) {
            $this->assertFalse(false);
        } else {
            $this->assertFalse(true);
        }
    }

    public function testIsBing()
    {
        $trustedBotComponent = new TrustedBot();
        $trustedBotComponent->setIp('40.77.169.1', true);

        if ($trustedBotComponent->isBing()) {
            $this->assertTrue(true);
        } else {
            $this->assertTrue(false);
        }

        $trustedBotComponent->setRdns('crawl-66-249-66-1.googlebot.com');

        if (! $trustedBotComponent->isBing()) {
            $this->assertFalse(false);
        } else {
            $this->assertFalse(true);
        }
    }

    /**
     * Situation 1: Check fake googlebot.
     */
    public function testFakeGoogleBot_1()
    {
        $_SERVER['HTTP_USER_AGENT'] = 'Googlebot/2.1 (+http://www.google.com/bot.html)';
    
        $trustedBotComponent = new TrustedBot();
        $trustedBotComponent->setIp('111.111.111.111', false);
        $trustedBotComponent->setRdns('crawl-66-249-66-1.googlebot.com.fakedomain.com');

        $result = $trustedBotComponent->isAllowed();

        $this->assertFalse($result);

        $isFakeGooglebot = $trustedBotComponent->isFakeRobot();

        $this->assertTrue($isFakeGooglebot);
    }

    /**
     * Situation 2: Disable checking fake googlebot.
     */
    public function testFakeGoogleBot_2()
    {
        $_SERVER['HTTP_USER_AGENT'] = 'Googlebot/2.1 (+http://www.google.com/bot.html)';
    
        $trustedBotComponent = new TrustedBot();
        $trustedBotComponent->setIp('111.111.111.111', false);
        $trustedBotComponent->setRdns('crawl-66-249-66-1.googlebot.com.fakedomain.com');

        $reflection = new \ReflectionObject($trustedBotComponent);
        $t = $reflection->getProperty('checkFakeRdns');
        $t->setAccessible(true);

        // Disable checking fake RDNS.
        $t->setValue($trustedBotComponent, false);

        $result = $trustedBotComponent->isAllowed();

        $this->assertTrue($result);

        $isFakeGooglebot = $trustedBotComponent->isFakeRobot();

        $this->assertFalse($isFakeGooglebot);
    }

    /**
     * Situation 3: Fake user-agent.
     */
    public function testFakeGoogleBot_3()
    {
        $_SERVER['HTTP_USER_AGENT'] = 'Googlebot/2.1 (+http://www.google.com/bot.html)';
    
        $trustedBotComponent = new TrustedBot();
        $trustedBotComponent->setIp('127.0.0.1', false);
        $trustedBotComponent->setRdns('localhost');

        $reflection = new \ReflectionObject($trustedBotComponent);
        $t = $reflection->getProperty('checkFakeRdns');
        $t->setAccessible(true);

        // Disable checking fake RDNS.
        $t->setValue($trustedBotComponent, false);

        $result = $trustedBotComponent->isAllowed();

        $this->assertFalse($result);

        $isFakeGooglebot = $trustedBotComponent->isFakeRobot();

        $this->assertTrue($isFakeGooglebot);
    }

    public function testGetDenyStatusCode()
    {
        $trustedBotComponent = new TrustedBot();
        $statusCode = $trustedBotComponent->getDenyStatusCode();

        $this->assertSame(85, $statusCode);
    }
}
