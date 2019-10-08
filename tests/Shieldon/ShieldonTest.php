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

    public function testDetect($driver = 'sqlite')
    {
        $shieldon = getTestingShieldonInstance($driver);

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

         /*
        $shieldon->setFilters([
            'session'   => false,
            'cookie'    => false,
            'referer'   => true,
            'frequency' => false,
        ]);
        */

       
        $shieldon->setFilter('session', false);
        $shieldon->setFilter('cookie', false);
        $shieldon->setFilter('referer', true);
        $shieldon->setFilter('frequency', false); 

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

    public function testAction($driver = 'sqlite')
    {
        // Test 1. Check temporaily denying.

        $shieldon = getTestingShieldonInstance($driver);

        $shieldon->setLogger(new \Shieldon\Log\ActionLogger(BOOTSTRAP_DIR . '/../tmp/shieldon'));

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

    public function testSessionHandler($driver = 'sqlite')
    {
        $shieldon = getTestingShieldonInstance($driver);

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

        $sessionId = md5(date('YmdHis') . mt_rand(1, 999999));
        $methodSetSessionId->invokeArgs($shieldon, [$sessionId]);
        $shieldon->run();

        // Test getSessionId
        $testSessionId = $shieldon->getSessionId();

        $this->assertSame($sessionId, $testSessionId);

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

        $result = $shieldon->run();
        $this->assertSame($shieldon::RESPONSE_ALLOW, $result);
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

    public function testSetDriver()
    {
        $shieldon = new \Shieldon\Shieldon();
        $dbLocation = saveTestingFile('shieldon_unittest.sqlite3');

        $pdoInstance = new \PDO('sqlite:' . $dbLocation);
        $driver = new \Shieldon\Driver\SqliteDriver($pdoInstance);
        $shieldon->setDriver($driver);

        if ($shieldon->driver === $driver) {
            $this->assertTrue(true);
        } else {
            $this->assertTrue(false);
        }
    }

    public function testSetLogger()
    {
        $shieldon = new \Shieldon\Shieldon();
  
        $logger = new \Shieldon\Log\ActionLogger(BOOTSTRAP_DIR . '/../tmp/shieldon');
        $shieldon->setLogger($logger);

        if ($shieldon->logger === $logger) {
            $this->assertTrue(true);
        } else {
            $this->assertTrue(false);
        }
    }

    public function testCreateDatabase()
    {
        $shieldon = new \Shieldon\Shieldon();
        $shieldon->createDatabase(false);
    
        $reflection = new \ReflectionObject($shieldon);
        $t = $reflection->getProperty('autoCreateDatabase');
        $t->setAccessible(true);
        $this->assertFalse($t->getValue($shieldon));
    }

    public function testSetChannel($driver = 'sqlite')
    {
        $shieldon = getTestingShieldonInstance($driver);

        $shieldon->setChannel('unittest');

        if ('unittest' === $shieldon->driver->getChannel()) {
            $this->assertTrue(true);
        } else {
            $this->assertTrue(false);
        }

        // Test exception.
        $this->expectException(\LogicException::class);
        $shieldon = new \Shieldon\Shieldon();
        $shieldon->setChannel('unittest');
    }

    public function testSetCaptcha()
    {
        $shieldon = new \Shieldon\Shieldon();
        $imageCaptcha = new \Shieldon\Captcha\ImageCaptcha();
        $shieldon->setCaptcha($imageCaptcha);

        $reflection = new \ReflectionObject($shieldon);
        $t = $reflection->getProperty('captcha');
        $t->setAccessible(true);
        $refectedCaptcha = $t->getValue($shieldon);

        if ($refectedCaptcha[1] instanceof $imageCaptcha) {
            $this->assertTrue(true);
        } else {
            $this->assertTrue(false);
        }
    }

    public function testCaptchaResponse($driver = 'sqlite')
    {
        $shieldon = new \Shieldon\Shieldon();
        $shieldon->setCaptcha(new \Shieldon\Captcha\ImageCaptcha());
        $result = $shieldon->captchaResponse();
        $this->assertFalse($result);

        $shieldon = new \Shieldon\Shieldon();
        $_POST['shieldon_captcha'] = 'ok';
        $result = $shieldon->captchaResponse();
        $this->assertTrue($result);

        $shieldon = getTestingShieldonInstance($driver);

        $shieldon->limitSession(1000, 9999);
        $reflection = new \ReflectionObject($shieldon);
        $methodSetSessionId = $reflection->getMethod('setSessionId');
        $methodSetSessionId->setAccessible(true);
        $methodSetSessionId->invokeArgs($shieldon, [md5(date('YmdHis') . mt_rand(2001, 3000))]);
        $result = $shieldon->run();
        $_POST['shieldon_captcha'] = 'ok';
        $result = $shieldon->captchaResponse();
        $this->assertTrue($result);
    }

    public function testSetComponent()
    {
        $shieldon = new \Shieldon\Shieldon();
        $ipComponent = new \Shieldon\Component\Ip();
        $shieldon->setComponent($ipComponent);

        if ($shieldon->component['Ip'] === $ipComponent) {
            $this->assertTrue(true);
        } else {
            $this->assertTrue(false);
        }
    }

    public function testBan($driver = 'sqlite')
    {
        $shieldon = getTestingShieldonInstance($driver);
        $shieldon->driver->rebuild();

        $shieldon->ban();
        $result = $shieldon->run();
        $this->assertSame($shieldon::RESPONSE_DENY, $result);

        $shieldon->unban();
        $this->assertSame($shieldon::RESPONSE_ALLOW, $shieldon->run());
    }

    public function testUnBan($driver = 'sqlite')
    {
        $shieldon = getTestingShieldonInstance($driver);
        $shieldon->driver->rebuild();
        $shieldon->setIp('33.33.33.33');

        $shieldon->ban('33.33.33.33');
        $this->assertSame($shieldon::RESPONSE_DENY, $shieldon->run());

        $shieldon->unban('33.33.33.33');
        $this->assertSame($shieldon::RESPONSE_ALLOW, $shieldon->run());
    }

    public function testSetView()
    {
        $shieldon = new \Shieldon\Shieldon();
        $shieldon->setView('<html><body>hello</body></html>', 'deny');
        $reflection = new \ReflectionObject($shieldon);
        $t = $reflection->getProperty('html');
        $t->setAccessible(true);
        $view = $t->getValue($shieldon);
        if ($view['deny'] === '<html><body>hello</body></html>') {
            $this->assertTrue(true);
        } else {
            $this->assertTrue(false);
        }
    }

    public function testOutput($driver = 'sqlite')
    {
        $_SERVER['REQUEST_URI'] = '/';

        $shieldon = getTestingShieldonInstance($driver);
        $shieldon->setProperty('display_credit_link', false);
        $shieldon->setProperty('display_online_info', false);
        $shieldon->setProperty('display_lineup_info', false);
        $shieldon->driver->rebuild();

        // Limit
        $shieldon->setIp('33.33.33.33');
        $reflection = new \ReflectionObject($shieldon);
        $methodSetSessionId = $reflection->getMethod('setSessionId');
        $methodSetSessionId->setAccessible(true);
        $methodSetSessionId->invokeArgs($shieldon, [md5('hello, this is an unit test!')]);

        $shieldon->limitSession(1, 30000);
        $result = $shieldon->run();
        $this->assertSame($shieldon::RESPONSE_ALLOW, $result);
        $result = $shieldon->run();
        if ($result === $shieldon::RESPONSE_LIMIT) {
            $output = $shieldon->output(0, false);

            if (strpos($output, 'Please line up') !== false) {
                $this->assertTrue(true);
            } else {
                $this->assertTrue(false);
            }
        }

        $shieldon->limitSession(100, 30000);
        $shieldon->setIp('33.33.33.33');
        $shieldon->ban('33.33.33.33');
        $result = $shieldon->run();

        if ($result === $shieldon::RESPONSE_DENY) {
            $output = $shieldon->output(0, false);

            if (strpos($output, 'Access denied') !== false) {
                $this->assertTrue(true);
            } else {
                $this->assertTrue(false);
            }
        } else {
            $this->assertTrue(false);
        }

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
        if ($result[3] === $shieldon::RESPONSE_TEMPORARILY_DENY) {
            $output = $shieldon->output(0, false);

            if (strpos($output, 'Captcha') !== false) {
                $this->assertTrue(true);
            } else {
                $this->assertTrue(false);
            }
        } else {
            $this->assertTrue(false);
        }
    }

    public function testRun($driver = 'sqlite')
    {
        $shieldon = new \Shieldon\Shieldon();

        $shieldon = getTestingShieldonInstance($driver);
        $shieldon->driver->rebuild();

        $shieldon->setComponent(new \Shieldon\Component\TrustedBot());
        $shieldon->setComponent(new \Shieldon\Component\Ip());
        $shieldon->setComponent(new \Shieldon\Component\Header());
        $shieldon->setComponent(new \Shieldon\Component\UserAgent());
        $shieldon->setComponent(new \Shieldon\Component\Rdns());
        
        // By default, it will block this session because of no common header information
        $shieldon->setStrict(true);
        $shieldon->setIp('8.8.8.8');
        $result = $shieldon->run();
        $this->assertSame($shieldon::RESPONSE_DENY, $result);

        // Set an IP to whitelist.
        $shieldon->component['Ip']->setAllowedItem('8.8.8.8');
        $result = $shieldon->run();
        $this->assertSame($shieldon::RESPONSE_ALLOW, $result);

        // Set an IP to blacklist.
        $shieldon->component['Ip']->removeItem('8.8.8.8');
        $shieldon->component['Ip']->setDeniedItem('8.8.8.8');
        $result = $shieldon->run();
        $this->assertSame($shieldon::RESPONSE_DENY, $result);

        // Check trusted bots.

        // BING
        $shieldon = getTestingShieldonInstance($driver);
        $_SERVER['HTTP_USER_AGENT'] = 'Mozilla/5.0 (compatible; bingbot/2.0; +http://www.bing.com/bingbot.htm)';
        $shieldon->setComponent(new \Shieldon\Component\TrustedBot());
        $shieldon->setIp('40.77.169.1', true);
        $shieldon->setStrict(false);
        $result = $shieldon->run();
        $this->assertSame($shieldon::RESPONSE_ALLOW, $result);

        // Code coverage for - // is no more needed for that IP.
        $result = $shieldon->run();
        $this->assertSame($shieldon::RESPONSE_ALLOW, $result);

        // GOOGLE
        $shieldon = getTestingShieldonInstance($driver);
        $_SERVER['HTTP_USER_AGENT'] = 'Googlebot/2.1 (+http://www.google.com/bot.html)';
        $shieldon->setComponent(new \Shieldon\Component\TrustedBot());
        $shieldon->setIp('66.249.66.1', true);
        $shieldon->setStrict(false);
        $result = $shieldon->run();
        $this->assertSame($shieldon::RESPONSE_ALLOW, $result);

        // Code coverage for - // is no more needed for that IP.
        $result = $shieldon->run();
        $this->assertSame($shieldon::RESPONSE_ALLOW, $result);

        // YAHOO
        $shieldon = getTestingShieldonInstance($driver);
        $_SERVER['HTTP_USER_AGENT'] = 'Mozilla/5.0 (compatible; Yahoo! Slurp; http://help.yahoo.com/help/us/ysearch/slurp)';
        $shieldon->setComponent(new \Shieldon\Component\TrustedBot());
        $shieldon->setIp('8.12.144.1', true);
        $shieldon->setStrict(false);
        $result = $shieldon->run();
        $this->assertSame($shieldon::RESPONSE_ALLOW, $result);

        // Code coverage for - // is no more needed for that IP.
        $result = $shieldon->run();
        $this->assertSame($shieldon::RESPONSE_ALLOW, $result);

        // OTHER
        $shieldon = getTestingShieldonInstance($driver);
        $_SERVER['HTTP_USER_AGENT'] = 'Mozilla/5.0 (compatible; YandexBot/3.0; +http://yandex.com/bots)';
        $shieldon->setComponent(new \Shieldon\Component\TrustedBot());
        $shieldon->setIp('100.43.90.1', true);
        $shieldon->setStrict(false);
        $result = $shieldon->run();
        $this->assertSame($shieldon::RESPONSE_ALLOW, $result);

        // Code coverage for - // is no more needed for that IP.
        $result = $shieldon->run();
        $this->assertSame($shieldon::RESPONSE_ALLOW, $result);

        $shieldon = getTestingShieldonInstance($driver);
        $shieldon->disableFiltering();
        $result = $shieldon->run();
        $this->assertSame($shieldon::RESPONSE_ALLOW, $result);
    }

    public function testGetSessionCount($driver = 'sqlite')
    {
        $shieldon = getTestingShieldonInstance($driver);
        $shieldon->driver->rebuild();

        $reflection = new \ReflectionObject($shieldon);
        $methodSetSessionId = $reflection->getMethod('setSessionId');
        $methodSetSessionId->setAccessible(true);

        $shieldon->limitSession(100, 3600);

        for ($i = 1; $i <= 10; $i++) {
            $shieldon->setIp(implode('.', [rand(1, 255), rand(1, 255), rand(1, 255), rand(1, 255)]));
            $methodSetSessionId->invokeArgs($shieldon, [md5(date('YmdHis') . mt_rand(1, 999999))]);
            $shieldon->run();
        }

        // Get how many people online.
        $sessionCount = $shieldon->getSessionCount();

        $this->assertSame($sessionCount, 10);
    }

    public function testOutputJsSnippet()
    {
        $shieldon = new \Shieldon\Shieldon();
        $js = $shieldon->outputJsSnippet();

        if (! empty($js)) {
            $this->assertTrue(true);
        } else {
            $this->assertTrue(false);
        }
    }

    public function testDisableFiltering()
    {
        $shieldon = new \Shieldon\Shieldon();
        $shieldon->disableFiltering();
        $reflection = new \ReflectionObject($shieldon);
        $t = $reflection->getProperty('enableFiltering');
        $t->setAccessible(true);
        $enableFiltering = $t->getValue($shieldon);

        $this->assertFalse($enableFiltering);
    }

    /***********************************************
     * File Driver 
     ***********************************************/

    public function testDetect_fileDriver()
    {
        $this->testDetect('file');
    }

    public function testAction_fileDriver()
    {
        $this->testAction('file');
    }

    public function testSessionHandler_fileDriver()
    {
        $this->testSessionHandler('file');
    }

    public function testSetChannel_fileDriver()
    {
        $this->testSetChannel('file');
    }

    public function testCaptchaResponse_fileDriver()
    {
        $this->testCaptchaResponse('file');
    }

    public function testBan_fileDriver()
    {
        $this->testBan('file');
    }

    public function testUnBan_fileDriver()
    {
        $this->testUnBan('file');
    }

    public function testRun_fileDriver()
    {
        $this->testRun('file');
    }

    public function testGetSessionCount_fileDriver()
    {
        $this->testGetSessionCount('file');
    }

    /***********************************************
     * MySQL Driver 
     ***********************************************/

    public function testDetect_mysqlDriver()
    {
        $this->testDetect('mysql');
    }

    public function testAction_mysqlDriver()
    {
        $this->testAction('mysql');
    }

    public function testSessionHandler_mysqlDriver()
    {
        $this->testSessionHandler('mysql');
    }

    public function testSetChannel_mysqlDriver()
    {
        $this->testSetChannel('mysql');
    }

    public function testCaptchaResponse_mysqlDriver()
    {
        $this->testCaptchaResponse('mysql');
    }

    public function testBan_mysqlDriver()
    {
        $this->testBan('mysql');
    }

    public function testUnBan_mysqlDriver()
    {
        $this->testUnBan('mysql');
    }

    public function testRun_mysqlDriver()
    {
        $this->testRun('mysql');
    }

    public function testGetSessionCount_mysqlDriver()
    {
        $this->testGetSessionCount('mysql');
    }

    /***********************************************
     * Redis Driver 
     ***********************************************/

    public function testDetect_redisDriver()
    {
        $this->testDetect('redis');
    }

    public function testAction_redisDriver()
    {
        $this->testAction('redis');
    }

    public function testSessionHandler_redisDriver()
    {
        $this->testSessionHandler('redis');
    }

    public function testSetChannel_redisDriver()
    {
        $this->testSetChannel('redis');
    }

    public function testCaptchaResponse_redisDriver()
    {
        $this->testCaptchaResponse('redis');
    }

    public function testBan_redisDriver()
    {
        $this->testBan('redis');
    }

    public function testUnBan_redisDriver()
    {
        $this->testUnBan('redis');
    }

    public function testRun_redisDriver()
    {
        $this->testRun('redis');
    }

    public function testGetSessionCount_redisDriver()
    {
        $this->testGetSessionCount('redis');
    }
}