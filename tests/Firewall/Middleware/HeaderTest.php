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

class HeaderTest extends \Shieldon\FirewallTest\ShieldonTestCase
{
    public function testHeaderDeny()
    {
        $firewall = new \Shieldon\Firewall\Firewall();
        $firewall->add(new \Shieldon\Firewall\Middleware\Header([
            'Accept',
            'Accept-Language',
            'Accept-Encoding',
        ]));
        $response = $firewall->run();
        $this->assertSame($response->getStatusCode(), 406);
    }

    public function testHeaderAllow()
    {
        $_SERVER['HTTP_ACCEPT'] = 'text/html,application/xhtml+xml,application/xml;q=0.9';
        $_SERVER['HTTP_ACCEPT_LANGUAGE'] = 'en-US,en;q=0.9,zh-TW;q=0.8,zh;q=0.7';
        $_SERVER['HTTP_ACCEPT_ENCODING'] = 'gzip';
        $this->refreshRequest();

        $firewall = new \Shieldon\Firewall\Firewall();
        $firewall->configure(BOOTSTRAP_DIR . '/../tmp/shieldon');
        $firewall->getKernel()->driver->rebuild();
        $firewall->getKernel()->setIp('131.132.87.12');
        $firewall->getKernel()->disableFilters();
        $firewall->getKernel()->disableComponents();
        $firewall->add(new \Shieldon\Firewall\Middleware\Header());
        $response = $firewall->run();
        $this->assertSame($response->getStatusCode(), 200);
    }
}
