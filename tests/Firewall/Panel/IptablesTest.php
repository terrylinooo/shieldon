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

class IptablesTest extends \Shieldon\FirewallTest\ShieldonTestCase
{
    use RouteTestTrait;

    public function testIptablesManager()
    {
        $this->assertPageOutputContainsString(
            'firewall/panel/iptables/ip4',
            'Iptables Manager (IPv4)'
        );

        $this->assertPageOutputContainsString(
            'firewall/panel/iptables/ip6',
            'Iptables Manager (IPv6)'
        );
    }

    public function testIptablesStatus()
    {
        $this->assertPageOutputContainsString(
            'firewall/panel/iptables/ip4status',
            'Iptables Status (IPv4)'
        );

        $this->assertPageOutputContainsString(
            'firewall/panel/iptables/ip6status',
            'Iptables Status (IPv6)'
        );
    }
}
