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

class UserAgentTest extends \PHPUnit\Framework\TestCase
{
    public function testSetStrict()
    {
        $userAgentComponent = new UserAgent();
        $userAgentComponent->setStrict(false);

        $reflection = new \ReflectionObject($userAgentComponent);
        $t = $reflection->getProperty('strictMode');
        $t->setAccessible(true);
  
        $this->assertEquals('strictMode' , $t->name);
        $this->assertFalse($t->getValue($userAgentComponent));
    }

    public function testSetDeniedList()
    {
        $list = ['google.com', 'yahoo.com'];

        $userAgentComponent = new UserAgent();
        $userAgentComponent->setDeniedItems($list);

        $deniedList = $userAgentComponent->getDeniedItems();

        $this->assertSame($deniedList, $list);
    }

    public function testSetDeniedItem()
    {
        $string = 'baidu.com';

        $userAgentComponent = new UserAgent();
        $userAgentComponent->setDeniedItem($string);

        $deniedList = $userAgentComponent->getDeniedItems();

        if (in_array($string, $deniedList)) {
            $this->assertTrue(true);
        } else {
            $this->assertTrue(false);
        }
    }

    public function testGetDeniedList()
    {
        $userAgentComponent = new UserAgent();
        $deniedList = $userAgentComponent->getDeniedItems();

        $this->assertSame(array_values($deniedList), [
            'Ahrefs',
            'roger',
            'moz.com',
            'MJ12bot',
            'findlinks',
            'Semrush',
            'domain',
            'copyright',
            'archive',
        ]);
    }

    public function testRemoveItem()
    {
        $userAgentComponent = new UserAgent();
        $userAgentComponent->removeDeniedItem('0');

        $deniedList = $userAgentComponent->getDeniedItems();

        if (! in_array('Ahrefs', $deniedList)) {
            $this->assertTrue(true);
        } else {
            $this->assertTrue(false);
        }
    }

    public function testIsDenied()
    {
        $_SERVER['HTTP_USER_AGENT'] = 'Mozilla/5.0 (compatible; AhrefsBot/6.1; +http://ahrefs.com/robot/)';
        reload_request();

        $userAgentComponent = new UserAgent();

        $result = $userAgentComponent->isDenied();
        $this->assertTrue($result);

        $_SERVER['HTTP_USER_AGENT'] = '';
        reload_request();

        $userAgentComponent = new UserAgent();
        $userAgentComponent->setStrict(true);
        $result = $userAgentComponent->isDenied();
        $this->assertTrue($result);

        $reflection = new \ReflectionObject($userAgentComponent);
        $t = $reflection->getProperty('userAgent');
        $t->setAccessible(true);
  
        if ($t->getValue($userAgentComponent) === '') {
            $this->assertTrue(true);
        } else {
            $this->assertTrue(false);
        }
    }

    public function testGetDenyStatusCode()
    {
        $userAgentComponent = new UserAgent();
        $statusCode = $userAgentComponent->getDenyStatusCode();

        $this->assertSame(84, $statusCode);
    }
}
