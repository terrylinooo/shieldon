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

class DemoModeTraitTest extends \Shieldon\FirewallTest\ShieldonTestCase
{
    public function testAll()
    {
        $firewall = new \Shieldon\Firewall\Firewall();
        $firewall->configure(BOOTSTRAP_DIR . '/../tmp/shieldon');

        $panel = new \Shieldon\Firewall\Panel();
        $panel->demo('hello', 'world');
        $panel->csrf(['test' => '1234']);

        $reflection = new \ReflectionObject($panel);
        $t = $reflection->getProperty('demoUser');
        $t->setAccessible(true);
        $demoUser = $t->getValue($panel);

        $t = $reflection->getProperty('mode');
        $t->setAccessible(true);
        $mode = $t->getValue($panel);

        $this->assertSame($demoUser['user'], 'hello');
        $this->assertSame($demoUser['pass'], 'world');
        $this->assertSame($mode, 'demo');

        $kernel = $firewall->getKernel();
    }
}
