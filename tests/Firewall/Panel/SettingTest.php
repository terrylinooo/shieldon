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

class SettingTest extends \Shieldon\FirewallTest\ShieldonTestCase
{
    use RouteTestTrait;

    public function testSettingsBasic()
    {
        $this->assertOutputContainsString(
            'firewall/panel/setting/basic',
            'Basic Setting'
        );
    }

    public function testSettingsBasicSaveConfig()
    {
        $_POST = \Shieldon\FirewallTest\Mock\MockSaveConfig::get();
        $_POST['tab'] = 'daemon';
        $this->refreshRequest();

        $this->assertOutputContainsString(
            'firewall/panel/setting/basic',
            'Settings saved'
        );
    }

    public function testSettingsExclusion()
    {
        $this->assertOutputContainsString(
            'firewall/panel/setting/exclusion',
            'Exclusion'
        );

        $this->assertOutputNotContainsString(
            'firewall/panel/setting/exclusion',
            '/wp-content'
        );
    }

    public function testSettingsExclusionPostFormAddItem()
    {
        $_POST['url'] = '/wp-content';
        $_POST['action'] = 'add';
        $_POST['order'] = '';

        $this->refreshRequest();

        $this->assertOutputContainsString(
            'firewall/panel/setting/exclusion',
            '/wp-content'
        );
    }

    public function testSettingsExclusionPostFormRemoveItem()
    {
        $_POST['url'] = '/wp-content';
        $_POST['action'] = 'remove';
        $_POST['order'] = '1';

        $this->refreshRequest();

        $this->assertOutputNotContainsString(
            'firewall/panel/setting/exclusion',
            '/wp-content'
        );
    }

    public function testSettingsIpManager()
    {
        $this->assertOutputContainsString(
            'firewall/panel/setting/ipManager',
            'IP Manager'
        );
    }

    public function testSettingsIpManagerPostFormAddItem()
    {
        $_POST['ip'] = '19.86.6.4';
        $_POST['action'] = 'deny';
        $_POST['url'] = '/just-test-deny';
        $_POST['order'] = '0';

        $this->refreshRequest();

        $this->assertOutputContainsString(
            'firewall/panel/setting/ipManager',
            '/just-test-deny'
        );
    }

    public function testSettingsIpManagerPostFormAddItem2()
    {
        $_POST['ip'] = '19.86.6.5';
        $_POST['action'] = 'allow';
        $_POST['url'] = '/just-test-allow';
        $_POST['order'] = '0';

        $this->refreshRequest();

        $this->assertOutputContainsString(
            'firewall/panel/setting/ipManager',
            '/just-test-allow'
        );
    }

    public function testSettingsIpManagerPostFormRemoveItem()
    {
        $_POST['ip'] = '19.86.6.4';
        $_POST['action'] = 'remove';
        $_POST['url'] = '/just-test-deny';
        $_POST['order'] = '2';

        $this->refreshRequest();

        $this->assertOutputContainsString(
            'firewall/panel/setting/ipManager',
            '/just-test-allow'
        );

        $this->assertOutputNotContainsString(
            'firewall/panel/setting/ipManager',
            '/just-test-deny'
        );
    }

    public function testSettingsIpManagerPostFormRemoveItem2()
    {
        $_POST['ip'] = '19.86.6.4';
        $_POST['action'] = 'remove';
        $_POST['url'] = '/just-test-allow';
        $_POST['order'] = '0';

        $this->refreshRequest();

        $this->assertOutputNotContainsString(
            'firewall/panel/setting/ipManager',
            '/just-test-allow'
        );
    }

    public function testSettingsMessenger()
    {
        $this->assertOutputContainsString(
            'firewall/panel/setting/messenger',
            'Messenger'
        );

        $this->assertOutputNotContainsString(
            'firewall/panel/setting/messenger',
            'Settings saved'
        );
    }

    public function testSettingsMessengerSaveConfig()
    {
        $_POST['tab'] = 'messenger-setting';
        $_POST['messengers__sendgrid__enable'] = 'off';
        $_POST['messengers__sendgrid__config__api_key'] = 'no key lala';
        $_POST['messengers__sendgrid__config__sender'] = 'test@gmail.com';
        $_POST['messengers__sendgrid__config__recipients'] = 'test@gmail.com' . "\n" . 'test2@gmail.com';

        $this->refreshRequest();

        $this->assertOutputContainsString(
            'firewall/panel/setting/messenger',
            'Settings saved'
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
            ],
        ];

        $response = $this->getRouteResponse('firewall/panel/setting/import');

        $session = \Shieldon\Firewall\Container::get('session');
        $flashMessage = $session->get('flash_messages');

        $this->assertSame($flashMessage[0]['text'], 'JSON file imported successfully.');
        $this->assertStringContainsString('firewall/panel/setting/basic', $response->getHeaderLine('Location'));
    }

    public function testSettingsImportInvalidFormatJsonFile()
    {
        $_FILES = [
            'json_file' => [
                'name' => 'invalid_json_format.json',
                'type' => 'text/plain',
                // The content is missing a } in the end. That's invalid JSON format.
                'tmp_name' => BOOTSTRAP_DIR . '/samples/json_files/invalid_json_format.json',
                'error' => 0,
                'size' => 100000,
            ],
        ];

        $response = $this->getRouteResponse('firewall/panel/setting/import');

        $session = \Shieldon\Firewall\Container::get('session');
        $flashMessage = $session->get('flash_messages');

        $this->assertSame($flashMessage[0]['text'], 'Invalid JSON file.');
        $this->assertStringContainsString('firewall/panel/setting/basic', $response->getHeaderLine('Location'));
    }

    public function testSettingsImportInvalidSpecJsonFile()
    {
        $_FILES = [
            'json_file' => [
                'name' => 'invalid_spec.json',
                'type' => 'text/plain',
                // The content does n;t contain Shieldon formatted configuration fields.
                'tmp_name' => BOOTSTRAP_DIR . '/samples/json_files/invalid_spec.json',
                'error' => 0,
                'size' => 100000,
            ],
        ];

        $response = $this->getRouteResponse('firewall/panel/setting/import');

        $session = \Shieldon\Firewall\Container::get('session');
        $flashMessage = $session->get('flash_messages');

        $this->assertSame($flashMessage[0]['text'], 'Invalid Shieldon configuration file.');
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
