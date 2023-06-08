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

class UserAgentTest extends \Shieldon\FirewallTest\ShieldonTestCase
{
    public function testSetStrict()
    {
        $userAgentComponent = new \Shieldon\Firewall\Component\UserAgent();
        $userAgentComponent->setStrict(false);

        $reflection = new \ReflectionObject($userAgentComponent);
        $t = $reflection->getProperty('strictMode');
        $t->setAccessible(true);
  
        $this->assertEquals('strictMode', $t->name);
        $this->assertFalse($t->getValue($userAgentComponent));
    }

    public function testSetDeniedList()
    {
        $list = ['google.com', 'yahoo.com'];

        $userAgentComponent = new \Shieldon\Firewall\Component\UserAgent();
        $userAgentComponent->setDeniedItems($list);

        $deniedList = $userAgentComponent->getDeniedItems();

        $this->assertSame($deniedList, $list);
    }

    public function testSetDeniedItem()
    {
        $string = 'baidu.com';

        $userAgentComponent = new \Shieldon\Firewall\Component\UserAgent();
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
        $userAgentComponent = new \Shieldon\Firewall\Component\UserAgent();
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
        $userAgentComponent = new \Shieldon\Firewall\Component\UserAgent();
        $userAgentComponent->removeDeniedItem('0');

        $deniedList = $userAgentComponent->getDeniedItems();

        if (!in_array('Ahrefs', $deniedList)) {
            $this->assertTrue(true);
        } else {
            $this->assertTrue(false);
        }
    }

    public function testIsDenied()
    {
        $_SERVER['HTTP_USER_AGENT'] = 'Mozilla/5.0 (compatible; AhrefsBot/6.1; +http://ahrefs.com/robot/)';
        $this->refreshRequest();

        $userAgentComponent = new \Shieldon\Firewall\Component\UserAgent();

        $result = $userAgentComponent->isDenied();
        $this->assertTrue($result);

        $_SERVER['HTTP_USER_AGENT'] = '';
        $this->refreshRequest();

        $userAgentComponent = new \Shieldon\Firewall\Component\UserAgent();
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
        $userAgentComponent = new \Shieldon\Firewall\Component\UserAgent();
        $statusCode = $userAgentComponent->getDenyStatusCode();

        $this->assertSame(84, $statusCode);
    }
}
