<?php declare(strict_types=1);
/*
 * This file is part of the Shieldon package.
 *
 * (c) Terry L. <contact@terryl.in>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Shieldon;

class ActionLoggerTest extends \PHPUnit\Framework\TestCase
{
    public function test__construct()
    {
        try {
            $logger = new ActionLogger(BOOTSTRAP_DIR . '/../tmp/shieldon');
        } catch(\Exception $e) {
            $this->assertTrue(false);
        }

        if ($logger instanceof ActionLogger) {
            $this->assertTrue(true);
        }
    }

    public function testAdd() 
    {
        $shieldon = new \Shieldon\Shieldon();
        $logger = new \Shieldon\ActionLogger(BOOTSTRAP_DIR . '/../tmp/shieldon', '19890604');

        $data['ip'] = '127.0.0.1';
        $data['session_id'] = md5((string) time());
        $data['action_code'] = $shieldon::ACTION_TEMPORARILY_DENY;
        $data['reason_code'] = $shieldon::REASON_EMPTY_REFERER;
        $data['timesamp'] = time();

        $logger->add($data);

        $data['ip'] = '127.0.0.1';
        $data['session_id'] = md5((string) time());
        $data['action_code'] = $shieldon::ACTION_UNBAN;
        $data['reason_code'] = $shieldon::REASON_MANUAL_BAN;
        $data['timesamp'] = time();

        $logger->add($data);

        $results = $logger->get('19890604');

        $logger->purgeLogs();

        $this->assertEquals($data, $results[1]);
    }

    public function testGet() {
        // This method has been tested in testAdd.
    }

    public function testCheckDirectory()
    {
        $logger = new \Shieldon\ActionLogger(BOOTSTRAP_DIR . '/../tmp/shieldon');

        $reflection = new \ReflectionObject($logger);
        $methodCreateDirectory = $reflection->getMethod('checkDirectory');
        $methodCreateDirectory->setAccessible(true);

        $result = $methodCreateDirectory->invokeArgs($logger, []);

        $this->assertTrue($result);
    }

    public function testPurgeLogs()
    {
        // This method has been tested in testAdd.
    }
}