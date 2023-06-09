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

class HomeTest extends \Shieldon\FirewallTest\ShieldonTestCase
{
    use RouteTestTrait;

    public function testOverview()
    {
        $this->assertOutputContainsString(
            'firewall/panel/home/overview',
            'Overview'
        );
    }

    public function testOverviewFormPostResetDataCircle()
    {
        $_POST['action_type'] = 'reset_data_circle';
        $this->refreshRequest();

        $this->assertOutputContainsString(
            'firewall/panel/home/overview',
            'Data circle tables have been reset.'
        );
    }

    public function testOverviewFormPostResetActionLogs()
    {
        $_POST['action_type'] = 'reset_action_logs';
        $this->refreshRequest();

        $this->assertOutputContainsString(
            'firewall/panel/home/overview',
            'Action logs have been removed.'
        );
    }

    public function testOverviewTemplateVarsOfActionLogger()
    {
        if (!defined('SHIELDON_PANEL_BASE')) {
            define('SHIELDON_PANEL_BASE', 'firewall/panel');
        }

        $firewall = new \Shieldon\Firewall\Firewall();
        $firewall->configure(BOOTSTRAP_DIR . '/../tmp/shieldon');
        $firewall->getKernel()->disableFilters();
        $firewall->getKernel()->disableComponents();
        $firewall->getKernel()->setLogger(
            new \Shieldon\Firewall\Log\ActionLogger(
                BOOTSTRAP_DIR . '/samples/action_logs'
            )
        );

        $resolver = new \Shieldon\Firewall\HttpResolver();

        $controllerClass = new \Shieldon\Firewall\Panel\Home();
        
        ob_start();
        $resolver(call_user_func([$controllerClass, 'overview']));
        $output = ob_get_contents();
        ob_end_clean();

        $this->assertStringContainsString('<strong>2020-02-03</strong>', $output);
        $this->assertStringContainsString('<strong>16</strong>', $output);
        $this->assertStringContainsString('<strong>0.41663 MB</strong>', $output);
    }
}
