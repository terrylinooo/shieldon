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

if (!defined('SHIELDON_PANEL_BASE')) {
    define('SHIELDON_PANEL_BASE', '/firewall/panel');
}

class BaseControllerTest extends \Shieldon\FirewallTest\ShieldonTestCase
{
    public function testInitialWithoutImplementFirewall()
    {
        $this->expectException(\RuntimeException::class);

        // Throws: The Firewall instance should be initialized first.
        new \Shieldon\Firewall\Panel\BaseController();
    }

    public function testInitialWithActionLogger()
    {
        $_SESSION['flash_messages'] = [
            [
                'type' => 'success',
                'text' => 'This is a flash message.',
                'class' => 'success',
            ],
        ];

        $this->mockUserSession('flash_messages', $_SESSION['flash_messages']);

        $this->mockSession();

        $firewall = new \Shieldon\Firewall\Firewall();
        $firewall->setConfig('loggers.action.config.directory_path', BOOTSTRAP_DIR . '/../tmp/shieldon/logs');
        $baseController = new \Shieldon\Firewall\Panel\BaseController();

        // loadViewPart
        $reflection = new \ReflectionObject($baseController);
        $method = $reflection->getMethod('loadViewPart');
        $method->setAccessible(true);

        ob_start();
        $method->invokeArgs($baseController, ['panel/setting/tab', ['test']]);
        $output = ob_get_contents();
        ob_end_clean();

        $this->assertStringContainsString('Daemon', $output);

        // pushMessage
        $method = $reflection->getMethod('pushMessage');
        $method->setAccessible(true);
        $method->invokeArgs($baseController, ['error', 'This is an error message.']);

        $t = $reflection->getProperty('messages');
        $t->setAccessible(true);
        $messages = $t->getValue($baseController);

        $this->assertEquals($messages, [
            [
                'type' => 'success',
                'text' => 'This is a flash message.',
                'class' => 'success',
            ],
            [
                'type' => 'error',
                'text' => 'This is an error message.',
                'class' => 'danger',
            ],
        ]);
    }

    public function testCsrfFiels()
    {
        $firewall = new \Shieldon\Firewall\Firewall();
        $panel = new \Shieldon\Firewall\Panel();
        $panel->csrf(['test' => '1234']);

        ob_start();
        echo $panel->fieldCsrf();
        $output = ob_get_contents();
        ob_end_clean();

        $this->assertSame($output, '<input type="hidden" name="test" value="1234" id="csrf-field">');
    }

    public function testSaveConfigDemoMode()
    {
        $this->mockSession();
        
        $firewall = new \Shieldon\Firewall\Firewall();
        $baseController = new \Shieldon\Firewall\Panel\BaseController();
        $baseController->demo();

        $reflection = new \ReflectionObject($baseController);
        $method = $reflection->getMethod('saveConfig');
        $method->setAccessible(true);
        $method->invokeArgs($baseController, []);
    }

    public function testdUnderscoreDemoModeForHiddenField()
    {
        $this->mockSession();

        $firewall = new \Shieldon\Firewall\Firewall();
        $baseController = new \Shieldon\Firewall\Panel\BaseController();
        $baseController->demo();

        $reflection = new \ReflectionObject($baseController);
        $method = $reflection->getMethod('_');
        $method->setAccessible(true);
        $method->invokeArgs($baseController, ['ip6tables.config.watching_folder']);

        $this->expectOutputString('This field cannot be viewed in demonstration mode.');
    }

    public function testSpecialMethodUnderscoreDemoMode()
    {
        $this->mockSession();

        $firewall = new \Shieldon\Firewall\Firewall();
        $baseController = new \Shieldon\Firewall\Panel\BaseController();
        $baseController->demo();

        $reflection = new \ReflectionObject($baseController);
        $method = $reflection->getMethod('_');
        $method->setAccessible(true);
        $method->invokeArgs($baseController, ['this.is.okay']);
    }

    public function testSpecialMethodUnderscore()
    {
        $this->mockSession();

        $firewall = new \Shieldon\Firewall\Firewall();
        $baseController = new \Shieldon\Firewall\Panel\BaseController();

        $reflection = new \ReflectionObject($baseController);
        $method = $reflection->getMethod('_');
        $method->setAccessible(true);
        $method->invokeArgs($baseController, ['messengers.sendgrid.config.recipients']);
    }
}
