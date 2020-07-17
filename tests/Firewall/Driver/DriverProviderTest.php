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

namespace Shieldon\FirewallTest\Driver;

class DriverProviderTest extends \PHPUnit\Framework\TestCase
{
    public function testSetChannel()
    {
        $driverProvider = new \Shieldon\Firewall\Driver\DriverProvider();
        $driverProvider->setChannel('unittest');
        $this->assertSame($driverProvider->getChannel(), 'unittest');
    }

    public function testGetChannel()
    {
        $driverProvider = new \Shieldon\Firewall\Driver\DriverProvider();
        $this->assertSame($driverProvider->getChannel(), '');
    }

    public function testParseData()
    {
        $driverProvider = new \Shieldon\Firewall\Driver\DriverProvider();
        $result = $driverProvider->parseData([], 'filter');

        $fields = [
            'ip'                 => '', 
            'session'            => '', 
            'hostname'           => '', 
            'first_time_s'       => 0,
            'first_time_m'       => 0,
            'first_time_h'       => 0,
            'first_time_d'       => 0,
            'first_time_flag'    => 0,
            'last_time'          => 0,
            'flag_js_cookie'     => 0,
            'flag_multi_session' => 0,
            'flag_empty_referer' => 0,
            'pageviews_cookie'   => 0,
            'pageviews_s'        => 0,
            'pageviews_m'        => 0,
            'pageviews_h'        => 0,
            'pageviews_d'        => 0,
        ];

        $this->assertSame($result, $fields);

        $this->assertSame([], $driverProvider->parseData([], 'rule'));
        $this->assertSame([], $driverProvider->parseData([], 'session'));
    }
}