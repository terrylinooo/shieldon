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

class UserAgentTest extends \Shieldon\FirewallTest\ShieldonTestCase
{
    public function testUserAgent()
    {
        $_SERVER['HTTP_USER_AGENT'] = 'moz.com';
        $this->refreshRequest();

        $deniedList = [
            'Ahrefs',
            'roger',
            'moz.com',
            'MJ12bot',
            'findlinks',
            'Semrush',
            'domain',
            'copyright',
            'archive',
        ];

        $firewall = new \Shieldon\Firewall\Firewall();
        $firewall->add(new \Shieldon\Firewall\Middleware\UserAgent($deniedList));
        $response = $firewall->run();
        $this->assertSame($response->getStatusCode(), 400);
    }

    public function testUserAgentEmptyValue()
    {
        $_SERVER['HTTP_USER_AGENT'] = '';
        $this->refreshRequest();

        $firewall = new \Shieldon\Firewall\Firewall();
        $firewall->add(new \Shieldon\Firewall\Middleware\UserAgent());
        $response = $firewall->run();
        $this->assertSame($response->getStatusCode(), 400);
    }

    public function testUserAgentPass()
    {
        $_SERVER['HTTP_USER_AGENT'] = 'Mozilla/5.0 (Windows NT 10.0; Win64; x64)';
        $this->refreshRequest();

        $firewall = new \Shieldon\Firewall\Firewall();
        $firewall->configure(BOOTSTRAP_DIR . '/../tmp/shieldon');
        $firewall->getKernel()->driver->rebuild();
        $firewall->getKernel()->setIp('131.132.87.12');
        $firewall->getKernel()->disableFilters();
        $firewall->getKernel()->disableComponents();
        $firewall->add(new \Shieldon\Firewall\Middleware\UserAgent());
        $response = $firewall->run();
        $this->assertSame($response->getStatusCode(), 200);
    }
}
