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
        $this->assertPageOutputContainsString(
            'firewall/panel/security/authentication',
            'Web Page Authentication'
        );
    }

    public function testWebAuthenticationPostForm()
    {
  
        $this->assertPageOutputContainsString(
            'firewall/panel/security/authentication',
            'Web Page Authentication'
        );
    }

    public function testXssProtection()
    {
        $this->assertPageOutputContainsString(
            'firewall/panel/security/xssProtection',
            'XSS Protection'
        );
    }

    public function testXssProtectionPostForm()
    {
        $_POST['type'] = 'post';
        $_POST['variable'] = 'test_variable_name';
        $_POST['action'] = 'add';
        $_POST['order'] = '';
        $_POST['xss'] = 'page';

        $_SERVER['REQUEST_METHOD'] = 'POST';
        $this->refreshRequest();

        $this->assertPageOutputContainsString(
            'firewall/panel/security/xssProtection',
            'test_variable_name' // This setting has been added successfully.
        );
    }

    public function testXssProtectionPostForm2()
    {
        $_POST['type'] = 'post';
        $_POST['variable'] = 'test_variable_name';
        $_POST['action'] = 'remove';
        $_POST['order'] = '4';
        $_POST['xss'] = 'page';

        $_SERVER['REQUEST_METHOD'] = 'POST';
        $this->refreshRequest();

        $this->assertPageOutputContainsString(
            'firewall/panel/security/xssProtection',
            'test_variable_name' // This setting has been added successfully.
        );
    }
}
