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

namespace Shieldon\FirewallTest\Middleware;

class UserAgentTest extends \PHPUnit\Framework\TestCase
{
    public function testUserAgent()
    {
        $_SERVER['HTTP_USER_AGENT'] = 'moz.com';
        reload_request();

        $firewall = new \Shieldon\Firewall\Firewall();
        $firewall->add(new \Shieldon\Firewall\Middleware\UserAgent());
        $response = $firewall->run();
        $this->assertSame($response->getStatusCode(), 400);
    }
}
