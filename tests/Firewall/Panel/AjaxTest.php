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

class AjaxTest extends \Shieldon\FirewallTest\ShieldonTestCase
{
    use RouteTestTrait;

    public function testChangeLocalePage()
    {
        $_SERVER['REQUEST_URI'] = '/firewall/panel/ajax/changeLocale';

        $this->route();

        $this->expectOutputString('{"status":"success","lang_code":"en","session_lang_code":"en"}');
    }

    public function testTryMessenger()
    {
        $_SERVER['REQUEST_URI'] = '/firewall/panel/ajax/tryMessenger';

        $this->route();

        // phpcs:ignore
        $this->expectOutputString('{"status":"error","result":{"moduleName":"","postKey":"messengers____confirm_test"}}');
    }

    public function testTryMessengersTelegram()
    {
        $this->expectOutputString(
            $this->getMessengerModulesTestExpectedString('telegram')
        );
    }

    public function testTryMessengersLineNotify()
    {
        $this->expectOutputString(
            $this->getMessengerModulesTestExpectedString('line-notify')
        );
    }

    public function testTryMessengersSlack()
    {
        $this->expectOutputString(
            $this->getMessengerModulesTestExpectedString('slack')
        );
    }

    public function testTryMessengersSlackhook()
    {
        $this->expectOutputString(
            $this->getMessengerModulesTestExpectedString('slack-webhook')
        );
    }

    public function testTryMessengersRocketChat()
    {
        $this->expectOutputString(
            $this->getMessengerModulesTestExpectedString('rocket-chat')
        );
    }

    public function testTryMessengersSmtp()
    {
        $this->expectOutputString(
            $this->getMessengerModulesTestExpectedString('smtp')
        );
    }

    public function testTryMessengersNativePhpMail()
    {
        $this->assertTrue(true);

        ob_start();
        $this->getMessengerModulesTestExpectedString('native-php-mail');
        $output = ob_get_contents();
        ob_end_clean();

        // phpcs:ignore
        $this->assertStringContainsString('result":{"moduleName":"native-php-mail","postKey":"messengers__native-php-mail__confirm_test"}', $output);   
    }

    public function testTryMessengersSendgrid()
    {
        $this->expectOutputString(
            $this->getMessengerModulesTestExpectedString('sendgrid')
        );
    }

    public function testTryMessengersMailgun()
    {
        $this->expectOutputString(
            $this->getMessengerModulesTestExpectedString('mailgun')
        );
    }

    private function getMessengerModulesTestExpectedString($module, $settings = [])
    {
        $messenger['telegram'] = [
            'apiKey',
            'channel',
        ];

        $messenger['line-notify'] = [
            'accessToken',
        ];

        $messenger['slack'] = [
            'botToken',
            'channel',
        ];

        $messenger['slack-webhook'] = [
            'webhookUrl',
        ];

        $messenger['rocket-chat'] = [
            'serverUrl',
            'userId',
            'accessToken',
            'channel',
        ];

        $messenger['smtp'] = [
            'type',
            'host',
            'port',
            'user',
            'pass',
            'sender',
            'recipients',
        ];

        $messenger['native-php-mail'] = [
            'sender',
            'recipients',
        ];

        $messenger['sendgrid'] = [
            'apiKey',
            'sender',
            'recipients',
        ];

        $messenger['mailgun'] = [
            'apiKey',
            'domain',
            'sender',
            'recipients',
        ];

        $fields = $messenger[$module];

        $_SERVER['REQUEST_URI'] = '/firewall/panel/ajax/tryMessenger';
        $_GET['module'] = $module;

        foreach ($fields as $field) {
            if ($field === 'host') {
                $_GET[$field] = 'smtp.gmail.com';
            } elseif ($field === 'sender') {
                $_GET[$field] = 'no-reply@gmail.com';
            } elseif ($field === 'recipients') {
                $_GET[$field] = 'no-reply@gmail.com';
            } elseif ($field === 'type') {
                $_GET[$field] = 'tls';
            } elseif ($field === 'port') {
                $_GET[$field] = 443;
            } else {
                $_GET[$field] = 'test';
            }
        }

        if (!empty($settings)) {
            foreach ($settings as $k => $setting) {
                $_GET[$k] = $setting;
            }
        }

        $this->refreshRequest();

        // phpcs:ignore
        $expectedString = '{"status":"error","result":{"moduleName":"' . $module . '","postKey":"messengers__' . $module . '__confirm_test"}}';

        $this->route();

        return $expectedString;
    }

    public function testTryMessengersSmtpInvalidSetting()
    {
        $settings = [
            'type' => 'ssl',
            'host' => 'localhost local', // invalid host - for code coverage.
            'port' => '80',
            'user' => 'test',
            'pass' => '1234',
            'sender' => 'test@gmail.com',
            'recipients' => 'test@gmail.com',
        ];

        $this->expectOutputString(
            $this->getMessengerModulesTestExpectedString('smtp', $settings)
        );
    }
}
