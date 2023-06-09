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

use function Shieldon\Firewall\get_session_instance;

class UserTest extends \Shieldon\FirewallTest\ShieldonTestCase
{
    use RouteTestTrait;

    public function testLogin()
    {
        $this->assertOutputContainsString(
            'firewall/panel/user/login',
            'Login to Shieldon Firewall Panel'
        );
    }

    public function testLoginAsAdminWithCaptcha()
    {
        $_POST['s_user'] = 'shieldon_user';
        $_POST['s_pass'] = 'shieldon_pass';
        $_SERVER['REQUEST_URI'] = '/firewall/panel/user/login';
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $this->refreshRequest();

        $this->assertOutputContainsString(
            'firewall/panel/user/login',
            'Invalid Captcha'
        );
    }

    public function testLoginAsAdmiPassCaptcha()
    {
        $_POST['s_user'] = 'shieldon_user';
        $_POST['s_pass'] = 'shieldon_pass';
        $_POST['shieldon_captcha'] = 'ok';
        $_SERVER['REQUEST_URI'] = '/firewall/panel/user/login';
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $this->refreshRequest();

        $this->getRouteResponse('firewall/panel/user/login');
        $loginStatus = get_session_instance()->get('shieldon_user_login');

        $this->assertTrue($loginStatus);
    }

    public function testLoginAsAdmiPassCaptchaWrongPassword()
    {
        $_POST['s_user'] = 'shieldon_user';
        $_POST['s_pass'] = '11111111111111111111111';
        $_POST['shieldon_captcha'] = 'ok';
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $this->refreshRequest();

        $this->assertOutputContainsString(
            'firewall/panel/user/login',
            'Invalid username or password.'
        );
    }

    public function testLogout()
    {
        $this->getRouteResponse('firewall/panel/user/logout');
        $loginStatus = get_session_instance()->get('shieldon_user_login');

        $this->assertSame($loginStatus, '');
    }

    public function testLoginAsDemo()
    {
        $_SERVER['REQUEST_URI'] = '/firewall/panel/user/login';
        $this->refreshRequest();

        $firewall = new \Shieldon\Firewall\Firewall();
        $firewall->configure(BOOTSTRAP_DIR . '/../tmp/shieldon');
        $firewall->controlPanel('firewall/panel');
        $panel = new \Shieldon\Firewall\Panel();
        $panel->demo();

        ob_start();
        $panel->entry();
        $output = ob_get_contents();
        ob_end_clean();

        $this->assertStringContainsString('Login to Shieldon Firewall Panel (DEMO)', $output);
    }

    public function testLoginAsDemoPostFormWithCaptcha()
    {
        $_POST['s_user'] = 'demo';
        $_POST['s_pass'] = 'demo';
        $_SERVER['REQUEST_URI'] = '/firewall/panel/user/login';
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $this->refreshRequest();

        $firewall = new \Shieldon\Firewall\Firewall();
        $firewall->configure(BOOTSTRAP_DIR . '/../tmp/shieldon');
        $firewall->controlPanel('firewall/panel');
        $firewall->setConfig('captcha_modules.image.enable', true);
        $firewall->setup();
        $panel = new \Shieldon\Firewall\Panel();
        $panel->demo();

        ob_start();
        $panel->entry();
        $output = ob_get_contents();
        ob_end_clean();

        $this->assertStringContainsString('Invalid Captcha', $output);
    }

    public function testLoginAsDemoPostFormPassCaptcha()
    {
        $_POST['s_user'] = 'demo';
        $_POST['s_pass'] = 'demo';
        $_POST['shieldon_captcha'] = 'ok';
        $_SERVER['REQUEST_URI'] = '/firewall/panel/user/login';
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $this->refreshRequest();

        $firewall = new \Shieldon\Firewall\Firewall();
        $firewall->configure(BOOTSTRAP_DIR . '/../tmp/shieldon');
        $firewall->controlPanel('firewall/panel');
        $firewall->getKernel()->disableCaptcha();
        $firewall->setConfig('captcha_modules.recaptcha.enable', false);
        $firewall->setConfig('captcha_modules.image.enable', false);
        $panel = new \Shieldon\Firewall\Panel();
        $panel->demo();

        ob_start();
        $panel->entry();
        $output = ob_get_contents();
        ob_end_clean();

        $loginStatus = get_session_instance()->get('shieldon_user_login');

        $this->assertTrue($loginStatus);
    }
}
