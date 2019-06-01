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

        $shieldon->setChannel('test_shieldon_detect_s');
        $shieldon->driver->rebuild();

        // Fake a IP for testing.
        $randomFakeIp = [rand(100, 200),rand(100, 200),rand(100, 200),rand(100, 200)];
        $ip = implode('.', $randomFakeIp);
        
        $shieldon->setIp($ip);

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

        return $result;
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
    }
}