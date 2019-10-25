<?php declare(strict_types=1);
/*
 * This file is part of the Shieldon package.
 *
 * (c) Terry L. <contact@terryl.in>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Shieldon;

class FirewallPanelTest extends \PHPUnit\Framework\TestCase
{
    public function testFirewall()
    {
        $firewall = new \Shieldon\Firewall(BOOTSTRAP_DIR . '/../tmp/shieldon');

        $_SERVER['PHP_AUTH_USER'] = 'shieldon_user';
        $_SERVER['PHP_AUTH_PW'] = 'shieldon_pass';

        $controlPanel = new \Shieldon\FirewallPanel($firewall);
    }

    public function testShieldon()
    {
        $shieldon = new \Shieldon\Shieldon();
        $logger = new \Shieldon\Log\ActionLogger(BOOTSTRAP_DIR . '/../tmp/shieldon');
        $shieldon->setLogger($logger);

        $_SERVER['PHP_AUTH_USER'] = 'shieldon_user';
        $_SERVER['PHP_AUTH_PW'] = 'shieldon_pass';

        $controlPanel = new \Shieldon\FirewallPanel($shieldon);
    }
}