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

namespace Shieldon\FirewallTest;

use Shieldon\Firewall\Kernel\Enum;
use function Shieldon\Firewall\get_session_instance;

class KernelTest extends \Shieldon\FirewallTest\ShieldonTestCase
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

        $kernel = new \Shieldon\Firewall\Kernel();
        $kernel->setProperties($properties);

        $reflection = new \ReflectionObject($kernel);
        $t = $reflection->getProperty('properties');
        $t->setAccessible(true);
        $properties = $t->getValue($kernel);

        $this->assertSame($properties['interval_check_session'], 1);
        $this->assertSame($properties['time_reset_limit'], 1);
        $this->assertSame($properties['limit_unusual_behavior'], ['cookie' => 1, 'session' => 1, 'referer' => 1]);
        $this->assertSame($properties['cookie_name'], 'unittest');
        $this->assertSame($properties['cookie_domain'], 'localhost');
        $this->assertSame($properties['display_online_info'], false);
    }

    public function testDetectByFilterFrequency($driver = 'sqlite')
    {
        $kernel = $this->getKernelInstance($driver);
        $kernel->driver->rebuild();

         // phpcs:ignore
        $_SERVER['HTTP_USER_AGENT'] = 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/70.0.3538.77 Safari/537.36';
        $kernel->setComponent(new \Shieldon\Firewall\Component\Ip());
        $kernel->setComponent(new \Shieldon\Firewall\Component\UserAgent());
        $kernel->setComponent(new \Shieldon\Firewall\Component\TrustedBot());
        $kernel->setComponent(new \Shieldon\Firewall\Component\Rdns());

        $kernel->setChannel('test_shieldon_detect');
        $kernel->driver->rebuild();

        // Test 1.
        $kernel->setIp('141.112.175.1');

        $kernel->setProperty('time_unit_quota', [
            's' => 2,
            'm' => 20,
            'h' => 60,
            'd' => 240,
        ]);

        $result = [];
        for ($i = 1; $i <= 5; $i++) {
            $result[$i] = $kernel->run();
        }

        $this->assertSame(Enum::RESPONSE_ALLOW, $result[1]);
        $this->assertSame(Enum::RESPONSE_ALLOW, $result[2]);
        $this->assertSame(Enum::RESPONSE_TEMPORARILY_DENY, $result[3]);
        $this->assertSame(Enum::RESPONSE_TEMPORARILY_DENY, $result[4]);
        $this->assertSame(Enum::RESPONSE_TEMPORARILY_DENY, $result[5]);

        // Reset the pageview check for specfic time unit.
        $kernel->setIp('141.112.175.2');
        $this->assertSame(Enum::RESPONSE_ALLOW, $kernel->run());
        sleep(2);
        $this->assertSame(Enum::RESPONSE_ALLOW, $kernel->run());
        $ipDetail = $kernel->driver->get('141.112.175.2', 'filter');

        if ($ipDetail['pageviews_s'] == 0) {
            $this->assertTrue(true);
        } else {
            $this->assertTrue(false);
        }
    }

    public function testDetectByFilterSession($driver = 'sqlite')
    {
        $kernel = $this->getKernelInstance($driver);
        $kernel->driver->rebuild();
        $kernel->setIp('141.112.175.2');

        $kernel->setFilters([
            'session'   => true,
            'cookie'    => false,
            'referer'   => false,
            'frequency' => false,
        ]);

        $kernel->setProperty('interval_check_session', 1);
        $kernel->setProperty('limit_unusual_behavior', ['cookie' => 3, 'session' => 3, 'referer' => 3]);

        // Let's get started checking Session.
        for ($i = 0; $i < 5; $i++) {
            $kernel->setIp('140.112.172.255');
            $kernel->limitSession(1000, 9999);
            $reflection = new \ReflectionObject($kernel);
            $methodSetSessionId = $reflection->getMethod('setSessionId');
            $methodSetSessionId->setAccessible(true);
            $methodSetSessionId->invokeArgs($kernel, [md5(date('YmdHis') . mt_rand(2001, 3000))]);
            $results[$i] = $kernel->run();
            sleep(2);
        }

        $this->assertSame(Enum::RESPONSE_ALLOW, $results[0]);
        $this->assertSame(Enum::RESPONSE_ALLOW, $results[1]);
        $this->assertSame(Enum::RESPONSE_ALLOW, $results[2]);
        $this->assertSame(Enum::RESPONSE_ALLOW, $results[3]);
        $this->assertSame(Enum::RESPONSE_TEMPORARILY_DENY, $results[4]);
    }

    public function testDetectByFilterReferer($driver = 'sqlite')
    {
        $kernel = $this->getKernelInstance($driver);
        $kernel->driver->rebuild();

        $kernel->setFilters([
            'session'   => false,
            'cookie'    => false,
            'referer'   => true,
            'frequency' => false,
        ]);

        $kernel->setProperty('interval_check_referer', 1);
        $kernel->setProperty('limit_unusual_behavior', ['cookie' => 3, 'session' => 3, 'referer' => 3]);

        for ($i = 0; $i < 5; $i++) {
            $kernel->setIp('140.112.173.1');
            $results[$i] = $kernel->run();
            sleep(2);
        }

        $this->assertSame(Enum::RESPONSE_ALLOW, $results[0]);
        $this->assertSame(Enum::RESPONSE_ALLOW, $results[1]);
        $this->assertSame(Enum::RESPONSE_ALLOW, $results[2]);
        $this->assertSame(Enum::RESPONSE_ALLOW, $results[3]);
        $this->assertSame(Enum::RESPONSE_TEMPORARILY_DENY, $results[4]);
    }

    public function testDetectByFilterCookie($driver = 'sqlite')
    {
        $kernel = $this->getKernelInstance($driver);
        $kernel->driver->rebuild();

        $kernel->setFilter('session', false);
        $kernel->setFilter('cookie', true);
        $kernel->setFilter('referer', false);
        $kernel->setFilter('frequency', false);

        $kernel->setProperty('limit_unusual_behavior', ['cookie' => 3, 'session' => 3, 'referer' => 3]);

        for ($i = 0; $i < 5; $i++) {
            $kernel->setIp('140.112.174.8');
            $results[$i] = $kernel->run();
        }

        $this->assertSame(Enum::RESPONSE_ALLOW, $results[0]);
        $this->assertSame(Enum::RESPONSE_ALLOW, $results[1]);
        $this->assertSame(Enum::RESPONSE_ALLOW, $results[2]);
        $this->assertSame(Enum::RESPONSE_ALLOW, $results[3]);
        $this->assertSame(Enum::RESPONSE_TEMPORARILY_DENY, $results[4]);

        $kernel->setProperty('cookie_name', 'unittest');
        $_COOKIE['unittest'] = 1;
        $this->refreshRequest();

        for ($i = 0; $i < 10; $i++) {
            $kernel->setIp('140.112.175.10');
            $results[$i] = $kernel->run();

            if ($i >= 5) {
                $_COOKIE['unittest'] = 2;
                $this->refreshRequest();
            }
        }

        $this->assertSame(Enum::RESPONSE_ALLOW, $results[0]);
        $this->assertSame(Enum::RESPONSE_ALLOW, $results[1]);
        $this->assertSame(Enum::RESPONSE_ALLOW, $results[2]);
        $this->assertSame(Enum::RESPONSE_ALLOW, $results[3]);
        $this->assertSame(Enum::RESPONSE_ALLOW, $results[4]);
        $this->assertSame(Enum::RESPONSE_ALLOW, $results[5]);
        $this->assertSame(Enum::RESPONSE_ALLOW, $results[6]);
        $this->assertSame(Enum::RESPONSE_ALLOW, $results[7]);
        $this->assertSame(Enum::RESPONSE_TEMPORARILY_DENY, $results[8]);
        $this->assertSame(Enum::RESPONSE_TEMPORARILY_DENY, $results[9]);
    }

    public function testResetFilterFlagChecks($driver = 'sqlite')
    {
        $kernel = $this->getKernelInstance($driver);

        $kernel->setFilters([
            'session'   => false,
            'cookie'    => false,
            'referer'   => true,
            'frequency' => false,
        ]);

        $kernel->setProperty('interval_check_referer', 1);
        $kernel->setProperty('time_reset_limit', 1);
        $kernel->setProperty('limit_unusual_behavior', ['cookie' => 3, 'session' => 3, 'referer' => 3]);

        $kernel->setIp('140.112.173.11');
        $this->assertSame(Enum::RESPONSE_ALLOW, $kernel->run());
        sleep(2);
        $this->assertSame(Enum::RESPONSE_ALLOW, $kernel->run());
        $ipDetail = $kernel->driver->get('140.112.173.11', 'filter');
        $this->assertEquals($ipDetail['flag_empty_referer'], 1);
        sleep(2);
        $this->assertSame(Enum::RESPONSE_ALLOW, $kernel->run());
        $ipDetail = $kernel->driver->get('140.112.173.11', 'filter');
        $this->assertEquals($ipDetail['flag_empty_referer'], 0);
    }

    public function testAction($driver = 'sqlite')
    {
        // Test 1. Check temporaily denying.

        $kernel = $this->getKernelInstance($driver);

        $kernel->setLogger(new \Shieldon\Firewall\Log\ActionLogger(BOOTSTRAP_DIR . '/../tmp/shieldon'));

         // phpcs:ignore
        $_SERVER['HTTP_USER_AGENT'] = 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/70.0.3538.77 Safari/537.36';
        $kernel->setComponent(new \Shieldon\Firewall\Component\Ip());
        $kernel->setComponent(new \Shieldon\Firewall\Component\UserAgent());
        $kernel->setComponent(new \Shieldon\Firewall\Component\TrustedBot());
        $kernel->setComponent(new \Shieldon\Firewall\Component\Rdns());

        $kernel->setChannel('test_shieldon_detect');
        $kernel->driver->rebuild();

        $reflection = new \ReflectionObject($kernel);
        $method = $reflection->getMethod('action');
        $method->setAccessible(true);
        $method->invokeArgs($kernel, [
            Enum::ACTION_TEMPORARILY_DENY, Enum::REASON_REACH_MINUTELY_LIMIT_DENIED,
            '140.112.172.11',
        ]);

        $kernel->setIp('140.112.172.11');
        $result = $kernel->run();
        $this->assertSame(Enum::RESPONSE_TEMPORARILY_DENY, $result);

        // Test 2. Check unbaning.

        $method->invokeArgs($kernel, [
            Enum::ACTION_UNBAN, Enum::REASON_MANUAL_BAN_DENIED,
            '140.112.172.11',
        ]);

        $result = $kernel->run();
        $this->assertSame(Enum::RESPONSE_ALLOW, $result);
    }

    public function testNoComponentAndFilters()
    {
        $kernel = $this->getKernelInstance();
        $kernel->setChannel('test_shieldon_detect');
        $kernel->setIp('39.27.1.1');
        $kernel->disableFilters();
        $result = $kernel->run();

        $this->assertSame(Enum::RESPONSE_ALLOW, $result);
    }

    public function testGetComponent()
    {
        $kernel = new \Shieldon\Firewall\Kernel();
        $kernel->setComponent(new \Shieldon\Firewall\Component\Ip());

        $reflection = new \ReflectionObject($kernel);
        $method = $reflection->getMethod('getComponent');
        $method->setAccessible(true);
        $result = $method->invokeArgs($kernel, ['Ip']);

        if ($result instanceof \Shieldon\Firewall\Component\ComponentProvider) {
            $this->assertTrue(true);
        } else {
            $this->assertTrue(false);
        }
    }

    public function testSessionHandler($driver = 'sqlite')
    {
        $kernel = $this->getKernelInstance($driver);

        $kernel->setChannel('test_shieldon_session');

        $_limit = 4;
        $kernel->limitSession($_limit, 300);
        $kernel->driver->rebuild();

        $reflection = new \ReflectionObject($kernel);
        $methodSessionHandler = $reflection->getMethod('sessionHandler');
        $methodSessionHandler->setAccessible(true);

        // The first visitor.
        $kernel->setIp('140.112.172.11');
        $methodSetSessionId = $reflection->getMethod('setSessionId');
        $methodSetSessionId->setAccessible(true);

        $sessionId = md5(date('YmdHis') . mt_rand(1, 999999));
        $methodSetSessionId->invokeArgs($kernel, [$sessionId]);
        $kernel->run();

        // Test.
        $testSessionId = get_session_instance()->getId();

        $this->assertSame($sessionId, $testSessionId);

        $sessionHandlerResult = $methodSessionHandler->invokeArgs($kernel, [Enum::RESPONSE_ALLOW]);

        $this->assertSame($sessionHandlerResult, Enum::RESPONSE_ALLOW);

        $t = $reflection->getProperty('sessionStatus');
        $t->setAccessible(true);
        $sessionStatus = $t->getValue($kernel);

        $currentSessionOrder = $sessionStatus['order'];
        $currentWaitNumber = $sessionStatus['order'] - $_limit;

        $this->assertSame(1, $sessionStatus['count']);
        $this->assertSame(1, $currentSessionOrder);

        $this->assertSame($currentWaitNumber, $sessionStatus['queue']);

        // The second visitor.
        $kernel->setIp('140.112.172.12');
        $methodSetSessionId = $reflection->getMethod('setSessionId');
        $methodSetSessionId->setAccessible(true);
        $methodSetSessionId->invokeArgs($kernel, [md5(date('YmdHis') . mt_rand(1, 1000))]);

        $result = $kernel->run();
        $t = $reflection->getProperty('sessionStatus');
        $t->setAccessible(true);
        $sessionStatus = $t->getValue($kernel);

        $currentSessionOrder = $sessionStatus['order'];

        $this->assertSame(2, $currentSessionOrder);
        $this->assertSame(Enum::RESPONSE_ALLOW, $result);

        // The third visitor.
        $kernel->setIp('140.112.172.13');
        $methodSetSessionId = $reflection->getMethod('setSessionId');
        $methodSetSessionId->setAccessible(true);
        $methodSetSessionId->invokeArgs($kernel, [md5(date('YmdHis') . mt_rand(1001, 2000))]);

        $result = $kernel->run();
        $t = $reflection->getProperty('sessionStatus');
        $t->setAccessible(true);
        $sessionStatus = $t->getValue($kernel);

        $currentSessionOrder = $sessionStatus['order'];
        $this->assertSame(3, $currentSessionOrder);
        $this->assertSame(Enum::RESPONSE_ALLOW, $result);

        // The fourth visitor.
        $kernel->setIp('140.112.172.14');
        $methodSetSessionId = $reflection->getMethod('setSessionId');
        $methodSetSessionId->setAccessible(true);
        $methodSetSessionId->invokeArgs($kernel, [md5(date('YmdHis') . mt_rand(2001, 3000))]);

        $result = $kernel->run();
        $t = $reflection->getProperty('sessionStatus');
        $t->setAccessible(true);
        $sessionStatus = $t->getValue($kernel);

        $currentSessionOrder = $sessionStatus['order'];
        $this->assertSame(4, $currentSessionOrder);
        $this->assertSame(Enum::RESPONSE_LIMIT_SESSION, $result);

        // The fifth vistor.
        $kernel->setIp('140.112.172.15');
        $methodSetSessionId = $reflection->getMethod('setSessionId');
        $methodSetSessionId->setAccessible(true);
        $methodSetSessionId->invokeArgs($kernel, [md5(date('YmdHis') . mt_rand(1, 999999))]);

        $result = $kernel->run();
        $this->assertSame(Enum::RESPONSE_LIMIT_SESSION, $result);

        // // Remove session if it expires.
        $kernel->limitSession($_limit, 1);
        sleep(3);
        $result = $kernel->run();

        $this->assertSame(Enum::RESPONSE_LIMIT_SESSION, $result);

        $result = $kernel->run();
        $this->assertSame(Enum::RESPONSE_ALLOW, $result);
    }

    public function testSessionHandler_uniqueSession($driver = 'file')
    {
        $kernel = $this->getKernelInstance($driver);

        $kernel->disableFilters();
        $kernel->setFilter('session', true);

        $kernel->setChannel('testsessionlimit');

        $_limit = 100;
        $kernel->limitSession($_limit, 300, true);
        $kernel->driver->rebuild();

        $reflection = new \ReflectionObject($kernel);
        $methodSessionHandler = $reflection->getMethod('sessionHandler');
        $methodSessionHandler->setAccessible(true);

        // The first visitor.
        $kernel->setIp('140.112.172.11');
        $methodSetSessionId = $reflection->getMethod('setSessionId');
        $methodSetSessionId->setAccessible(true);

        $sessionId = md5(date('YmdHis') . mt_rand(1, 999999));
        $methodSetSessionId->invokeArgs($kernel, [$sessionId]);
        $kernel->run();

        for ($i = 1; $i <= 10; $i++) {
            $kernel->setIp('140.112.172.12');
            $sessionId = md5(date('YmdHis') . mt_rand(1, 999999));
            $methodSetSessionId->invokeArgs($kernel, [$sessionId]);
            $kernel->run();
        }

        $this->assertEquals(7, $kernel->getSessionCount());
    }

    public function testSetProperty()
    {
        $kernel = new \Shieldon\Firewall\Kernel();
        $kernel->setProperty();
        $kernel->setProperty('interval_check_session', 1);
        $kernel->setProperty('time_reset_limit', 1);
        $kernel->setProperty('limit_unusual_behavior', ['cookie' => 1, 'session' => 1, 'referer' => 1]);
        $kernel->setProperty('cookie_name', 'unittest');
        $kernel->setProperty('cookie_domain', 'localhost');
        $kernel->setProperty('display_online_info', true);
        $kernel->setProperty('display_lineup_info', true);
        $kernel->setProperty('display_user_info', true);

        $reflection = new \ReflectionObject($kernel);
        $t = $reflection->getProperty('properties');
        $t->setAccessible(true);
        $properties = $t->getValue($kernel);

        $this->assertSame($properties['interval_check_session'], 1);
        $this->assertSame($properties['time_reset_limit'], 1);
        $this->assertSame($properties['limit_unusual_behavior'], ['cookie' => 1, 'session' => 1, 'referer' => 1]);
        $this->assertSame($properties['cookie_name'], 'unittest');
        $this->assertSame($properties['cookie_domain'], 'localhost');
    }

    public function testSetDriver()
    {
        $kernel = new \Shieldon\Firewall\Kernel();
        $dbLocation = $this->getWritableTestFilePath('shieldon_unittest.sqlite3');

        $pdoInstance = new \PDO('sqlite:' . $dbLocation);
        $driver = new \Shieldon\Firewall\Driver\SqliteDriver($pdoInstance);
        $kernel->setDriver($driver);

        if ($kernel->driver === $driver) {
            $this->assertTrue(true);
        } else {
            $this->assertTrue(false);
        }
    }

    public function testSetLogger()
    {
        $kernel = new \Shieldon\Firewall\Kernel();
  
        $logger = new \Shieldon\Firewall\Log\ActionLogger(BOOTSTRAP_DIR . '/../tmp/shieldon');
        $kernel->setLogger($logger);

        if ($kernel->logger === $logger) {
            $this->assertTrue(true);
        } else {
            $this->assertTrue(false);
        }
    }

    public function testCreateDatabase()
    {
        $kernel = new \Shieldon\Firewall\Kernel();
        $kernel->disableDbBuilder();
    
        $reflection = new \ReflectionObject($kernel);
        $t = $reflection->getProperty('isCreateDatabase');
        $t->setAccessible(true);

        // CLI returns true always.
        $this->assertTrue($t->getValue($kernel));
    }

    public function testSetChannel($driver = 'sqlite')
    {
        $kernel = $this->getKernelInstance($driver);

        $kernel->setChannel('unittest');

        if ('unittest' === $kernel->driver->getChannel()) {
            $this->assertTrue(true);
        } else {
            $this->assertTrue(false);
        }
    }

    public function testSetCaptcha()
    {
        $kernel = new \Shieldon\Firewall\Kernel();
        $imageCaptcha = new \Shieldon\Firewall\Captcha\ImageCaptcha();
        $kernel->setCaptcha($imageCaptcha);

        $reflection = new \ReflectionObject($kernel);
        $t = $reflection->getProperty('captcha');
        $t->setAccessible(true);
        $refectedCaptcha = $t->getValue($kernel);

        if ($refectedCaptcha['ImageCaptcha'] instanceof $imageCaptcha) {
            $this->assertTrue(true);
        } else {
            $this->assertTrue(false);
        }
    }

    public function testCaptchaResponse($driver = 'sqlite')
    {
        $kernel = new \Shieldon\Firewall\Kernel();
        $kernel->setCaptcha(new \Shieldon\Firewall\Captcha\ImageCaptcha());
        $result = $kernel->captchaResponse();
        $this->assertFalse($result);

        $kernel = new \Shieldon\Firewall\Kernel();
        $_POST['shieldon_captcha'] = 'ok';
        $this->refreshRequest();

        $result = $kernel->captchaResponse();
        $this->assertTrue($result);

        $kernel = $this->getKernelInstance($driver);

        $kernel->limitSession(1000, 9999);
        $reflection = new \ReflectionObject($kernel);
        $methodSetSessionId = $reflection->getMethod('setSessionId');
        $methodSetSessionId->setAccessible(true);
        $methodSetSessionId->invokeArgs($kernel, [md5(date('YmdHis') . mt_rand(2001, 3000))]);
        $result = $kernel->run();
        $_POST['shieldon_captcha'] = 'ok';
        $this->refreshRequest();

        $result = $kernel->captchaResponse();
        $this->assertTrue($result);
    }

    public function testadd()
    {
        $kernel = new \Shieldon\Firewall\Kernel();
        $ipComponent = new \Shieldon\Firewall\Component\Ip();
        $kernel->setComponent($ipComponent);

        if ($kernel->component['Ip'] === $ipComponent) {
            $this->assertTrue(true);
        } else {
            $this->assertTrue(false);
        }
    }

    public function testBan($driver = 'sqlite')
    {
        $kernel = $this->getKernelInstance($driver);
        $kernel->driver->rebuild();

        $kernel->ban();
        $result = $kernel->run();
        $this->assertSame(Enum::RESPONSE_DENY, $result);

        $kernel->unban();
        $this->assertSame(Enum::RESPONSE_ALLOW, $kernel->run());
    }

    public function testUnBan($driver = 'sqlite')
    {
        $kernel = $this->getKernelInstance($driver);
        $kernel->driver->rebuild();
        $kernel->setIp('33.33.33.33');

        $kernel->ban('33.33.33.33');
        $this->assertSame(Enum::RESPONSE_DENY, $kernel->run());

        $kernel->unban('33.33.33.33');
        $this->assertSame(Enum::RESPONSE_ALLOW, $kernel->run());
    }

    public function testRespond($driver = 'sqlite')
    {
        $_SERVER['REQUEST_URI'] = '/';
        $this->refreshRequest();

        $kernel = $this->getKernelInstance($driver);
        $kernel->setProperty('display_lineup_info', true);
        $kernel->setProperty('display_user_info', true);
        $kernel->setProperty('display_online_info', true);
        $kernel->driver->rebuild();

        // Limit
        $kernel->setIp('33.33.33.33');
        $reflection = new \ReflectionObject($kernel);
        $methodSetSessionId = $reflection->getMethod('setSessionId');
        $methodSetSessionId->setAccessible(true);
        $methodSetSessionId->invokeArgs($kernel, [md5('hello, this is an unit test!')]);

        $kernel->limitSession(1, 30000);
        $result = $kernel->run();
        $this->assertSame(Enum::RESPONSE_ALLOW, $result);
        $result = $kernel->run();

        if ($result === Enum::RESPONSE_LIMIT_SESSION) {
            $response = $kernel->respond();
            $output = $response->getBody()->getContents();

            if (strpos($output, 'Please Queue') !== false) {
                $this->assertTrue(true);
            } else {
                $this->assertTrue(false);
            }
        }

        $kernel->limitSession(100, 30000);
        $kernel->setIp('33.33.33.33');
        $kernel->ban('33.33.33.33');
        $result = $kernel->run();

        if ($result === Enum::RESPONSE_DENY) {
            $response = $kernel->respond();
            $output = $response->getBody()->getContents();

            if (strpos($output, 'Access Denied') !== false) {
                $this->assertTrue(true);
            } else {
                $this->assertTrue(false);
            }
        } else {
            $this->assertTrue(false);
        }

        $kernel->setIp('141.112.175.1');

        $kernel->setProperty('display_lineup_info', false);
        $kernel->setProperty('display_user_info', false);
        $kernel->setProperty('display_online_info', false);

        $kernel->setProperty('time_unit_quota', [
            's' => 2,
            'm' => 20,
            'h' => 60,
            'd' => 240,
        ]);

        $result = [];
        for ($i = 1; $i <= 5; $i++) {
            $result[$i] = $kernel->run();
        }

        $this->assertSame(Enum::RESPONSE_ALLOW, $result[1]);
        $this->assertSame(Enum::RESPONSE_ALLOW, $result[2]);
        if ($result[3] === Enum::RESPONSE_TEMPORARILY_DENY) {
            $response = $kernel->respond();
            $output = $response->getBody()->getContents();

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
        $kernel = new \Shieldon\Firewall\Kernel();
        
        $kernel = $this->getKernelInstance($driver);
        $kernel->driver->rebuild();

        $kernel->setComponent(new \Shieldon\Firewall\Component\Ip());

        $kernel->setIp('8.8.8.8');

        // Set an IP to whitelist.
        $kernel->component['Ip']->setAllowedItem('8.8.8.8');
        $result = $kernel->run();
        $this->assertSame(Enum::RESPONSE_ALLOW, $result);

        // Set an IP to blacklist.

        $kernel->setIp('8.8.4.4');
        $kernel->component['Ip']->setDeniedItem('8.8.4.4');
        $result = $kernel->run();
        $this->assertSame(Enum::RESPONSE_DENY, $result);
    }

    public function testSetStrict()
    {
        $kernel = new \Shieldon\Firewall\Kernel();
        $kernel->setStrict(false);

        $reflection = new \ReflectionObject($kernel);
        $t = $reflection->getProperty('strictMode');
        $t->setAccessible(true);
  
        $this->assertEquals('strictMode', $t->name);
        $this->assertFalse($t->getValue($kernel));
    }

    public function testSetStrictTrue()
    {
        $kernel = $this->getKernelInstance();
        $kernel->driver->rebuild();
        $kernel->setStrict(true);

        $headerComponent = new \Shieldon\Firewall\Component\Header();
        $trustedBot = new \Shieldon\Firewall\Component\TrustedBot();
        $ip = new \Shieldon\Firewall\Component\Ip();
        $userAgent = new \Shieldon\Firewall\Component\UserAgent();
        $rdns = new \Shieldon\Firewall\Component\Rdns();

        $kernel->setComponent($trustedBot);
        $kernel->setComponent($ip);
        $kernel->setComponent($headerComponent);
        $kernel->setComponent($userAgent);
        $kernel->setComponent($rdns);

        $result = $kernel->run();

        $this->assertEquals(0, $result);
    }

    public function testRun($driver = 'sqlite')
    {
        $kernel = new \Shieldon\Firewall\Kernel();

        $kernel = $this->getKernelInstance($driver);
        $kernel->driver->rebuild();

        $headerComponent = new \Shieldon\Firewall\Component\Header();
        $headerComponent->setStrict(true);

        $trustedBot = new \Shieldon\Firewall\Component\TrustedBot();
        $trustedBot->setStrict(true);

        $ip = new \Shieldon\Firewall\Component\Ip();
        $ip->setStrict(true);

        $userAgent = new \Shieldon\Firewall\Component\UserAgent();
        $userAgent->setStrict(true);

        $rdns = new \Shieldon\Firewall\Component\Rdns();
        $rdns->setStrict(true);

        $kernel->setComponent($trustedBot);
        $kernel->setComponent($ip);
        $kernel->setComponent($headerComponent);
        $kernel->setComponent($userAgent);
        $kernel->setComponent($rdns);
        
        // By default, it will block this session because of no common header information

        $kernel->setIp('8.8.8.8');
        $result = $kernel->run();
        $this->assertSame(Enum::RESPONSE_DENY, $result);

        // Check trusted bots.

        // BING
        $_SERVER['HTTP_USER_AGENT'] = 'Mozilla/5.0 (compatible; bingbot/2.0; +http://www.bing.com/bingbot.htm)';
        $this->refreshRequest();

        $kernel = $this->getKernelInstance($driver);
        $kernel->setComponent(new \Shieldon\Firewall\Component\TrustedBot());
        $kernel->setIp('40.77.169.1', true);
   
        $result = $kernel->run();
        $this->assertSame(Enum::RESPONSE_ALLOW, $result);

        // Code coverage for - // is no more needed for that IP.
        $result = $kernel->run();
        $this->assertSame(Enum::RESPONSE_ALLOW, $result);

        // GOOGLE
        $_SERVER['HTTP_USER_AGENT'] = 'Googlebot/2.1 (+http://www.google.com/bot.html)';
        $this->refreshRequest();

        $kernel = $this->getKernelInstance($driver);
        $kernel->setComponent(new \Shieldon\Firewall\Component\TrustedBot());
        $kernel->setIp('66.249.66.1', true);
        $result = $kernel->run();
        $this->assertSame(Enum::RESPONSE_ALLOW, $result);

        // Code coverage for - // is no more needed for that IP.
        $result = $kernel->run();
        $this->assertSame(Enum::RESPONSE_ALLOW, $result);

        // YAHOO
        // phpcs:ignore
        $_SERVER['HTTP_USER_AGENT'] = 'Mozilla/5.0 (compatible; Yahoo! Slurp; http://help.yahoo.com/help/us/ysearch/slurp)';
        $this->refreshRequest();

        $kernel = $this->getKernelInstance($driver);
        $kernel->setComponent(new \Shieldon\Firewall\Component\TrustedBot());
        $kernel->setIp('8.12.144.1', true);
        $result = $kernel->run();
        $this->assertSame(Enum::RESPONSE_ALLOW, $result);

        // Code coverage for - // is no more needed for that IP.
        $result = $kernel->run();
        $this->assertSame(Enum::RESPONSE_ALLOW, $result);

        // OTHER
        $_SERVER['HTTP_USER_AGENT'] = 'Mozilla/5.0 (compatible; YandexBot/3.0; +http://yandex.com/bots)';
        $this->refreshRequest();

        $kernel = $this->getKernelInstance($driver);
        $kernel->setComponent(new \Shieldon\Firewall\Component\TrustedBot());
        $kernel->setIp('100.43.90.1', true);
        $result = $kernel->run();
        $this->assertSame(Enum::RESPONSE_ALLOW, $result);

        // Code coverage for - // is no more needed for that IP.
        $result = $kernel->run();
        $this->assertSame(Enum::RESPONSE_ALLOW, $result);

        $kernel = $this->getKernelInstance($driver);
        $kernel->disableFilters();
        $result = $kernel->run();
        $this->assertSame(Enum::RESPONSE_ALLOW, $result);
    }

    public function testGetSessionCount($driver = 'sqlite')
    {
        $kernel = $this->getKernelInstance($driver);
        $kernel->driver->rebuild();
        $kernel->disableFilters();

        $reflection = new \ReflectionObject($kernel);
        $methodSetSessionId = $reflection->getMethod('setSessionId');
        $methodSetSessionId->setAccessible(true);

        $kernel->limitSession(100, 3600);

        for ($i = 1; $i <= 10; $i++) {
            $kernel->setIp(implode('.', [rand(1, 255), rand(1, 255), rand(1, 255), rand(1, 255)]));
            $methodSetSessionId->invokeArgs($kernel, [md5(date('YmdHis') . mt_rand(1, 999999))]);
            $kernel->run();
        }

        // Get how many people online.
        $sessionCount = $kernel->getSessionCount();

        $this->assertSame($sessionCount, 10);
    }

    public function testGetJavascript()
    {
        $kernel = new \Shieldon\Firewall\Kernel();
        $js = $kernel->getJavascript();

        if (!empty($js)) {
            $this->assertTrue(true);
        } else {
            $this->assertTrue(false);
        }
    }

    public function testDisableFiltering()
    {
        $kernel = new \Shieldon\Firewall\Kernel();
        $kernel->disableFilters();
        $reflection = new \ReflectionObject($kernel);
        $t = $reflection->getProperty('filterStatus');
        $t->setAccessible(true);
        $filterStatus = $t->getValue($kernel);

        $this->assertFalse($filterStatus['frequency']);
        $this->assertFalse($filterStatus['referer']);
        $this->assertFalse($filterStatus['cookie']);
        $this->assertFalse($filterStatus['session']);
    }

    public function testIPv6($driver = 'sqlite')
    {
        $kernel = $this->getKernelInstance($driver);
        $kernel->driver->rebuild();
        $kernel->setIp('0:0:0:0:0:ffff:c0a8:5f01');
        $result = $kernel->run();

        $ipDetail = $kernel->driver->get('0:0:0:0:0:ffff:c0a8:5f01', 'filter');

        $this->assertSame($ipDetail['ip'], '0:0:0:0:0:ffff:c0a8:5f01');
    }

    /***********************************************
     * File Driver
     ***********************************************/

    public function testDetect_fileDriver_filterFrequency()
    {
        $this->testDetectByFilterFrequency('file');
    }

    public function testDetect_fileDriver_filterReferer()
    {
        $this->testDetectByFilterReferer('file');
    }

    public function testDetect_fileDriver_filterCookie()
    {
        $this->testDetectByFilterCookie('file');
    }

    public function testDetect_fileDriver_flagChecks()
    {
        $this->testResetFilterFlagChecks('file');
    }

    public function testDetect_fileDriver_filterSession()
    {
        $this->testDetectByFilterSession('file');
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

    public function testDetect_mysqlDriver_filterFrequency()
    {
        $this->testDetectByFilterFrequency('mysql');
    }

    public function testDetect_mysqlDriver_filterSession()
    {
        $this->testDetectByFilterSession('mysql');
    }

    public function testDetect_mysqlDriver_filterCookie()
    {
        $this->testDetectByFilterCookie('mysql');
    }

    public function testDetect_mysqlDriver_flagChecks()
    {
        $this->testResetFilterFlagChecks('mysql');
    }

    public function testDetect_mysqlDriver_filterReferer()
    {
        $this->testDetectByFilterReferer('mysql');
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

    public function testDetect_redisDriver_filterFrequency()
    {
        $this->testDetectByFilterFrequency('redis');
    }

    public function testDetect_redisDriver_filterSession()
    {
        $this->testDetectByFilterSession('redis');
    }

    public function testDetect_redisDriver_filterReferer()
    {
        $this->testDetectByFilterReferer('redis');
    }

    public function testDetect_redisDriver_filterCookie()
    {
        $this->testDetectByFilterCookie('redis');
    }

    public function testDetect_redisDriver()
    {
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

    public function testSetMessenger()
    {
        $kernel = new \Shieldon\Firewall\Kernel();

        $telegram = new \Shieldon\Messenger\Telegram('test', 'test');

        $kernel->setMessenger($telegram);
    }

    public function testSetDialog()
    {
        $kernel = new \Shieldon\Firewall\Kernel();

        $kernel->setDialog([]);
    }

    public function testsetExcludedList()
    {
        $kernel = new \Shieldon\Firewall\Kernel();

        $kernel->setExcludedList([]);
    }

    public function testIgnoreExcludedUrls()
    {
        $_SERVER['REQUEST_URI'] = '/ignore-this-url/index.html';
        $this->refreshRequest();

        $kernel = $this->getKernelInstance();
        $kernel->disableFilters();
        
        $kernel->setExcludedList([
            '/ignore-this-url/index.html'
        ]);

        $result = $kernel->run();

        $this->assertEquals(1, $result);
    }

    public function testSetClosure()
    {
        $kernel = new \Shieldon\Firewall\Kernel();

        $kernel->setClosure('key', function () {
            return true;
        });
    }

    public function testManagedBy()
    {
        $kernel = new \Shieldon\Firewall\Kernel();

        $kernel->managedBy('demo');
    }

    /***********************************************
     * Test for building bridge to Iptable
     ***********************************************/

    public function testDenyAttempts()
    {
        $kernel = $this->getKernelInstance('file');

        //$_SERVER['HTTP_USER_AGENT'] = 'google';
        // phpcs:ignore
        $_SERVER['HTTP_USER_AGENT'] = 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/70.0.3538.77 Safari/537.36';

        $kernel->setComponent(new \Shieldon\Firewall\Component\TrustedBot());
        $kernel->setComponent(new \Shieldon\Firewall\Component\Ip());
        $kernel->setComponent(new \Shieldon\Firewall\Component\UserAgent());
        $kernel->setComponent(new \Shieldon\Firewall\Component\Rdns());
        $kernel->setMessenger(new \Shieldon\FirewallTest\Mock\MockMessenger());

        $kernel->setChannel('test_shieldon_deny_attempt');
        $kernel->driver->rebuild();

        $kernel->setProperty('deny_attempt_enable', [
            'data_circle' => true,
            'system_firewall' => true,
        ]);

        $kernel->setProperty('deny_attempt_notify', [
            'data_circle' => true,
            'system_firewall' => true,
        ]);

        $kernel->setProperty('deny_attempt_buffer', [
            'data_circle' => 2,
            'system_firewall' => 2,
        ]);

        $kernel->setProperty('reset_attempt_counter', 5);

        // Test for IPv4 and IPv6.
        foreach (['127.0.1.1', '2607:f0d0:1002:51::4'] as $ip) {
            $kernel->setIp($ip);

            $result = $kernel->run();
            $this->assertEquals($result, Enum::RESPONSE_ALLOW);
    
            $result = $kernel->run();
            $this->assertEquals($result, Enum::RESPONSE_ALLOW);
    
            $result = $kernel->run();
            $this->assertEquals($result, Enum::RESPONSE_TEMPORARILY_DENY);
    
            $result = $kernel->run();
            $this->assertEquals($result, Enum::RESPONSE_TEMPORARILY_DENY);
    
            $result = $kernel->run();
            $this->assertEquals($result, Enum::RESPONSE_TEMPORARILY_DENY);
    
            $result = $kernel->run();
            $this->assertEquals($result, Enum::RESPONSE_DENY);
    
            $result = $kernel->run();
            $this->assertEquals($result, Enum::RESPONSE_DENY);
    
            $result = $kernel->run();
            $this->assertEquals($result, Enum::RESPONSE_DENY);
        }

        // Test for IPv4 and IPv6.
        foreach (['127.0.1.2', '2607:f0d0:1002:52::4'] as $ip) {
            $kernel->setIp($ip);

            $result = $kernel->run();
            $this->assertEquals($result, Enum::RESPONSE_ALLOW);
    
            $result = $kernel->run();
            $this->assertEquals($result, Enum::RESPONSE_ALLOW);
    
            $result = $kernel->run();
            $this->assertEquals($result, Enum::RESPONSE_TEMPORARILY_DENY);

            sleep(7);
    
            $result = $kernel->run();
            $this->assertEquals($result, Enum::RESPONSE_TEMPORARILY_DENY);
    
            $result = $kernel->run();
            $this->assertEquals($result, Enum::RESPONSE_TEMPORARILY_DENY);

            $result = $kernel->run();
            $this->assertEquals($result, Enum::RESPONSE_TEMPORARILY_DENY);
    
            $result = $kernel->run();
            $this->assertEquals($result, Enum::RESPONSE_DENY);
    
            $result = $kernel->run();
            $this->assertEquals($result, Enum::RESPONSE_DENY);
        }
    }

    public function testFakeTrustedBot()
    {
        $_SERVER['HTTP_USER_AGENT'] = 'google';

        $kernel = $this->getKernelInstance();
        $kernel->setComponent(new \Shieldon\Firewall\Component\TrustedBot());
        $kernel->disableFilters();
        $result = $kernel->run();

        $this->assertSame(Enum::RESPONSE_DENY, $result);
    }

    public function testSetAndGetTemplate()
    {
        $kernel = new \Shieldon\Firewall\Kernel();
        $kernel->setTemplateDirectory(BOOTSTRAP_DIR . '/../templates/frontend');

        $reflection = new \ReflectionObject($kernel);
        $methodGetTemplate = $reflection->getMethod('getTemplate');
        $methodGetTemplate->setAccessible(true);
        $tpl = $methodGetTemplate->invokeArgs($kernel, ['captcha']);

        $this->assertSame($tpl, BOOTSTRAP_DIR . '/../templates/frontend/captcha.php');

        $this->expectException(\RuntimeException::class);
        $tpl = $methodGetTemplate->invokeArgs($kernel, ['captcha2']);
    }

    public function testThrowEexceptionSpecificTemplateFileNotExist()
    {
        $this->expectException(\RuntimeException::class);

        $kernel = new \Shieldon\Firewall\Kernel();
        $kernel->setTemplateDirectory(BOOTSTRAP_DIR . '/../templates/frontend');

        $reflection = new \ReflectionObject($kernel);
        $methodGetTemplate = $reflection->getMethod('getTemplate');
        $methodGetTemplate->setAccessible(true);
  
        $tpl = $methodGetTemplate->invokeArgs($kernel, ['captcha2']);
    }

    public function testThrowEexceptionWhenNoDriver()
    {
        $this->expectException(\RuntimeException::class);
        $kernel = new \Shieldon\Firewall\Kernel();
        $kernel->run();
    }

    public function testThrowEexceptionWhenTemplateDirectoryNotExist()
    {
        $this->expectException(\InvalidArgumentException::class);
        $kernel = new \Shieldon\Firewall\Kernel();
        $kernel->setTemplateDirectory('/not-exists');
        $kernel->run();
    }

    public function testThrowEexceptionWhenTemplateFileNotExist()
    {
        $this->expectException(\RuntimeException::class);
        $kernel = new \Shieldon\Firewall\Kernel();
        $kernel->setTemplateDirectory('/');
        $kernel->run();
    }
}
