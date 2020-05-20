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


class UserAgentTest extends \PHPUnit\Framework\TestCase
{
    public function testSetStrict()
    {
        $request = new \Shieldon\Mock\MockRequest();
        $request->apply();

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
        $request = new \Shieldon\Mock\MockRequest();
        $request->apply();

        $list = ['google.com', 'yahoo.com'];

        $userAgentComponent = new UserAgent();
        $userAgentComponent->setDeniedList($list);

        $deniedList = $userAgentComponent->getDeniedList();

        $this->assertSame($deniedList, $list);
    }

    public function testSetDeniedItem()
    {
        $request = new \Shieldon\Mock\MockRequest();
        $request->apply();

        $string = 'baidu.com';

        $userAgentComponent = new UserAgent();
        $userAgentComponent->setDeniedItem($string);

        $deniedList = $userAgentComponent->getDeniedList();

        if (in_array($string, $deniedList)) {
            $this->assertTrue(true);
        } else {
            $this->assertTrue(false);
        }
    }

    public function testGetDeniedList()
    {
        $request = new \Shieldon\Mock\MockRequest();
        $request->apply();

        $userAgentComponent = new UserAgent();
        $deniedList = $userAgentComponent->getDeniedList();

        $this->assertSame($deniedList, [
            'domain',
            'copyright',
            'Ahrefs',
            'roger',
            'moz.com',
            'MJ12bot',
            'findlinks',
            'Semrush',
            'archive',
        ]);
    }

    public function testRemoveItem()
    {
        $request = new \Shieldon\Mock\MockRequest();
        $request->apply();

        $userAgentComponent = new UserAgent();
        $userAgentComponent->removeItem('Ahrefs');

        $deniedList = $userAgentComponent->getDeniedList();

        if (! in_array('Ahrefs', $deniedList)) {
            $this->assertTrue(true);
        } else {
            $this->assertTrue(false);
        }
    }

    public function testIsDenied()
    {
        $request = new \Shieldon\Mock\MockRequest();
        $request->server->set('HTTP_USER_AGENT', 'Mozilla/5.0 (compatible; AhrefsBot/6.1; +http://ahrefs.com/robot/)');
        $request->apply();

        $userAgentComponent = new UserAgent();

        $result = $userAgentComponent->isDenied();
        $this->assertTrue($result);

        $request->server->remove('HTTP_USER_AGENT');
        $request->apply();

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
        $request = new \Shieldon\Mock\MockRequest();
        $request->apply();

        $userAgentComponent = new UserAgent();
        $statusCode = $userAgentComponent->getDenyStatusCode();

        $this->assertSame(84, $statusCode);
    }
}
