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

namespace Shieldon\FirewallTest;

use function Shieldon\Firewall\get_session_instance;
use function Shieldon\Firewall\get_response;

class SessionTest extends \Shieldon\FirewallTest\ShieldonTestCase
{
    public function testGetChannel()
    {
        $kernel = $this->getKernelInstance('file');
        $kernel->setChannel('test_session_get_channel');

        $session = get_session_instance();
        $channelName = $session->getChannel();

        $this->assertSame('test_session_get_channel', $channelName);
    }

    public function testAll()
    {
        $kernel = $this->getKernelInstance('file');
        $kernel->setChannel('test_session_get_channel');

        $session = get_session_instance();

        $this->assertFalse($session->has('no_such_key'));

        $session->set('foo', 'bar');
        $session->set('foo2', 'bar2');

        $this->assertTrue($session->has('foo'));

        $this->assertSame($session->get('foo'), 'bar');

        $session->remove('foo');

        $this->assertFalse($session->has('foo'));

        $session->setId('e04');

        $this->assertSame('e04', $session->getId());

        $session->clear();

        $this->assertFalse($session->has('foo2'));
    }

    public function testResetCookie()
    {
        $session = get_session_instance();

        $expiredTime = time() + 3600;
        $expires = date('D, d M Y H:i:s', $expiredTime) . ' GMT';
        $cookieName = '_shieldon';

        $session->resetCookie();

        $sessionId = $session->getId();
        $string = $cookieName . '=' . $sessionId . '; Path=/; Expires=' . $expires;

        $response = get_response();

        $this->assertSame($response->getHeaderLine('Set-Cookie'), $string);
    }

    public function testAssertInit()
    {
        $this->expectException(\RuntimeException::class);

        $session = get_session_instance();
        $session->set('foo', 'bar');
    }
}
