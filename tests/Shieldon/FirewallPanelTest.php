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

use PHPUnit\Framework\TestCase;
use Shieldon\Firewall;
use Shieldon\Shieldon;
use Shieldon\Log\ActionLogger;

class FirewallPanelTest extends TestCase
{
    public function testFirewall()
    {
        $firewall = new Firewall();
        $firewall->configure(BOOTSTRAP_DIR . '/../tmp/shieldon');

        $_SERVER['PHP_AUTH_USER'] = 'shieldon_user';
        $_SERVER['PHP_AUTH_PW'] = 'shieldon_pass';

        $controlPanel = new FirewallPanel($firewall);
    }

    public function testShieldon()
    {
        $shieldon = new Shieldon();
        $logger = new ActionLogger(BOOTSTRAP_DIR . '/../tmp/shieldon');
        $shieldon->add($logger);

        $_SERVER['PHP_AUTH_USER'] = 'shieldon_user';
        $_SERVER['PHP_AUTH_PW'] = 'shieldon_pass';

        $controlPanel = new FirewallPanel($shieldon);
    }
}