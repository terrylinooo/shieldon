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

class SecurityTest extends \PHPUnit\Framework\TestCase
{
    use RouteTestTrait;

    public function testWebAuthentication()
    {
        $this->assertPageOutputContainsString(
            'firewall/panel/security/authentication',
            'Web Page Authentication'
        );
    }

    public function testXssProtection()
    {
        $this->assertPageOutputContainsString(
            'firewall/panel/security/xssProtection',
            'XSS Protection'
        );
    }
}
