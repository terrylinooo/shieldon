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

namespace Shieldon\FirewallTest\Panel;

class AssetTest extends \Shieldon\FirewallTest\ShieldonTestCase
{
    use RouteTestTrait;

    public function testCss()
    {
        $_SERVER['REQUEST_URI'] = 'firewall/panel/asset/css';

        $response = $this->route(false);

        $this->assertTrue(($response instanceof \Psr\Http\Message\ResponseInterface));
        $this->assertSame('text/css; charset=UTF-8', $response->getHeaderLine('Content-Type'));
    }

    public function testJs()
    {
        $_SERVER['REQUEST_URI'] = '/firewall/panel/asset/js';

        $response = $this->route(false);

        $this->assertTrue(($response instanceof \Psr\Http\Message\ResponseInterface));
        $this->assertSame('text/javascript; charset=UTF-8', $response->getHeaderLine('Content-Type'));
    }

    public function testFavicon()
    {
        $_SERVER['REQUEST_URI'] = '/firewall/panel/asset/favicon';

        $response = $this->route(false);

        $this->assertTrue(($response instanceof \Psr\Http\Message\ResponseInterface));
        $this->assertSame('image/x-icon', $response->getHeaderLine('Content-Type'));
    }

    public function testLogo()
    {
        $_SERVER['REQUEST_URI'] = '/firewall/panel/asset/logo';

        $response = $this->route(false);

        $this->assertTrue(($response instanceof \Psr\Http\Message\ResponseInterface));
        $this->assertSame('image/png', $response->getHeaderLine('Content-Type'));
    }
}
