<?php declare(strict_types=1);
/*
 * This file is part of the Shieldon package.
 *
 * (c) Terry L. <contact@terryl.in>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Shieldon\Log;

class LogParserTest extends \PHPUnit\Framework\TestCase
{
    public function test__construct()
    {
        try {
            $logger = new LogParser(BOOTSTRAP_DIR . '/../tmp/shieldon');
        } catch(\Exception $e) {
            $this->assertTrue(false);
        }

        if ($logger instanceof LogParser) {
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
     * This is a comprehensive test with ActionLogger and LogParser.
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
        $shieldon = new \Shieldon\Shieldon();

        $logger = new ActionLogger(BOOTSTRAP_DIR . '/../tmp/shieldon');

        $baseTimesamp = strtotime(date('Ymd'));
        
        // Remove logs.
        $logger->purgeLogs();

        // Rebuild log dictory.
        $logger = new ActionLogger(BOOTSTRAP_DIR . '/../tmp/shieldon');

        $data['ip'] = '127.0.0.1';
        $data['session_id'] = '2ss8ukvfpdgrec2qb6r44c2bgm';
        $data['action_code'] = $shieldon::LOG_PAGEVIEW;
        $data['timesamp'] = $baseTimesamp;

        $logger->add($data); // 127.0.0.1 - pagview: 1

        $data['timesamp'] = $baseTimesamp + 1;

        $logger->add($data); // 127.0.0.1 - pagview: 2

        $data['timesamp'] = $baseTimesamp + 3;

        $logger->add($data); // 127.0.0.1 - pagview: 3

        $data['timesamp'] = $baseTimesamp + 5;

        $logger->add($data); // 127.0.0.1 - pagview: 4

        $data['timesamp'] = $baseTimesamp + 7;

        $logger->add($data); // 127.0.0.1 - pagview: 5

        $data['ip'] = '127.0.0.1';
        $data['session_id'] = '2ss8ukvfpdgrec2qb6r44c2bgm';
        $data['action_code'] = $shieldon::ACTION_TEMPORARILY_DENY;
        $data['timesamp'] = $baseTimesamp + 9;

        $logger->add($data); // 127.0.0.1 - pagview: 5, temporarily ban: 1, stuck in captcha: 1, captcha falied: 1

        $data['ip'] = '127.0.0.1';
        $data['session_id'] = '2ss8ukvfpdgrec2qb6r44c2bgm';
        $data['action_code'] = $shieldon::LOG_CAPTCHA;
        $data['timesamp'] = $baseTimesamp + 11;

        $logger->add($data); // 127.0.0.1 - pagview: 5, temporarily ban: 1, stuck in captcha: 2, captcha falied: 2

        $data['ip'] = '127.0.0.1';
        $data['session_id'] = '2ss8ukvfpdgrec2qb6r44c2bgm';
        $data['action_code'] = $shieldon::LOG_CAPTCHA;
        $data['timesamp'] = $baseTimesamp + 13;

        $logger->add($data); // 127.0.0.1 - pagview: 5, temporarily ban: 1, stuck in captcha: 3, captcha falied: 3

        $data['ip'] = '127.0.0.1';
        $data['session_id'] = '2ss8ukvfpdgrec2qb6r44c2bgm';
        $data['action_code'] = $shieldon::ACTION_UNBAN;
        $data['timesamp'] = $baseTimesamp + 15;

        $logger->add($data); // 127.0.0.1 - pagview: 5, temporarily ban: 1, stuck in captcha: 3, captcha falied: 2, captcha soloved: 1, unban: 1

        $data['ip'] = '127.0.0.1';
        $data['session_id'] = '2ss8ukvfpdgrec2qb6r44c2bgm';
        $data['action_code'] = $shieldon::LOG_PAGEVIEW;
        $data['timesamp'] = $baseTimesamp + 17; 

        $logger->add($data); // 127.0.0.1 - pagview: 6, temporarily ban: 1, stuck in captcha: 3, captcha falied: 2, captcha soloved: 1, unban: 1

        $data['ip'] = '127.0.0.2';
        $data['session_id'] = 'lo1hk46k6io3vdugamg762c6m1';
        $data['action_code'] = $shieldon::LOG_PAGEVIEW;
        $data['timesamp'] = $baseTimesamp;

        $logger->add($data); // 127.0.0.2 - pagview: 1 (7)
        $logger->add($data); // 127.0.0.2 - pagview: 2 (8)
        $logger->add($data); // 127.0.0.2 - pagview: 3 (9)

        $data['ip'] = '127.0.0.2';
        $data['session_id'] = 'lo1hk46k6io3vdugamg762c6m1';
        $data['action_code'] = $shieldon::ACTION_TEMPORARILY_DENY;
        $data['timesamp'] = $baseTimesamp + 2;

        $logger->add($data); // 127.0.0.1 - pagview: 3 (9), temporarily ban: 1 (2), , stuck in captcha: 1 (4), captcha falied: 1 (3), captcha soloved: 0 (1), unban: 0 (1)

        $data['ip'] = '127.0.0.2';
        $data['session_id'] = '2ss8ukvfpdgrec2qb6r44c2bgm';
        $data['action_code'] = $shieldon::LOG_CAPTCHA; // display captcha.
        $data['timesamp'] = $baseTimesamp + 9;

        $logger->add($data); // 127.0.0.1 - pagview: 3 (9), temporarily ban: 2 (3), , stuck in captcha: 2 (5), captcha falied: 2 (4), captcha soloved: 0 (1), unban: 0 (1)

        $data['ip'] = '127.0.0.2';
        $data['session_id'] = 'lo1hk46k6io3vdugamg762c6m1';
        $data['action_code'] = $shieldon::ACTION_UNBAN;
        $data['timesamp'] = $baseTimesamp + 4;

        $logger->add($data); // 127.0.0.1 - pagview: 3 (9), temporarily ban: 2 (3), , stuck in captcha: 2 (5), captcha falied: 1 (3), captcha soloved: 1 (2), unban: 1 (2)


        /**
         * Let's start parsing logs.
         */
        $parser = new LogParser(BOOTSTRAP_DIR . '/../tmp/shieldon');

        $ipData = $parser->getIpData();
        $this->assertSame($ipData, []);

        $parser->prepare($type);

        $ipData = $parser->getParsedIpData('127.0.0.1');

        $this->assertSame(6, $ipData['pageview_count']);
        $this->assertSame(3, $ipData['captcha_count']);
        $this->assertSame(2, $ipData['captcha_failure_count']);
        $this->assertSame(1, $ipData['captcha_success_count']);
        $this->assertSame(33, $ipData['captcha_percentageage']);
        $this->assertSame(33, $ipData['captcha_success_percentage']);

        $periodData = $parser->getParsedPeriodData();

        $this->assertSame('5,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0', $periodData['captcha_chart_string']);
        $this->assertSame('9,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0', $periodData['pageview_chart_string']);
        $this->assertSame(2, $periodData['captcha_success_count']);
        $this->assertSame(3, $periodData['captcha_failure_count']);
        $this->assertSame(5, $periodData['captcha_count']);
        $this->assertSame(9, $periodData['pageview_count']);
        $this->assertSame(36, $periodData['captcha_percentageage']);
        $this->assertSame('2,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0', $periodData['captcha_success_chart_string']);
        $this->assertSame('3,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0', $periodData['captcha_failure_chart_string']);
        $this->assertSame('12:00 am,01:00 am,02:00 am,03:00 am,04:00 am,05:00 am,06:00 am,07:00 am,08:00 am,09:00 am,10:00 am,11:00 am,12:00 pm,01:00 pm,02:00 pm,03:00 pm,04:00 pm,05:00 pm,06:00 pm,07:00 pm,08:00 pm,09:00 pm,10:00 pm,11:00 pm', $periodData['label_chart_string']);
    }

    function testGetParsedIpData()
    {
        $parser = new LogParser(BOOTSTRAP_DIR . '/../tmp/shieldon');
        $ipData = $parser->getParsedIpData();

        $this->assertSame($ipData, []);
    } 

}