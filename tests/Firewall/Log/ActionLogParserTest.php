<?php declare(strict_types=1);
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

namespace Shieldon\FirewallTest\Log;

use Shieldon\Firewall\Kernel\Enum;

class ActionLogParserTest extends \Shieldon\FirewallTest\ShieldonTestCase
{
    public function test__construct()
    {
        try {
            $logger = new \Shieldon\Firewall\Log\ActionLogParser(BOOTSTRAP_DIR . '/../tmp/shieldon');
        } catch (\Exception $e) {
            $this->assertTrue(false);
        }

        if ($logger instanceof \Shieldon\Firewall\Log\ActionLogParser) {
            $this->assertTrue(true);
        }
    }

    public function testParsePeriodData()
    {
        // This method has been tested in testParse
    }

    public function testGetPeriodData()
    {
        // This method has been tested in testParse
    }

    public function testGetIpData()
    {
        // This method has been tested in testParse
    }

    /**
     * This is a comprehensive test with ActionLogger and ActionLogParser.
     *
     *  ACTION_DENY = 0
     *  ACTION_ALLOW = 1
     *  ACTION_TEMPORARILY_DENY = 2
     *  ACTION_UNBAN = 9
     *  LOG_PAGEVIEW = 11
     *  LOG_LIMIT = 3
     *  LOG_BLACKLIST = 98
     *  LOG_CAPTCHA = 99
     */
    public function testParse($type = 'today')
    {
        $kernel = new \Shieldon\Firewall\Kernel();

        $testLogDir = BOOTSTRAP_DIR . '/../tmp/shieldon/log';

        $logger = new \Shieldon\Firewall\Log\ActionLogger($testLogDir);
 
        // Remove logs.
        $logger->purgeLogs();

        // Rebuild log dictory.
        $logger = new \Shieldon\Firewall\Log\ActionLogger($testLogDir, date('Ymd', $this->mockTimesamp(1, $type)));

        $data['ip'] = '127.0.0.1';
        $data['session_id'] = '2ss8ukvfpdgrec2qb6r44c2bgm';
        $data['action_code'] = Enum::LOG_PAGEVIEW;
        $data['timestamp'] = $this->mockTimesamp(1, $type);

        $logger->add($data); // 127.0.0.1 - pagview: 1

        $data['timestamp'] = $this->mockTimesamp(1, $type);

        $logger->add($data); // 127.0.0.1 - pagview: 2

        $data['timestamp'] = $this->mockTimesamp(3, $type);

        $logger->add($data); // 127.0.0.1 - pagview: 3

        $data['timestamp'] = $this->mockTimesamp(5, $type);

        $logger->add($data); // 127.0.0.1 - pagview: 4

        $data['timestamp'] = $this->mockTimesamp(7, $type);

        $logger->add($data); // 127.0.0.1 - pagview: 5

        $data['ip'] = '127.0.0.1';
        $data['session_id'] = '2ss8ukvfpdgrec2qb6r44c2bgm';
        $data['action_code'] = Enum::ACTION_TEMPORARILY_DENY;
        $data['timestamp'] = $this->mockTimesamp(9, $type);

        $logger->add($data); // 127.0.0.1 - pagview: 5, temporarily ban: 1, stuck in captcha: 1, captcha falied: 1

        $data['ip'] = '127.0.0.1';
        $data['session_id'] = '2ss8ukvfpdgrec2qb6r44c2bgm';
        $data['action_code'] = Enum::LOG_CAPTCHA;
        $data['timestamp'] = $this->mockTimesamp(11, $type);

        $logger->add($data); // 127.0.0.1 - pagview: 5, temporarily ban: 1, stuck in captcha: 2, captcha falied: 2

        $data['ip'] = '127.0.0.1';
        $data['session_id'] = '2ss8ukvfpdgrec2qb6r44c2bgm';
        $data['action_code'] = Enum::LOG_CAPTCHA;
        $data['timestamp'] = $this->mockTimesamp(13, $type);

        $logger->add($data); // 127.0.0.1 - pagview: 5, temporarily ban: 1, stuck in captcha: 3, captcha falied: 3

        $data['ip'] = '127.0.0.1';
        $data['session_id'] = '2ss8ukvfpdgrec2qb6r44c2bgm';
        $data['action_code'] = Enum::ACTION_UNBAN;
        $data['timestamp'] = $this->mockTimesamp(15, $type);

        $logger->add($data);
        // 127.0.0.1 - pagview: 5, temporarily ban: 1,
        // stuck in captcha: 3, captcha falied: 2, captcha soloved: 1, unban: 1

        $data['ip'] = '127.0.0.1';
        $data['session_id'] = '2ss8ukvfpdgrec2qb6r44c2bgm';
        $data['action_code'] = Enum::LOG_PAGEVIEW;
        $data['timestamp'] = $this->mockTimesamp(17, $type);

        $logger->add($data);
        // 127.0.0.1 - pagview: 6, temporarily ban: 1,
        // stuck in captcha: 3, captcha falied: 2, captcha soloved: 1, unban: 1

        $data['ip'] = '127.0.0.2';
        $data['session_id'] = 'lo1hk46k6io3vdugamg762c6m1';
        $data['action_code'] = Enum::LOG_PAGEVIEW;
        $data['timestamp'] = $this->mockTimesamp(1, $type);

        $logger->add($data); // 127.0.0.2 - pagview: 1 (7)
        $logger->add($data); // 127.0.0.2 - pagview: 2 (8)
        $logger->add($data); // 127.0.0.2 - pagview: 3 (9)

        $data['ip'] = '127.0.0.2';
        $data['session_id'] = 'lo1hk46k6io3vdugamg762c6m1';
        $data['action_code'] = Enum::ACTION_TEMPORARILY_DENY;
        $data['timestamp'] = $this->mockTimesamp(2, $type);

        $logger->add($data);
        // 127.0.0.2 - pagview: 3 (9), temporarily ban: 1 (2),
        // stuck in captcha: 1 (4), captcha falied: 1 (3), captcha soloved: 0 (1), unban: 0 (1)

        $data['ip'] = '127.0.0.2';
        $data['session_id'] = '2ss8ukvfpdgrec2qb6r44c2bgm';
        $data['action_code'] = Enum::LOG_CAPTCHA; // display captcha.
        $data['timestamp'] = $this->mockTimesamp(3, $type);

        $logger->add($data);
        // 127.0.0.2 - pagview: 3 (9), temporarily ban: 2 (3),
        // stuck in captcha: 2 (5), captcha falied: 2 (4), captcha soloved: 0 (1), unban: 0 (1)

        $data['ip'] = '127.0.0.2';
        $data['session_id'] = 'lo1hk46k6io3vdugamg762c6m1';
        $data['action_code'] = Enum::ACTION_UNBAN;
        $data['timestamp'] = $this->mockTimesamp(4, $type);

        $logger->add($data);
        // 127.0.0.2 - pagview: 3 (9), temporarily ban: 2 (3),
        // stuck in captcha: 2 (5), captcha falied: 1 (3), captcha soloved: 1 (2), unban: 1 (2)

        $data['ip'] = '32.10.1.2';
        $data['session_id'] = '3as8ukdfpdgred2q4c2b6r4bgm';
        $data['action_code'] = Enum::ACTION_DENY;
        $data['timestamp'] = $this->mockTimesamp(5, $type);

        $logger->add($data); // 32.10.1.2 - ban 1

        $data['ip'] = '32.10.1.2';
        $data['session_id'] = '3as8ukdfpdgred2q4c2b6r4bgm';
        $data['action_code'] = Enum::ACTION_DENY;
        $data['timestamp'] = $this->mockTimesamp(6, $type);

        $logger->add($data); // 32.10.1.2 - ban 1

        $data['ip'] = '32.10.1.2';
        $data['session_id'] = '3as8ukdfpdgred2q4c2b6r4bgm';
        $data['action_code'] = Enum::LOG_BLACKLIST;
        $data['timestamp'] = $this->mockTimesamp(6, $type);

        $logger->add($data); // 32.10.1.2 - ban 1, blacklist: 1

        $data['ip'] = '32.10.1.3';
        $data['session_id'] = '2q4c2b6r4hk43as8ukdfpdgred';
        $data['action_code'] = Enum::LOG_LIMIT;
        $data['timestamp'] = $this->mockTimesamp(6, $type);

        $logger->add($data); // 32.10.1.2 - ban 1, blacklist: 1

        /**
         * Let's start parsing logs.
         */
        $parser = new \Shieldon\Firewall\Log\ActionLogParser($testLogDir);

        $ipData = $parser->getIpData();
        $this->assertSame($ipData, []);

        $parser->prepare($type);

        //$x = $parser->getIpData($type);
        //die(json_encode($x));

        $ipData = $parser->getParsedIpData('127.0.0.1');

        $this->assertSame(6, $ipData['pageview_count']);
        $this->assertSame(3, $ipData['captcha_count']);
        $this->assertSame(2, $ipData['captcha_failure_count']);
        $this->assertSame(1, $ipData['captcha_success_count']);
        $this->assertSame(33, $ipData['captcha_percentageage']);
        $this->assertSame(33, $ipData['captcha_success_percentage']);

        $periodData = $parser->getParsedPeriodData();

        switch ($type) {
            case 'yesterday':
            case 'past_seven_days':
            case 'this_month':
            case 'last_month':
            case 'past_seven_hours':
                $this->assertSame(2, $periodData['captcha_success_count']);
                $this->assertSame(3, $periodData['captcha_failure_count']);
                $this->assertSame(5, $periodData['captcha_count']);
                $this->assertSame(9, $periodData['pageview_count']);
                $this->assertSame(36, $periodData['captcha_percentageage']);
                $this->assertSame(1, $periodData['blacklist_count']);
                $this->assertSame(1, $periodData['session_limit_count']);
                break;

            case 'today':
                $this->assertSame(
                    '5,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0',
                    $periodData['captcha_chart_string']
                );
                $this->assertSame(
                    '9,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0',
                    $periodData['pageview_chart_string']
                );
                $this->assertSame(2, $periodData['captcha_success_count']);
                $this->assertSame(3, $periodData['captcha_failure_count']);
                $this->assertSame(5, $periodData['captcha_count']);
                $this->assertSame(9, $periodData['pageview_count']);
                $this->assertSame(36, $periodData['captcha_percentageage']);
                $this->assertSame(1, $periodData['blacklist_count']);
                $this->assertSame(1, $periodData['session_limit_count']);
                $this->assertSame(
                    '2,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0',
                    $periodData['captcha_success_chart_string']
                );
                $this->assertSame(
                    '3,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0',
                    $periodData['captcha_failure_chart_string']
                );
                $this->assertSame(
                    // phpcs:ignore
                    "'12:00 am','01:00 am','02:00 am','03:00 am','04:00 am','05:00 am','06:00 am','07:00 am','08:00 am','09:00 am','10:00 am','11:00 am','12:00 pm','01:00 pm','02:00 pm','03:00 pm','04:00 pm','05:00 pm','06:00 pm','07:00 pm','08:00 pm','09:00 pm','10:00 pm','11:00 pm'",
                    $periodData['label_chart_string']
                );
                break;

            default:
        }
    }

    public function testParse_pastSevenHours()
    {
        $this->testParse('past_seven_hours');
    }

    public function testParse_yesterday()
    {
        $this->testParse('yesterday');
    }

    public function testParse_lastMonth()
    {
        $this->testParse('last_month');
    }

    public function testParse_thisMonth()
    {
        $this->testParse('this_month');
    }

    public function testParse_pastSevenDays()
    {
        $this->testParse('past_seven_days');
    }

    public function testParse_pastNumDays()
    {
        $this->testParse('past_14_days');
    }

    public function testParse_randomTypeString()
    {
        $this->testParse('random_type_string');
    }

    public function testGetParsedIpDataEmpty()
    {
        $parser = new \Shieldon\Firewall\Log\ActionLogParser(BOOTSTRAP_DIR . '/../tmp/shieldon');
        $ipData = $parser->getParsedIpData();

        $this->assertSame($ipData, []);
    }

    public function testGetPeriodDataEmpty()
    {
        $parser = new \Shieldon\Firewall\Log\ActionLogParser(BOOTSTRAP_DIR . '/../tmp/shieldon');
        $periodData = $parser->getPeriodData();

        $this->assertSame($periodData, []);
    }

    private function mockTimesamp($unit = 1, $type = 'today')
    {
        switch ($type) {
            // The startDate is supposed to be the same as parsePeriodData in ActionLogParser.

            case 'yesterday':
                // Set start date and end date.
                $startDate = date('Ymd', strtotime('yesterday'));
                break;

            case 'past_seven_days':
                $startDate = date('Ymd', strtotime('-7 days'));
                break;

            case 'this_month':
                $startDate = date('Ym') . '01';
                break;

            case 'last_month':
                $startDate = date('Ym', strtotime('-1 month')) . '01';
                break;

            case 'past_seven_hours':
                return strtotime('-7 hours') + $unit;

            case 'today':
                $startDate = date('Ymd');
                break;

            default:
                if (preg_match('/past_([0-9]+)_days/', $type, $matches)) {
                    $dayCount = $matches[1];
                    $startDate = date('Ymd', strtotime('-' . $dayCount . ' days'));
                } else {
                    $startDate = date('Ymd');
                }
            // endswitch;
        }
        
        $baseTimesamp = strtotime($startDate) + $unit;
    
        return $baseTimesamp;
    }

    public function testGetDirectory()
    {
        $testLogDir = BOOTSTRAP_DIR . '/../tmp/shieldon/log';
        $parser = new \Shieldon\Firewall\Log\ActionLogParser($testLogDir);

        $dir = $parser->getDirectory();

        $this->assertSame($dir, $testLogDir);
    }
}
