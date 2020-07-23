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

class SettingTest extends \PHPUnit\Framework\TestCase
{
    use RouteTestTrait;

    public function testSettingsBasic()
    {
        $this->assertPageOutputContainsString(
            'firewall/panel/setting/basic',
            'Basic Setting'
        );
    }

    public function testSettingsExclusion()
    {
        $this->assertPageOutputContainsString(
            'firewall/panel/setting/exclusion',
            'Exclusion'
        );
    }

    public function testSettingsIpManager()
    {
        $this->assertPageOutputContainsString(
            'firewall/panel/setting/ipManager',
            'IP Manager'
        );
    }

    public function testSettingsMessenger()
    {
        $this->assertPageOutputContainsString(
            'firewall/panel/setting/messenger',
            'Messenger'
        );
    }

    public function testSettingsImport()
    {
        $_FILES = [
            'json_file' => [
                'name' => 'firewall.json',
                'type' => 'text/plain',
                'tmp_name' => BOOTSTRAP_DIR . '/../tmp/shieldon/config.firewall.json',
                'error' => 0,
                'size' => 100000,
            ]
        ];

        $response = $this->getRouteResponse('firewall/panel/setting/import');

        $session = \Shieldon\Firewall\Utils\Container::get('session');
        $flashMessage = $session->get('flash_messages');

        $this->assertSame($flashMessage[0]['text'], 'JSON file imported successfully.');
        $this->assertStringContainsString('firewall/panel/setting/basic', $response->getHeaderLine('Location'));
    }

    public function testSettingsExport()
    {
        $response = $this->getRouteResponse('firewall/panel/setting/export');
        $this->assertSame($response->getHeaderLine('content-type'), 'text/plain');
        $this->assertStringContainsString('attachment', $response->getHeaderLine('Content-Disposition'));
        $this->assertSame($response->getHeaderLine('Expires'), '0');
        $this->assertSame($response->getHeaderLine('Cache-Control'), 'must-revalidate, post-check=0, pre-check=0');
        $this->assertSame($response->getHeaderLine('Pragma'), 'public');
    }
}