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

class PanelTest extends \Shieldon\FirewallTest\ShieldonTestCase
{
    public function testPanelLoginPage()
    {
        $firewall = new \Shieldon\Firewall\Firewall();
        $firewall->configure(BOOTSTRAP_DIR . '/../tmp/shieldon');

        $panel = new \Shieldon\Firewall\Panel();

        ob_start();
        $panel->entry('firewall/panel');
        $output = ob_get_contents();
        ob_end_clean();

        $this->assertStringContainsString('Login to Shieldon Firewall Panel', $output);
    }

    public function testPanelLoginPageDemoMode()
    {
        $firewall = new \Shieldon\Firewall\Firewall();
        $firewall->configure(BOOTSTRAP_DIR . '/../tmp/shieldon');

        $panel = new \Shieldon\Firewall\Panel();
        $panel->demo();

        ob_start();
        $panel->entry('firewall/panel');
        $output = ob_get_contents();
        ob_end_clean();

        $this->assertStringContainsString('(DEMO)', $output);
    }

    public function testGetFirewallInstance()
    {
        $panel = new \Shieldon\Firewall\Panel();

        $reflection = new \ReflectionObject($panel);
        $methodSetSessionId = $reflection->getMethod('getFirewallInstance');
        $methodSetSessionId->setAccessible(true);

        // Test exception.
        $this->expectException(\RuntimeException::class);

        $methodSetSessionId->invokeArgs($panel, []);
    }
}
