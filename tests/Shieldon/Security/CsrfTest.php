<?php declare(strict_types=1);
/*
 * This file is part of the Shieldon package.
 *
 * (c) Terry L. <contact@terryl.in>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Shieldon\Security;

class CsrfTest extends \PHPUnit\Framework\TestCase
{
    public function test__construct()
    {
        $csrfInstance = new Csrf();

        $reflection = new \ReflectionObject($csrfInstance);
        $t = $reflection->getProperty('hash');
        $t->setAccessible(true);
        $hash = $t->getValue($csrfInstance);

        $this->assertNotEmpty($hash);

        $gethash = $csrfInstance->getHash();

        $this->assertSame($hash, $gethash);
    }

    public function testVerify()
    {
        $_SERVER['REQUEST_METHOD'] = 'GET';
    
        $csrfInstance = new Csrf();
        $r = $csrfInstance->verify();
        $this->assertTrue($r);

        $_SERVER['REQUEST_METHOD'] = 'POST';

        $csrfInstance = new Csrf();
        $r = $csrfInstance->verify();
        $this->assertFalse($r);

        $name = $csrfInstance->getTokenName();

        $_SESSION[$name]['hash'] = 'aaa';
        $_POST[$name] = 'bbb';
        $r = $csrfInstance->verify();
        $this->assertFalse($r);

        $_SESSION[$name]['hash'] = 'hhh';
        $_POST[$name] = 'hhh';
        $r = $csrfInstance->verify();
        $this->assertTrue($r);   
    }

    public function testSetExpirationTime()
    {
        $csrfInstance = new Csrf();
        $csrfInstance->setExpirationTime(86400);

        $reflection = new \ReflectionObject($csrfInstance);
        $t = $reflection->getProperty('expire');
        $t->setAccessible(true);
        $expire = $t->getValue($csrfInstance);

        $this->assertEquals($expire, 86400);
    }

    public function testGetTokenName()
    {
        $csrfInstance = new Csrf();
        $tokenName = $csrfInstance->getTokenName();

        $this->assertEquals($tokenName, 'shieldon_csrf_token');
    }
}