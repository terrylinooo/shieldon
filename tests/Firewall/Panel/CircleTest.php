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

class CircleTest extends \Shieldon\FirewallTest\ShieldonTestCase
{
    use RouteTestTrait;

    public function testFilterTable()
    {
        $this->assertPageOutputContainsString(
            'firewall/panel/circle/filter',
            'Data Circle - Filter Table'
        );
    }

    public function testRuleTableFormSubmit()
    {
        $_POST['ip'] = '127.0.1.127';
        $_POST['action'] = 'permanently_ban';
        $this->refreshRequest();

        $this->assertPageOutputContainsString(
            'firewall/panel/circle/rule',
            '127.0.1.127'
        );

        $_POST['ip'] = '127.0.1.127';
        $_POST['action'] = 'remove';
        $this->refreshRequest();

        $this->assertPageOutputNotContainsString(
            'firewall/panel/circle/rule',
            '127.0.1.127'
        );
    }

    public function testSessionTable()
    {
        $this->assertPageOutputContainsString(
            'firewall/panel/circle/session',
            'Data Circle - Session Table'
        );
    }
}