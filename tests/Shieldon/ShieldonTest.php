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

        $shieldon->setComponent(new \Shieldon\Component\Ip());
        $shieldon->setComponent(new \Shieldon\Component\UserAgent());
        $shieldon->setComponent(new \Shieldon\Component\TrustedBot());
        $shieldon->setComponent(new \Shieldon\Component\RDns());

        $shieldon->setChannel('test_shieldon_detect_s');

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
        
        $shieldon->setComponent(new \Shieldon\Component\Ip());
        $shieldon->setComponent(new \Shieldon\Component\UserAgent());
        $shieldon->setComponent(new \Shieldon\Component\TrustedBot());
        $shieldon->setComponent(new \Shieldon\Component\RDns());

        $shieldon->setChannel('test_shieldon_detect');
        $driver->init();

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
}