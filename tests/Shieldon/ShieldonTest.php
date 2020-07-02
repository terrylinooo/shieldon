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

namespace Shieldon;

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
            'display_online_info'    => false,
        ];

        $shieldon = new \Shieldon\Shieldon();
        $shieldon->setProperties($properties);

        $reflection = new \ReflectionObject($shieldon);
        $t = $reflection->getProperty('properties');
        $t->setAccessible(true);
        $properties = $t->getValue($shieldon);

        $this->assertSame($properties['interval_check_session'], 1);
        $this->assertSame($properties['time_reset_limit'], 1);
        $this->assertSame($properties['limit_unusual_behavior'], ['cookie' => 1, 'session' => 1, 'referer' => 1]);
        $this->assertSame($properties['cookie_name'], 'unittest');
        $this->assertSame($properties['cookie_domain'], 'localhost');
        $this->assertSame($properties['display_online_info'], false);
    }

    public function testDetectByFilterFrequency($driver = 'sqlite')
    {
        $shieldon = get_testing_shieldon_instance($driver);

        $_SERVER['HTTP_USER_AGENT'] = 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/70.0.3538.77 Safari/537.36';
        $shieldon->add(new \Shieldon\Component\Ip());
        $shieldon->add(new \Shieldon\Component\UserAgent());
        $shieldon->add(new \Shieldon\Component\TrustedBot());
        $shieldon->add(new \Shieldon\Component\Rdns());

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

        // Reset the pageview check for specfic time unit.
        $shieldon->setIp('141.112.175.2');
        $this->assertSame($shieldon::RESPONSE_ALLOW, $shieldon->run());
        sleep(2);
        $this->assertSame($shieldon::RESPONSE_ALLOW, $shieldon->run());
        $ipDetail = $shieldon->driver->get('141.112.175.2', 'filter_log');

        if ($ipDetail['pageviews_s'] == 0) {
            $this->assertTrue(true);
        } else {
            $this->assertTrue(false);
        }
    }

    public function testDetectByFilterSession($driver = 'sqlite')
    {
        $shieldon = get_testing_shieldon_instance($driver);
        $shieldon->setIp('141.112.175.2');

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
    }

    public function testDetectByFilterReferer($driver = 'sqlite')
    {
        $shieldon = get_testing_shieldon_instance($driver);

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
    }

    public function testDetectByFilterCookie($driver = 'sqlite')
    {
        $shieldon = get_testing_shieldon_instance($driver);

        $shieldon->setFilter('session', false);
        $shieldon->setFilter('cookie', true);
        $shieldon->setFilter('referer', false);
        $shieldon->setFilter('frequency', false);

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
    }

    public function testResetFilterFlagChecks($driver = 'sqlite')
    {
        $shieldon = get_testing_shieldon_instance($driver);

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
        $ipDetail = $shieldon->driver->get('140.112.173.11', 'filter_log');
        $this->assertEquals($ipDetail['flag_empty_referer'], 1);
        sleep(2);
        $this->assertSame($shieldon::RESPONSE_ALLOW, $shieldon->run());
        $ipDetail = $shieldon->driver->get('140.112.173.11', 'filter_log');
        $this->assertEquals($ipDetail['flag_empty_referer'], 0);
    }

    public function testAction($driver = 'sqlite')
    {
        // Test 1. Check temporaily denying.

        $shieldon = get_testing_shieldon_instance($driver);

        $shieldon->add(new \Shieldon\Log\ActionLogger(BOOTSTRAP_DIR . '/../tmp/shieldon'));

        $_SERVER['HTTP_USER_AGENT'] = 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/70.0.3538.77 Safari/537.36';
        $shieldon->add(new \Shieldon\Component\Ip());
        $shieldon->add(new \Shieldon\Component\UserAgent());
        $shieldon->add(new \Shieldon\Component\TrustedBot());
        $shieldon->add(new \Shieldon\Component\Rdns());

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

    public function testNoComponentAndFilters()
    {
        $shieldon = get_testing_shieldon_instance();
        $shieldon->setChannel('test_shieldon_detect');
        $shieldon->setIp('39.27.1.1');
        $shieldon->disableFilters();
        $result = $shieldon->run();

        $this->assertSame($shieldon::RESPONSE_ALLOW, $result);
    }

    public function testGetComponent()
    {
        $shieldon = new \Shieldon\Shieldon();
        $shieldon->add(new \Shieldon\Component\Ip());

        $reflection = new \ReflectionObject($shieldon);
        $method = $reflection->getMethod('getComponent');
        $method->setAccessible(true);
        $result = $method->invokeArgs($shieldon, ['Ip']);

        if ($result instanceof \Shieldon\Component\ComponentProvider) {
            $this->assertTrue(true);
        } else {
            $this->assertTrue(false);
        }
    }

    public function testSessionHandler($driver = 'sqlite')
    {
        $shieldon = get_testing_shieldon_instance($driver);

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

        // Test.
        $testSessionId = get_session()->get('id');

        $this->assertSame($sessionId, $testSessionId);

        $sessionHandlerResult = $methodSessionHandler->invokeArgs($shieldon, [$shieldon::RESPONSE_ALLOW]);

        $this->assertSame($sessionHandlerResult, $shieldon::RESPONSE_ALLOW);

        $t = $reflection->getProperty('sessionStatus');
        $t->setAccessible(true);
        $sessionStatus = $t->getValue($shieldon);

        $currentSessionOrder = $sessionStatus['order'];
        $currentWaitNumber = $sessionStatus['order'] - $_limit;

        $this->assertSame(1, $sessionStatus['count']);
        $this->assertSame(1, $currentSessionOrder);

        $this->assertSame($currentWaitNumber, $sessionStatus['queue']);

        // The second visitor.
        $shieldon->setIp('140.112.172.12');
        $methodSetSessionId = $reflection->getMethod('setSessionId');
        $methodSetSessionId->setAccessible(true);
        $methodSetSessionId->invokeArgs($shieldon, [md5(date('YmdHis') . mt_rand(1, 1000))]);

        $result = $shieldon->run();
        $t = $reflection->getProperty('sessionStatus');
        $t->setAccessible(true);
        $sessionStatus = $t->getValue($shieldon);

        $currentSessionOrder = $sessionStatus['order'];

        $this->assertSame(2, $currentSessionOrder);
        $this->assertSame($shieldon::RESPONSE_ALLOW, $result);

        // The third visitor.
        $shieldon->setIp('140.112.172.13');
        $methodSetSessionId = $reflection->getMethod('setSessionId');
        $methodSetSessionId->setAccessible(true);
        $methodSetSessionId->invokeArgs($shieldon, [md5(date('YmdHis') . mt_rand(1001, 2000))]);

        $result = $shieldon->run();
        $t = $reflection->getProperty('sessionStatus');
        $t->setAccessible(true);
        $sessionStatus = $t->getValue($shieldon);

        $currentSessionOrder = $sessionStatus['order'];
        $this->assertSame(3, $currentSessionOrder);
        $this->assertSame($shieldon::RESPONSE_ALLOW, $result);

        // The fourth visitor.
        $shieldon->setIp('140.112.172.14');
        $methodSetSessionId = $reflection->getMethod('setSessionId');
        $methodSetSessionId->setAccessible(true);
        $methodSetSessionId->invokeArgs($shieldon, [md5(date('YmdHis') . mt_rand(2001, 3000))]);

        $result = $shieldon->run();
        $t = $reflection->getProperty('sessionStatus');
        $t->setAccessible(true);
        $sessionStatus = $t->getValue($shieldon);

        $currentSessionOrder = $sessionStatus['order'];
        $this->assertSame(4, $currentSessionOrder);
        $this->assertSame($shieldon::RESPONSE_LIMIT_SESSION, $result);

        // The fifth vistor.
        $shieldon->setIp('140.112.172.15');
        $methodSetSessionId = $reflection->getMethod('setSessionId');
        $methodSetSessionId->setAccessible(true);
        $methodSetSessionId->invokeArgs($shieldon, [md5(date('YmdHis') . mt_rand(1, 999999))]);

        $result = $shieldon->run();
        $this->assertSame($shieldon::RESPONSE_LIMIT_SESSION, $result);

        // // Remove session if it expires.
        $shieldon->limitSession($_limit, 1);
        sleep(3);
        $result = $shieldon->run();

        $this->assertSame($shieldon::RESPONSE_LIMIT_SESSION, $result);

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
        $shieldon->setProperty('display_online_info', true);
        $shieldon->setProperty('display_lineup_info', true);
        $shieldon->setProperty('display_user_info', true);

        $reflection = new \ReflectionObject($shieldon);
        $t = $reflection->getProperty('properties');
        $t->setAccessible(true);
        $properties = $t->getValue($shieldon);

        $this->assertSame($properties['interval_check_session'], 1);
        $this->assertSame($properties['time_reset_limit'], 1);
        $this->assertSame($properties['limit_unusual_behavior'], ['cookie' => 1, 'session' => 1, 'referer' => 1]);
        $this->assertSame($properties['cookie_name'], 'unittest');
        $this->assertSame($properties['cookie_domain'], 'localhost');
    }

    public function testSetDriver()
    {
        $shieldon = new \Shieldon\Shieldon();
        $dbLocation = save_testing_file('shieldon_unittest.sqlite3');

        $pdoInstance = new \PDO('sqlite:' . $dbLocation);
        $driver = new \Shieldon\Driver\SqliteDriver($pdoInstance);
        $shieldon->add($driver);

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
        $shieldon->add($logger);

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
        $shieldon = get_testing_shieldon_instance($driver);

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
        $shieldon->add($imageCaptcha);

        $reflection = new \ReflectionObject($shieldon);
        $t = $reflection->getProperty('captcha');
        $t->setAccessible(true);
        $refectedCaptcha = $t->getValue($shieldon);

        if ($refectedCaptcha['ImageCaptcha'] instanceof $imageCaptcha) {
            $this->assertTrue(true);
        } else {
            $this->assertTrue(false);
        }
    }

    public function testCaptchaResponse($driver = 'sqlite')
    {
        $shieldon = new \Shieldon\Shieldon();
        $shieldon->add(new \Shieldon\Captcha\ImageCaptcha());
        $result = $shieldon->captchaResponse();
        $this->assertFalse($result);

        $shieldon = new \Shieldon\Shieldon();
        $_POST['shieldon_captcha'] = 'ok';
        reload_request();

        $result = $shieldon->captchaResponse();
        $this->assertTrue($result);

        $shieldon = get_testing_shieldon_instance($driver);

        $shieldon->limitSession(1000, 9999);
        $reflection = new \ReflectionObject($shieldon);
        $methodSetSessionId = $reflection->getMethod('setSessionId');
        $methodSetSessionId->setAccessible(true);
        $methodSetSessionId->invokeArgs($shieldon, [md5(date('YmdHis') . mt_rand(2001, 3000))]);
        $result = $shieldon->run();
        $_POST['shieldon_captcha'] = 'ok';
        reload_request();

        $result = $shieldon->captchaResponse();
        $this->assertTrue($result);
    }

    public function testadd()
    {
        $shieldon = new \Shieldon\Shieldon();
        $ipComponent = new \Shieldon\Component\Ip();
        $shieldon->add($ipComponent);

        if ($shieldon->component['Ip'] === $ipComponent) {
            $this->assertTrue(true);
        } else {
            $this->assertTrue(false);
        }
    }

    public function testBan($driver = 'sqlite')
    {
        $shieldon = get_testing_shieldon_instance($driver);
        $shieldon->driver->rebuild();

        $shieldon->ban();
        $result = $shieldon->run();
        $this->assertSame($shieldon::RESPONSE_DENY, $result);

        $shieldon->unban();
        $this->assertSame($shieldon::RESPONSE_ALLOW, $shieldon->run());
    }

    public function testUnBan($driver = 'sqlite')
    {
        $shieldon = get_testing_shieldon_instance($driver);
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
        $shieldon->setView('<html><body>hello</body></html>', 'rejection');
        $reflection = new \ReflectionObject($shieldon);
        $t = $reflection->getProperty('html');
        $t->setAccessible(true);
        $view = $t->getValue($shieldon);
        if ($view['rejection'] === '<html><body>hello</body></html>') {
            $this->assertTrue(true);
        } else {
            $this->assertTrue(false);
        }
    }

    public function testOutput($driver = 'sqlite')
    {
        $_SERVER['REQUEST_URI'] = '/';
        reload_request();

        $shieldon = get_testing_shieldon_instance($driver);
        $shieldon->setProperty('display_lineup_info', true);
        $shieldon->setProperty('display_user_info', true);
        $shieldon->setProperty('display_online_info', true);
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
        if ($result === $shieldon::RESPONSE_LIMIT_SESSION) {
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

        $shieldon->setProperty('display_lineup_info', false);
        $shieldon->setProperty('display_user_info', false);
        $shieldon->setProperty('display_online_info', false);

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

            if (stripos($output, 'Captcha') !== false) {
                $this->assertTrue(true);
            } else {
                $this->assertTrue(false);
            }
        } else {
            $this->assertTrue(false);
        }
    }

    public function testIpCompoment($driver = 'sqlite')
    {
        $shieldon = new \Shieldon\Shieldon();
        
        $shieldon = get_testing_shieldon_instance($driver);
        $shieldon->driver->rebuild();

        $shieldon->add(new \Shieldon\Component\Ip());

        $shieldon->setIp('8.8.8.8');

        // Set an IP to whitelist.
        $shieldon->component['Ip']->setAllowedItem('8.8.8.8');
        $result = $shieldon->run();
        $this->assertSame($shieldon::RESPONSE_ALLOW, $result);

        // Set an IP to blacklist.

        $shieldon->setIp('8.8.4.4');
        $shieldon->component['Ip']->setDeniedItem('8.8.4.4');
        $result = $shieldon->run();
        $this->assertSame($shieldon::RESPONSE_DENY, $result);
    }

    public function testRun($driver = 'sqlite')
    {
        $shieldon = new \Shieldon\Shieldon();

        $shieldon = get_testing_shieldon_instance($driver);
        $shieldon->driver->rebuild();

        $headerComponent = new \Shieldon\Component\Header();
        $headerComponent->setStrict(true);

        $trustedBot = new \Shieldon\Component\TrustedBot();
        $trustedBot->setStrict(true);

        $ip = new \Shieldon\Component\Ip();
        $ip->setStrict(true);

        $userAgent = new \Shieldon\Component\UserAgent();
        $userAgent->setStrict(true);

        $rdns = new \Shieldon\Component\Rdns();
        $rdns->setStrict(true);

        $shieldon->add($trustedBot);
        $shieldon->add($ip);
        $shieldon->add($headerComponent);
        $shieldon->add($userAgent);
        $shieldon->add($rdns);
        
        // By default, it will block this session because of no common header information

        $shieldon->setIp('8.8.8.8');
        $result = $shieldon->run();
        $this->assertSame($shieldon::RESPONSE_DENY, $result);

        // Check trusted bots.

        // BING
        $shieldon = get_testing_shieldon_instance($driver);
        $_SERVER['HTTP_USER_AGENT'] = 'Mozilla/5.0 (compatible; bingbot/2.0; +http://www.bing.com/bingbot.htm)';
        $shieldon->add(new \Shieldon\Component\TrustedBot());
        $shieldon->setIp('40.77.169.1', true);
   
        $result = $shieldon->run();
        $this->assertSame($shieldon::RESPONSE_ALLOW, $result);

        // Code coverage for - // is no more needed for that IP.
        $result = $shieldon->run();
        $this->assertSame($shieldon::RESPONSE_ALLOW, $result);

        // GOOGLE
        $shieldon = get_testing_shieldon_instance($driver);
        $_SERVER['HTTP_USER_AGENT'] = 'Googlebot/2.1 (+http://www.google.com/bot.html)';
        $shieldon->add(new \Shieldon\Component\TrustedBot());
        $shieldon->setIp('66.249.66.1', true);
        $result = $shieldon->run();
        $this->assertSame($shieldon::RESPONSE_ALLOW, $result);

        // Code coverage for - // is no more needed for that IP.
        $result = $shieldon->run();
        $this->assertSame($shieldon::RESPONSE_ALLOW, $result);

        // YAHOO
        $shieldon = get_testing_shieldon_instance($driver);
        $_SERVER['HTTP_USER_AGENT'] = 'Mozilla/5.0 (compatible; Yahoo! Slurp; http://help.yahoo.com/help/us/ysearch/slurp)';
        $shieldon->add(new \Shieldon\Component\TrustedBot());
        $shieldon->setIp('8.12.144.1', true);
        $result = $shieldon->run();
        $this->assertSame($shieldon::RESPONSE_ALLOW, $result);

        // Code coverage for - // is no more needed for that IP.
        $result = $shieldon->run();
        $this->assertSame($shieldon::RESPONSE_ALLOW, $result);

        // OTHER
        $shieldon = get_testing_shieldon_instance($driver);
        $_SERVER['HTTP_USER_AGENT'] = 'Mozilla/5.0 (compatible; YandexBot/3.0; +http://yandex.com/bots)';
        $shieldon->add(new \Shieldon\Component\TrustedBot());
        $shieldon->setIp('100.43.90.1', true);
        $result = $shieldon->run();
        $this->assertSame($shieldon::RESPONSE_ALLOW, $result);

        // Code coverage for - // is no more needed for that IP.
        $result = $shieldon->run();
        $this->assertSame($shieldon::RESPONSE_ALLOW, $result);

        $shieldon = get_testing_shieldon_instance($driver);
        $shieldon->disableFilters();
        $result = $shieldon->run();
        $this->assertSame($shieldon::RESPONSE_ALLOW, $result);
    }

    public function testGetSessionCount($driver = 'sqlite')
    {
        $shieldon = get_testing_shieldon_instance($driver);
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
        $shieldon->disableFilters();
        $reflection = new \ReflectionObject($shieldon);
        $t = $reflection->getProperty('filterStatus');
        $t->setAccessible(true);
        $filterStatus = $t->getValue($shieldon);

        $this->assertFalse($filterStatus['frequency']);
        $this->assertFalse($filterStatus['referer']);
        $this->assertFalse($filterStatus['cookie']);
        $this->assertFalse($filterStatus['session']);
    }

    public function testIPv6($driver = 'sqlite')
    {
        $shieldon = get_testing_shieldon_instance($driver);
        $shieldon->driver->rebuild();
        $shieldon->setIp('0:0:0:0:0:ffff:c0a8:5f01');
        $result = $shieldon->run();

        $ipDetail = $shieldon->driver->get('0:0:0:0:0:ffff:c0a8:5f01', 'filter_log');

        $this->assertSame($ipDetail['ip'], '0:0:0:0:0:ffff:c0a8:5f01'); 
    }

    /***********************************************
     * File Driver 
     ***********************************************/

    public function testDetect_fileDriver()
    {
        $this->testDetectByFilterFrequency('file');
        $this->testDetectByFilterSession('file');
        $this->testDetectByFilterReferer('file');
        $this->testDetectByFilterCookie('file');
        $this->testResetFilterFlagChecks('file');
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

    public function testIPv6_fileDriver()
    {
        $this->testIPv6('file');
    }

    /***********************************************
     * MySQL Driver 
     ***********************************************/

    public function testDetect_mysqlDriver()
    {
        $this->testDetectByFilterFrequency('mysql');
        $this->testDetectByFilterSession('mysql');
        $this->testDetectByFilterReferer('mysql');
        $this->testDetectByFilterCookie('mysql');
        $this->testResetFilterFlagChecks('mysql');
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

    public function testIPv6_mysqlDriver()
    {
        $this->testIPv6('mysql');
    }

    /***********************************************
     * Redis Driver 
     ***********************************************/

    public function testDetect_redisDriver()
    {
        $this->testDetectByFilterFrequency('redis');
        $this->testDetectByFilterSession('redis');
        $this->testDetectByFilterReferer('redis');
        $this->testDetectByFilterCookie('redis');
        $this->testResetFilterFlagChecks('redis');
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

    public function testIPv6_redisDriver()
    {
        $this->testIPv6('redis');
    }

    /*****************/

    public function testSetMessenger()
    {
        $shieldon = new \Shieldon\Shieldon();

        $telegram = new \Messenger\Telegram('test', 'test');

        $shieldon->add($telegram);
    }

    public function testSetDialogUI()
    {
        $shieldon = new \Shieldon\Shieldon();

        $shieldon->setDialogUI([]);
    }

    public function testSetExcludedUrls()
    {
        $shieldon = new \Shieldon\Shieldon();

        $shieldon->setExcludedUrls([]);
    }

    public function testSetClosure()
    {
        $shieldon = new \Shieldon\Shieldon();

        $shieldon->setClosure('key', function() {
            return true;
        });
    }

    public function testManagedBy()
    {
        $shieldon = new \Shieldon\Shieldon();

        $shieldon->managedBy('demo');
    }

    /***********************************************
     * Test for building bridge to Iptable 
     ***********************************************/

    public function testDenyAttempts()
    {
        $shieldon = get_testing_shieldon_instance('file');

        //$_SERVER['HTTP_USER_AGENT'] = 'google';
        $_SERVER['HTTP_USER_AGENT'] = 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/70.0.3538.77 Safari/537.36';

        $shieldon->add(new \Shieldon\Component\TrustedBot());
        $shieldon->add(new \Shieldon\Component\Ip());
        $shieldon->add(new \Shieldon\Component\UserAgent());
        
        $shieldon->add(new \Shieldon\Component\Rdns());

        $shieldon->add(new \MockMessenger());

        $shieldon->setChannel('test_shieldon_deny_attempt');
        $shieldon->driver->rebuild();

        $shieldon->setProperty('deny_attempt_enable', [
            'data_circle' => true,
            'system_firewall' => true, 
        ]);

        $shieldon->setProperty('deny_attempt_notify', [
            'data_circle' => true,
            'system_firewall' => true, 
        ]);

        $shieldon->setProperty('deny_attempt_buffer', [
            'data_circle' => 2,
            'system_firewall' => 2, 
        ]);

        $shieldon->setProperty('reset_attempt_counter', 5);

        // Test for IPv4 and IPv6.
        foreach(['127.0.1.1', '2607:f0d0:1002:51::4'] as $ip) {

            $shieldon->setIp($ip);

            $result = $shieldon->run();
            $this->assertEquals($result, $shieldon::RESPONSE_ALLOW);
    
            $result = $shieldon->run();
            $this->assertEquals($result, $shieldon::RESPONSE_ALLOW);
    
            $result = $shieldon->run();
            $this->assertEquals($result, $shieldon::RESPONSE_TEMPORARILY_DENY);
    
            $result = $shieldon->run();
            $this->assertEquals($result, $shieldon::RESPONSE_TEMPORARILY_DENY);
    
            $result = $shieldon->run();
            $this->assertEquals($result, $shieldon::RESPONSE_TEMPORARILY_DENY);
    
            $result = $shieldon->run();
            $this->assertEquals($result, $shieldon::RESPONSE_DENY);
    
            $result = $shieldon->run();
            $this->assertEquals($result, $shieldon::RESPONSE_DENY);
    
            $result = $shieldon->run();
            $this->assertEquals($result, $shieldon::RESPONSE_DENY);
        }

        // Test for IPv4 and IPv6. 
        foreach(['127.0.1.2', '2607:f0d0:1002:52::4'] as $ip) {

            $shieldon->setIp($ip);

            $result = $shieldon->run();
            $this->assertEquals($result, $shieldon::RESPONSE_ALLOW);
    
            $result = $shieldon->run();
            $this->assertEquals($result, $shieldon::RESPONSE_ALLOW);
    
            $result = $shieldon->run();
            $this->assertEquals($result, $shieldon::RESPONSE_TEMPORARILY_DENY);

            sleep(7);
    
            $result = $shieldon->run();
            $this->assertEquals($result, $shieldon::RESPONSE_TEMPORARILY_DENY);
    
            $result = $shieldon->run();
            $this->assertEquals($result, $shieldon::RESPONSE_TEMPORARILY_DENY);

            $result = $shieldon->run();
            $this->assertEquals($result, $shieldon::RESPONSE_TEMPORARILY_DENY);
    
            $result = $shieldon->run();
            $this->assertEquals($result, $shieldon::RESPONSE_DENY);
    
            $result = $shieldon->run();
            $this->assertEquals($result, $shieldon::RESPONSE_DENY);
        }
    }

    public function testFakeTrustedBot()
    {
        $_SERVER['HTTP_USER_AGENT'] = 'google';

        $shieldon = get_testing_shieldon_instance();
        $shieldon->add(new \Shieldon\Component\TrustedBot());
        $shieldon->disableFilters();
        $result = $shieldon->run();

        $this->assertSame($shieldon::RESPONSE_DENY, $result);
    }
}