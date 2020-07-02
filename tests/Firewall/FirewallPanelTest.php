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
    public function testFirewall()
    {
        $firewall = new \Shieldon\Firewall\Firewall();
        $firewall->configure(BOOTSTRAP_DIR . '/../tmp/shieldon');

        $_SERVER['PHP_AUTH_USER'] = 'shieldon_user';
        $_SERVER['PHP_AUTH_PW'] = 'shieldon_pass';

        $controlPanel = new Panel($firewall);
    }

    public function testShieldon()
    {
        $kernel = new \Shieldon\Firewall\Kernel();
        $logger = new \Shieldon\Firewall\Log\ActionLogger(BOOTSTRAP_DIR . '/../tmp/shieldon');
        $kernel->add($logger);

        $_SERVER['PHP_AUTH_USER'] = 'shieldon_user';
        $_SERVER['PHP_AUTH_PW'] = 'shieldon_pass';

        $controlPanel = new Panel($kernel);
    }
}