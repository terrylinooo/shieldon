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

namespace Shieldon\FirewallTest\Panel;

class IptablesTest extends \PHPUnit\Framework\TestCase
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
