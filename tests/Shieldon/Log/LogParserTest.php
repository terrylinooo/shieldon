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

    public function testParse($type = 'today')
    {
        $shieldon = new \Shieldon\Shieldon();

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

        $logger = new ActionLogger(BOOTSTRAP_DIR . '/../tmp/shieldon', date('Ymd'));

        $data['ip'] = '127.0.0.1';
        $data['session_id'] = '2ss8ukvfpdgrec2qb6r44c2bgm';
        $data['action_code'] = $shieldon::LOG_PAGEVIEW;
        $data['timesamp'] = time();

        $logger->add($data); // 127.0.0.1 - pagview: 1

        $data['timesamp'] = time() + 1;

        $logger->add($data); // 127.0.0.1 - pagview: 2

        $data['timesamp'] = time() + 3;

        $logger->add($data); // 127.0.0.1 - pagview: 3

        $data['timesamp'] = time() + 1;

        $logger->add($data); // 127.0.0.1 - pagview: 4

        $data['timesamp'] = time() + 2;

        $logger->add($data); // 127.0.0.1 - pagview: 5

        $data['ip'] = '127.0.0.1';
        $data['session_id'] = '2ss8ukvfpdgrec2qb6r44c2bgm';
        $data['action_code'] = $shieldon::ACTION_TEMPORARILY_DENY;
        $data['timesamp'] = time() + 2;

        $logger->add($data); // 127.0.0.1 - pagview: 5, temporarily ban: 1

        $data['ip'] = '127.0.0.1';
        $data['session_id'] = '2ss8ukvfpdgrec2qb6r44c2bgm';
        $data['action_code'] = $shieldon::LOG_CAPTCHA;
        $data['timesamp'] = time() + 4;

        $logger->add($data); // 127.0.0.1 - pagview: 5, temporarily ban: 1, stuck in captcha: 1, captcha falied: 1

        $data['ip'] = '127.0.0.1';
        $data['session_id'] = '2ss8ukvfpdgrec2qb6r44c2bgm';
        $data['action_code'] = $shieldon::LOG_CAPTCHA;
        $data['timesamp'] = time() + 2;

        $logger->add($data); // 127.0.0.1 - pagview: 5, temporarily ban: 1, stuck in captcha: 2, captcha falied: 2

        $data['ip'] = '127.0.0.1';
        $data['session_id'] = '2ss8ukvfpdgrec2qb6r44c2bgm';
        $data['action_code'] = $shieldon::ACTION_UNBAN;
        $data['timesamp'] = time() + 2;

        $logger->add($data); // 127.0.0.1 - pagview: 5, temporarily ban: 1, stuck in captcha: 2, captcha falied: 2, captcha soloved: 1, unban: 1

        $data['ip'] = '127.0.0.1';
        $data['session_id'] = '2ss8ukvfpdgrec2qb6r44c2bgm';
        $data['action_code'] = $shieldon::LOG_PAGEVIEW;
        $data['timesamp'] = time() + 1; 

        $logger->add($data); // 127.0.0.1 - pagview: 6, temporarily ban: 1, stuck in captcha: 2, captcha falied: 2, captcha soloved: 1, unban: 1

        $data['ip'] = '127.0.0.2';
        $data['session_id'] = 'lo1hk46k6io3vdugamg762c6m1';
        $data['action_code'] = $shieldon::LOG_PAGEVIEW;
        $data['timesamp'] = time();

        $logger->add($data); // 127.0.0.2 - pagview: 1 (7)
        $logger->add($data); // 127.0.0.2 - pagview: 2 (8)
        $logger->add($data); // 127.0.0.2 - pagview: 3 (9)

        $data['ip'] = '127.0.0.2';
        $data['session_id'] = 'lo1hk46k6io3vdugamg762c6m1';
        $data['action_code'] = $shieldon::ACTION_TEMPORARILY_DENY;
        $data['timesamp'] = time() + 2;

        $logger->add($data); // 127.0.0.1 - pagview: 3 (9), temporarily ban: 1 (2)

        $data['ip'] = '127.0.0.2';
        $data['session_id'] = 'lo1hk46k6io3vdugamg762c6m1';
        $data['action_code'] = $shieldon::ACTION_UNBAN;
        $data['timesamp'] = time() + 4;

        $logger->add($data); // 127.0.0.1 - pagview: 3 (9), temporarily ban: 1 (2), captcha soloved: 1 (2), unban: 1

        /**
         * Let's start parsing logs.
         */

        
    }
}