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

class HttpAuthenticationTest extends \Shieldon\FirewallTest\ShieldonTestCase
{
    public function testRequestHttpAuthentication()
    {
        $_SERVER['REQUEST_URI'] = '/wp-amdin';
        $this->refreshRequest();
        
        $firewall = new \Shieldon\Firewall\Firewall();
        $firewall->add(new \Shieldon\Firewall\Middleware\HttpAuthentication());
        $response = $firewall->run();
        $this->assertSame($response->getStatusCode(), 401);
    }

    public function testRequestHttpAuthenticationInvalidUserAndPassword()
    {
        $_SERVER['PHP_AUTH_USER'] = 'not-exist-user';
        $_SERVER['PHP_AUTH_PW'] = 'wrong-password';
        $_SERVER['REQUEST_URI'] = '/wp-amdin';
        $this->refreshRequest();
        
        $firewall = new \Shieldon\Firewall\Firewall();
        $firewall->add(new \Shieldon\Firewall\Middleware\HttpAuthentication());
        $response = $firewall->run();
        $this->assertSame($response->getStatusCode(), 401);
    }

    public function testRequestHttpAuthenticationSuccess()
    {
        $_SERVER['PHP_AUTH_USER'] = 'wp_shieldon_admin';
        $_SERVER['PHP_AUTH_PW'] = '$2y$10$eA/S6rH3JDkYV9nrrUvuMOTh8Q/ts33DdCerbNAUpdwtSl3Xq9cQq';
        $_SERVER['REQUEST_URI'] = '/admin/';
        $this->refreshRequest();

        $firewall = new \Shieldon\Firewall\Firewall();
        $firewall->configure(BOOTSTRAP_DIR . '/../tmp/shieldon');
        $firewall->getKernel()->driver->rebuild();
        $firewall->getKernel()->setIp('131.132.87.12');
        $firewall->getKernel()->disableFilters();
        $firewall->getKernel()->disableComponents();
        $firewall->add(new \Shieldon\Firewall\Middleware\HttpAuthentication());
        $response = $firewall->run();
        $this->assertSame($response->getStatusCode(), 200);
    }

    public function testSetHttpAuthentication()
    {
        $httpAuthentication = new \Shieldon\Firewall\Middleware\HttpAuthentication();

        $list = [
            [
                'url' => '/test-protection',
                'user' => 'testuser',
                'pass' => '$2y$10$eA/S6rH3JDkYV9nrrUvuMOTh8Q/ts33DdCerbNAUpdwtSl3Xq9cQq',
            ],
        ];

        $httpAuthentication->set($list);
    }

    public function testSetHttpAuthenticationInvalidArrayKey()
    {
        $httpAuthentication = new \Shieldon\Firewall\Middleware\HttpAuthentication();

        $list = [
            [
                'url2' => '/test-protection',
                'user' => 'testuser',
                'pass' => '$2y$10$eA/S6rH3JDkYV9nrrUvuMOTh8Q/ts33DdCerbNAUpdwtSl3Xq9cQq',
            ],
        ];

        $this->expectException(\InvalidArgumentException::class);
        $httpAuthentication->set($list);
    }
}
