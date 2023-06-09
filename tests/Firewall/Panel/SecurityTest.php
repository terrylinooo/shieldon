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

class SecurityTest extends \Shieldon\FirewallTest\ShieldonTestCase
{
    use RouteTestTrait;

    public function testWebAuthentication()
    {
        $this->assertOutputContainsString(
            'firewall/panel/security/authentication',
            'Web Page Authentication'
        );
    }

    public function testWebAuthenticationPostFormAddItem()
    {
        $_POST['url'] = '/just-for-test';
        $_POST['user'] = 'terry';
        $_POST['pass'] = '1234';
        $_POST['order'] = '';
        $_POST['action'] = 'add';

        $_SERVER['REQUEST_METHOD'] = 'POST';
        $this->refreshRequest();

        $this->assertOutputContainsString(
            'firewall/panel/security/authentication',
            '/just-for-test' // This setting has been added successfully.
        );
    }

    public function testWebAuthenticationPostFormRemoveItem()
    {
        $_POST['url'] = '/just-for-test';
        $_POST['user'] = 'terry';
        $_POST['pass'] = '1234';
        $_POST['order'] = '1';
        $_POST['action'] = 'remove';

        $_SERVER['REQUEST_METHOD'] = 'POST';
        $this->refreshRequest();

        $this->assertOutputNotContainsString(
            'firewall/panel/security/authentication',
            '/just-for-test'
        );
    }

    public function testXssProtection()
    {
        $this->assertOutputContainsString(
            'firewall/panel/security/xssProtection',
            'XSS Protection'
        );
    }

    public function testXssProtectionPostFormSuperglobal()
    {
        $_POST['xss_protection__cookie'] = 'on';
        $_POST['xss_protection__post'] = 'off';
        $_POST['xss_protection__get'] = 'off';
        $_POST['xss_form_1'] = 'page';

        $_SERVER['REQUEST_METHOD'] = 'POST';
        $this->refreshRequest();

        $this->assertOutputContainsString(
            'firewall/panel/security/xssProtection',
            '<input type="checkbox" name="xss_protection__cookie" class="toggle-block" value="on" checked />'
        );
    }

    public function testXssProtectionPostFormAddItem()
    {
        $_POST['type'] = 'post';
        $_POST['variable'] = 'test_variable_name';
        $_POST['action'] = 'add';
        $_POST['order'] = '';
        $_POST['xss_form_2'] = 'page';

        $_SERVER['REQUEST_METHOD'] = 'POST';
        $this->refreshRequest();

        $this->assertOutputContainsString(
            'firewall/panel/security/xssProtection',
            'test_variable_name' // This setting has been added successfully.
        );
    }

    public function testXssProtectionPostFormRemoveItem()
    {
        $_POST['type'] = 'post';
        $_POST['variable'] = 'test_variable_name';
        $_POST['action'] = 'remove';
        $_POST['order'] = '3';
        $_POST['xss_form_2'] = 'page';

        $_SERVER['REQUEST_METHOD'] = 'POST';
        $this->refreshRequest();

        $this->assertOutputNotContainsString(
            'firewall/panel/security/xssProtection',
            'test_variable_name'
        );
    }
}
