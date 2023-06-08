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

class HttpResolverTest extends \Shieldon\FirewallTest\ShieldonTestCase
{
    public function testHttpResolver()
    {
        $httpFactory = new \Shieldon\Firewall\HttpFactory();
        $response = $httpFactory->createResponse();
        $response = $response->withHeader('Set-Cookie', 'name=; expires=Thu, 01-Jan-1970 00:00:01 GMT; Max-Age=0');

        $stream = $response->getBody();
        $stream->write('test');
        $stream->rewind();
        $response = $response->withBody($stream);

        $httpResolver = new \Shieldon\Firewall\HttpResolver();
        
        ob_start();
        $httpResolver($response);
        $output = ob_get_contents();
        ob_end_clean();

        if (function_exists('xdebug_get_headers')) {
            $this->assertContains(
                'Set-Cookie: name=; expires=Thu, 01-Jan-1970 00:00:01 GMT; Max-Age=0',
                xdebug_get_headers()
            );
        } else {
            $this->console('function "xdebug_get_headers()" is needed to test the header output.', 'notice');
        }

        $this->assertStringContainsString('test', $output);
    }
}
