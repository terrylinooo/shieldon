<?php
/*
 * This file is part of the Shieldon package.
 *
 * (c) Terry L. <contact@terryl.in>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Shieldon\Firewall;

class PanelTest extends \PHPUnit\Framework\TestCase
{
    public function testPanelLoginPage()
    {
        $firewall = new \Shieldon\Firewall\Firewall();
        $firewall->configure(BOOTSTRAP_DIR . '/../tmp/shieldon');

        ob_start();

        $controlPanel = new \Shieldon\Firewall\Panel();
        $controlPanel->entry('firewall/panel');

        $output = ob_get_contents();
        ob_end_clean();

        if (stripos($output, 'Login to Firewall Panel')) {
            $this->assertTrue(true);
        } else {
            $this->assertTrue(false);
        }
    }
}