<?php declare(strict_types=1);
/*
 * This file is part of the Shieldon package.
 *
 * (c) Terry L. <contact@terryl.in>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Shieldon\Driver;


class DriverProviderTest extends \PHPUnit\Framework\TestCase
{
    public function testSetChannel()
    {
        $mock = $this->getMockForAbstractClass('Shieldon\Driver\DriverProvider');
        $mock->setChannel('unittest');
        $this->assertSame($mock->getChannel(), 'unittest');
    }

    public function testGetChannel()
    {
        $mock = $this->getMockForAbstractClass('Shieldon\Driver\DriverProvider');
        $this->assertSame($mock->getChannel(), '');
    }

    public function testParseData()
    {
        $mock = $this->getMockForAbstractClass('Shieldon\Driver\DriverProvider');
        $result = $mock->parseData([], 'log');

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

        $this->assertSame([], $mock->parseData([], 'rule'));
        $this->assertSame([], $mock->parseData([], 'session'));
    }
}