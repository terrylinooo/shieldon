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

class ReportTest extends \PHPUnit\Framework\TestCase
{
    use RouteTestTrait;

    public function testActionLog()
    {
        $this->assertPageOutputContainsString(
            'firewall/panel/report/actionLog',
            'Action Logs'
        );
    }

    public function testOperationStatus()
    {
        $this->assertPageOutputContainsString(
            'firewall/panel/report/operation',
            'Operation Status'
        );
    }
}
