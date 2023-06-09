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

namespace Shieldon\FirewallTest\Log;

use Shieldon\Firewall\Kernel\Enum;

class ActionLoggerTest extends \Shieldon\FirewallTest\ShieldonTestCase
{
    public function test__construct()
    {
        try {
            $logger = new \Shieldon\Firewall\Log\ActionLogger(BOOTSTRAP_DIR . '/../tmp/shieldon');

            $info = $logger->getCurrentLoggerInfo();

            $this->assertIsArray($info);
        } catch (\Exception $e) {
            $this->assertTrue(false);
        }

        if ($logger instanceof \Shieldon\Firewall\Log\ActionLogger) {
            $this->assertTrue(true);
        }
    }

    public function testAdd()
    {
        $kernel = new \Shieldon\Firewall\Kernel();
        $logger = new \Shieldon\Firewall\Log\ActionLogger(BOOTSTRAP_DIR . '/../tmp/shieldon/test_logs', '19890604');

        $data['ip'] = '127.0.0.1';
        $data['session_id'] = md5((string) time());
        $data['action_code'] = Enum::ACTION_TEMPORARILY_DENY;
        $data['timestamp'] = time();

        $logger->add($data);

        $data['ip'] = '127.0.0.1';
        $data['session_id'] = md5((string) time());
        $data['action_code'] = Enum::ACTION_UNBAN;
        $data['timestamp'] = time() + 4;

        $logger->add($data);

        $results = $logger->get('19890604');

        $this->assertEquals($data['ip'], $results[1]['ip']);
        $this->assertEquals($data['action_code'], $results[1]['action_code']);
        $this->assertEquals($data['timestamp'], $results[1]['timestamp']);

        $results = $logger->get('19890604', date('Ymd'));

        $logger->purgeLogs();
    }

    public function testGet()
    {
        // This method has been tested in testAdd.
    }

    public function testCheckDirectory()
    {
        $logger = new \Shieldon\Firewall\Log\ActionLogger(BOOTSTRAP_DIR . '/../tmp/shieldon');

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
