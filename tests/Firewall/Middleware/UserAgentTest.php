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

namespace Shieldon\Firewall\Middleware;

class UserAgentTest extends \PHPUnit\Framework\TestCase
{
    public function testUserAgent()
    {
        $_SERVER['HTTP_USER_AGENT'] = 'moz.com';

        $firewall = new \Shieldon\Firewall\Firewall();
        $firewall->add(new \Shieldon\Firewall\Middleware\UserAgent());
        $response = $firewall->run();
        $this->assertSame($response->getStatusCode(), 400);
    }
}
