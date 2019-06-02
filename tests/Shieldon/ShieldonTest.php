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

use Shieldon\Component\ComponentProvider;

use function saveTestingFile;

class ShieldonTest extends \PHPUnit\Framework\TestCase
{
    public function test__construct()
    {
        $properties = [
            'time_unit_quota'        => ['s' => 1, 'm' => 1, 'h' => 1, 'd' => 1],
            'time_reset_limit'       => 1,
            'interval_check_referer' => 1,
            'interval_check_session' => 1,
            'limit_unusual_behavior' => ['cookie' => 1, 'session' => 1, 'referer' => 1],
            'cookie_name'            => 'unittest',
            'cookie_domain'          => 'localhost',
            'lang'                   => 'zh',
            'display_credit_link'    => false,
            'display_online_info'    => false,
            'display_lineup_info'    => false,
        ];

        $shieldon = new \Shieldon\Shieldon($properties);

        $reflection = new \ReflectionObject($shieldon);
        $t = $reflection->getProperty('properties');
        $t->setAccessible(true);
        $properties = $t->getValue($shieldon);

        $this->assertSame($properties['interval_check_session'], 1);
        $this->assertSame($properties['time_reset_limit'], 1);
        $this->assertSame($properties['limit_unusual_behavior'], ['cookie' => 1, 'session' => 1, 'referer' => 1]);
        $this->assertSame($properties['cookie_name'], 'unittest');
        $this->assertSame($properties['cookie_domain'], 'localhost');
        $this->assertSame($properties['lang'], 'zh');
        $this->assertSame($properties['display_credit_link'], false);
        $this->assertSame($properties['display_online_info'], false);
        $this->assertSame($properties['display_lineup_info'], false);
    }

    public function testDetect()
    {
        $shieldon = new \Shieldon\Shieldon();

        $dbLocation = saveTestingFile('shieldon_unittest.sqlite3');

        $pdoInstance = new \PDO('sqlite:' . $dbLocation);
        $shieldon->setDriver(new \Shieldon\Driver\SqliteDriver($pdoInstance));

        $_SERVER['HTTP_USER_AGENT'] = 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/70.0.3538.77 Safari/537.36';
        $shieldon->setComponent(new \Shieldon\Component\Ip());
        $shieldon->setComponent(new \Shieldon\Component\UserAgent());
        $shieldon->setComponent(new \Shieldon\Component\TrustedBot());
        $shieldon->setComponent(new \Shieldon\Component\Rdns());

        $shieldon->setChannel('test_shieldon_detect');
        $shieldon->driver->rebuild();

        // Test 1.
        $shieldon->setIp('141.112.175.1');

        $shieldon->setProperty('time_unit_quota', [
            's' => 2,
            'm' => 20, 
            'h' => 60, 
            'd' => 240
        ]);

        $result = [];
        for ($i = 1; $i <= 5; $i++) {
            $result[$i] = $shieldon->run();
        }

        $this->assertSame($shieldon::RESPONSE_ALLOW, $result[1]);
        $this->assertSame($shieldon::RESPONSE_ALLOW, $result[2]);
        $this->assertSame($shieldon::RESPONSE_TEMPORARILY_DENY, $result[3]);
        $this->assertSame($shieldon::RESPONSE_TEMPORARILY_DENY, $result[4]);
        $this->assertSame($shieldon::RESPONSE_TEMPORARILY_DENY, $result[5]);

        // Test 2. Reset the pageview check for specfic time unit.
        $shieldon->setIp('141.112.175.2');
        $this->assertSame($shieldon::RESPONSE_ALLOW, $shieldon->run());
        sleep(2);
        $this->assertSame($shieldon::RESPONSE_ALLOW, $shieldon->run());
        $ipDetail = $shieldon->driver->get('141.112.175.2', 'log');

        if ($ipDetail['pageviews_s'] == 0) {
            $this->assertTrue(true);
        } else {
            $this->assertTrue(false);
        }

        // Test 3. Session. Ban this IP if they reached the limit.
        $shieldon->setFilters([
            'session'   => true,
            'cookie'    => false,
            'referer'   => false,
            'frequency' => false,
        ]);

        $shieldon->setProperty('interval_check_session', 1);
        $shieldon->setProperty('limit_unusual_behavior', ['cookie' => 3, 'session' => 3, 'referer' => 3]);

        // Let's get started checking Session.
        for ($i =  0; $i < 5; $i++) {
            $shieldon->setIp('140.112.172.255');
            $shieldon->limitSession(1000, 9999);
            $reflection = new \ReflectionObject($shieldon);
            $methodSetSessionId = $reflection->getMethod('setSessionId');
            $methodSetSessionId->setAccessible(true);
            $methodSetSessionId->invokeArgs($shieldon, [md5(date('YmdHis') . mt_rand(2001, 3000))]);
            $results[$i] = $shieldon->run();
            sleep(2);
        }

        $this->assertSame($shieldon::RESPONSE_ALLOW, $results[0]);
        $this->assertSame($shieldon::RESPONSE_ALLOW, $results[1]);
        $this->assertSame($shieldon::RESPONSE_ALLOW, $results[2]);
        $this->assertSame($shieldon::RESPONSE_ALLOW, $results[3]);
        $this->assertSame($shieldon::RESPONSE_TEMPORARILY_DENY, $results[4]);

        unset($results);

        // Test 4. Referer.
        $shieldon->setFilters([
            'session'   => false,
            'cookie'    => false,
            'referer'   => true,
            'frequency' => false,
        ]);

        $shieldon->setProperty('interval_check_referer', 1);
        $shieldon->setProperty('limit_unusual_behavior', ['cookie' => 3, 'session' => 3, 'referer' => 3]);

        for ($i =  0; $i < 5; $i++) {
            $shieldon->setIp('140.112.173.1');
            $results[$i] = $shieldon->run();
            sleep(2);
        }

        $this->assertSame($shieldon::RESPONSE_ALLOW, $results[0]);
        $this->assertSame($shieldon::RESPONSE_ALLOW, $results[1]);
        $this->assertSame($shieldon::RESPONSE_ALLOW, $results[2]);
        $this->assertSame($shieldon::RESPONSE_ALLOW, $results[3]);
        $this->assertSame($shieldon::RESPONSE_TEMPORARILY_DENY, $results[4]);

        unset($results);

        // Test 5. JS Cookie

        $shieldon->setFilters([
            'session'   => false,
            'cookie'    => true,
            'referer'   => false,
            'frequency' => false,
        ]);

        $shieldon->setProperty('limit_unusual_behavior', ['cookie' => 3, 'session' => 3, 'referer' => 3]);

        for ($i =  0; $i < 5; $i++) {
            $shieldon->setIp('140.112.174.1');
            $results[$i] = $shieldon->run();
        }

        $this->assertSame($shieldon::RESPONSE_ALLOW, $results[0]);
        $this->assertSame($shieldon::RESPONSE_ALLOW, $results[1]);
        $this->assertSame($shieldon::RESPONSE_ALLOW, $results[2]);
        $this->assertSame($shieldon::RESPONSE_ALLOW, $results[3]);
        $this->assertSame($shieldon::RESPONSE_TEMPORARILY_DENY, $results[4]);

        $shieldon->setProperty('cookie_name', 'unittest');
        $_COOKIE['unittest'] = 1;

        for ($i =  0; $i < 10; $i++) {
            $shieldon->setIp('140.112.175.1');
            $results[$i] = $shieldon->run();

            if ($i >= 5) {
                $_COOKIE['unittest'] = 2;
            }
        }

        $this->assertSame($shieldon::RESPONSE_ALLOW, $results[0]);
        $this->assertSame($shieldon::RESPONSE_ALLOW, $results[1]);
        $this->assertSame($shieldon::RESPONSE_ALLOW, $results[2]);
        $this->assertSame($shieldon::RESPONSE_ALLOW, $results[3]);
        $this->assertSame($shieldon::RESPONSE_ALLOW, $results[4]);
        $this->assertSame($shieldon::RESPONSE_ALLOW, $results[5]);
        $this->assertSame($shieldon::RESPONSE_ALLOW, $results[6]);
        $this->assertSame($shieldon::RESPONSE_ALLOW, $results[7]);
        $this->assertSame($shieldon::RESPONSE_TEMPORARILY_DENY, $results[8]);
        $this->assertSame($shieldon::RESPONSE_TEMPORARILY_DENY, $results[9]);

        // Test 6. Reset the flagged factor check.
        $shieldon->setFilters([
            'session'   => false,
            'cookie'    => false,
            'referer'   => true,
            'frequency' => false,
        ]);

        $shieldon->setProperty('interval_check_referer', 1);
        $shieldon->setProperty('time_reset_limit', 1);
        $shieldon->setProperty('limit_unusual_behavior', ['cookie' => 3, 'session' => 3, 'referer' => 3]);

        $shieldon->setIp('140.112.173.11');
        $this->assertSame($shieldon::RESPONSE_ALLOW, $shieldon->run());
        sleep(2);
        $this->assertSame($shieldon::RESPONSE_ALLOW, $shieldon->run());
        $ipDetail = $shieldon->driver->get('140.112.173.11', 'log');
        $this->assertEquals($ipDetail['flag_empty_referer'], 1);
        sleep(2);
        $this->assertSame($shieldon::RESPONSE_ALLOW, $shieldon->run());
        $ipDetail = $shieldon->driver->get('140.112.173.11', 'log');
        $this->assertEquals($ipDetail['flag_empty_referer'], 0);
    }

    public function testAction()
    {
        // Test 1. Check temporaily denying.

        $shieldon = new \Shieldon\Shieldon();
        $dbLocation = saveTestingFile('shieldon_unittest.sqlite3');

        $pdoInstance = new \PDO('sqlite:' . $dbLocation);
        $driver = new \Shieldon\Driver\SqliteDriver($pdoInstance);
        $shieldon->setDriver($driver);

        $_SERVER['HTTP_USER_AGENT'] = 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/70.0.3538.77 Safari/537.36';
        $shieldon->setComponent(new \Shieldon\Component\Ip());
        $shieldon->setComponent(new \Shieldon\Component\UserAgent());
        $shieldon->setComponent(new \Shieldon\Component\TrustedBot());
        $shieldon->setComponent(new \Shieldon\Component\Rdns());

        $shieldon->setChannel('test_shieldon_detect');
        $shieldon->driver->rebuild();

        $reflection = new \ReflectionObject($shieldon);
        $method = $reflection->getMethod('action');
        $method->setAccessible(true);
        $method->invokeArgs($shieldon, [
            $shieldon::ACTION_TEMPORARILY_DENY, $shieldon::REASON_REACHED_LIMIT_MINUTE, '140.112.172.11'
        ]);

        $shieldon->setIp('140.112.172.11');
        $result = $shieldon->run();
        $this->assertSame($shieldon::RESPONSE_TEMPORARILY_DENY, $result);

        // Test 2. Check unbaning.

        $method->invokeArgs($shieldon, [
            $shieldon::ACTION_UNBAN, $shieldon::REASON_MANUAL_BAN, '140.112.172.11'
        ]);

        $result = $shieldon->run();
        $this->assertSame($shieldon::RESPONSE_ALLOW, $result);
    }

    public function testGetComponent()
    {
        $shieldon = new \Shieldon\Shieldon();
        $shieldon->setComponent(new \Shieldon\Component\Ip());

        $reflection = new \ReflectionObject($shieldon);
        $method = $reflection->getMethod('getComponent');
        $method->setAccessible(true);
        $result = $method->invokeArgs($shieldon, ['Ip']);

        if ($result instanceof ComponentProvider) {
            $this->assertTrue(true);
        } else {
            $this->assertTrue(false);
        }
    }

    public function testSessionHandler()
    {
        $shieldon = new \Shieldon\Shieldon();
        $dbLocation = saveTestingFile('shieldon_unittest.sqlite3');

        $pdoInstance = new \PDO('sqlite:' . $dbLocation);
        $driver = new \Shieldon\Driver\SqliteDriver($pdoInstance);
        $shieldon->setDriver($driver);
        $shieldon->setChannel('test_shieldon_session');

        $_limit = 4;
        $shieldon->limitSession($_limit, 300);
        $shieldon->driver->rebuild();
        
        $reflection = new \ReflectionObject($shieldon);
        $methodSessionHandler = $reflection->getMethod('sessionHandler');
        $methodSessionHandler->setAccessible(true);


        // The first visitor.
        $shieldon->setIp('140.112.172.11');
        $methodSetSessionId = $reflection->getMethod('setSessionId');
        $methodSetSessionId->setAccessible(true);
        $methodSetSessionId->invokeArgs($shieldon, [md5(date('YmdHis') . mt_rand(1, 999999))]);
        $shieldon->run();

        $sessionHandlerResult = $methodSessionHandler->invokeArgs($shieldon, [$shieldon::RESPONSE_ALLOW]);

        $this->assertSame($sessionHandlerResult, $shieldon::RESPONSE_ALLOW);

        $t = $reflection->getProperty('sessionCount');
        $t->setAccessible(true);
        $sessionCount = $t->getValue($shieldon);
        $this->assertSame(1, $sessionCount);

        $t = $reflection->getProperty('currentSessionOrder');
        $t->setAccessible(true);
        $currentSessionOrder = $t->getValue($shieldon);
        $this->assertSame(1, $currentSessionOrder);

        $currentWaitNumber = $currentSessionOrder - $_limit;
        
        $t = $reflection->getProperty('currentWaitNumber');
        $t->setAccessible(true);
        $this->assertSame($currentWaitNumber, $t->getValue($shieldon));

        // The second visitor.
        $shieldon->setIp('140.112.172.12');
        $methodSetSessionId = $reflection->getMethod('setSessionId');
        $methodSetSessionId->setAccessible(true);
        $methodSetSessionId->invokeArgs($shieldon, [md5(date('YmdHis') . mt_rand(1, 1000))]);

        $result = $shieldon->run();
        $t = $reflection->getProperty('currentSessionOrder');
        $t->setAccessible(true);
        $currentSessionOrder = $t->getValue($shieldon);
        $this->assertSame(2, $currentSessionOrder);
        $this->assertSame($shieldon::RESPONSE_ALLOW, $result);

        // The third visitor.
        $shieldon->setIp('140.112.172.13');
        $methodSetSessionId = $reflection->getMethod('setSessionId');
        $methodSetSessionId->setAccessible(true);
        $methodSetSessionId->invokeArgs($shieldon, [md5(date('YmdHis') . mt_rand(1001, 2000))]);

        $result = $shieldon->run();
        $t = $reflection->getProperty('currentSessionOrder');
        $t->setAccessible(true);
        $currentSessionOrder = $t->getValue($shieldon);
        $this->assertSame(3, $currentSessionOrder);
        $this->assertSame($shieldon::RESPONSE_ALLOW, $result);

        // The fourth visitor.
        $shieldon->setIp('140.112.172.14');
        $methodSetSessionId = $reflection->getMethod('setSessionId');
        $methodSetSessionId->setAccessible(true);
        $methodSetSessionId->invokeArgs($shieldon, [md5(date('YmdHis') . mt_rand(2001, 3000))]);

        $result = $shieldon->run();
        $t = $reflection->getProperty('currentSessionOrder');
        $t->setAccessible(true);
        $currentSessionOrder = $t->getValue($shieldon);
        $this->assertSame(4, $currentSessionOrder);
        $this->assertSame($shieldon::RESPONSE_LIMIT, $result);

        // The fifth vistor.
        $shieldon->setIp('140.112.172.15');
        $methodSetSessionId = $reflection->getMethod('setSessionId');
        $methodSetSessionId->setAccessible(true);
        $methodSetSessionId->invokeArgs($shieldon, [md5(date('YmdHis') . mt_rand(1, 999999))]);

        $result = $shieldon->run();
        $this->assertSame($shieldon::RESPONSE_LIMIT, $result);

        // // Remove session if it expires.
        $shieldon->limitSession($_limit, 1);
        sleep(3);
        $result = $shieldon->run();
        $this->assertSame($shieldon::RESPONSE_LIMIT, $result);
    }

    public function testSetProperty()
    {
        $shieldon = new \Shieldon\Shieldon();
        $shieldon->setProperty();
        $shieldon->setProperty('interval_check_session', 1);
        $shieldon->setProperty('time_reset_limit', 1);
        $shieldon->setProperty('limit_unusual_behavior', ['cookie' => 1, 'session' => 1, 'referer' => 1]);
        $shieldon->setProperty('cookie_name', 'unittest');
        $shieldon->setProperty('cookie_domain', 'localhost');
        $shieldon->setProperty('lang', 'zh');
        $shieldon->setProperty('display_credit_link', false);
        $shieldon->setProperty('display_online_info', false);
        $shieldon->setProperty('display_lineup_info', false);

        $reflection = new \ReflectionObject($shieldon);
        $t = $reflection->getProperty('properties');
        $t->setAccessible(true);
        $properties = $t->getValue($shieldon);

        $this->assertSame($properties['interval_check_session'], 1);
        $this->assertSame($properties['time_reset_limit'], 1);
        $this->assertSame($properties['limit_unusual_behavior'], ['cookie' => 1, 'session' => 1, 'referer' => 1]);
        $this->assertSame($properties['cookie_name'], 'unittest');
        $this->assertSame($properties['cookie_domain'], 'localhost');
        $this->assertSame($properties['lang'], 'zh');
        $this->assertSame($properties['display_credit_link'], false);
        $this->assertSame($properties['display_online_info'], false);
        $this->assertSame($properties['display_lineup_info'], false);
    }

    public function testSetStrict()
    {
        $shieldon = new \Shieldon\Shieldon();
        $shieldon->setStrict(false);

        $reflection = new \ReflectionObject($shieldon);
        $t = $reflection->getProperty('strictMode');
        $t->setAccessible(true);
  
        $this->assertEquals('strictMode' , $t->name);
        $this->assertFalse($t->getValue($shieldon));
    }
}