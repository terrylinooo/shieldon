<?php declare(strict_types=1);
/*
 * This file is part of the Shieldon package.
 *
 * (c) Terry L. <contact@terryl.in>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Shieldon\Driver;

class RedisDriverTest extends \PHPUnit\Framework\TestCase
{
    public function test__construct()
    {
        $redisInstance = new \Redis();
        $redisInstance->connect('127.0.0.1', 6379); 

        try {
            $redis = new RedisDriver($redisInstance);
        } catch(\Exception $e) {
            $this->assertTrue(false);
        }

        if ($redis instanceof RedisDriver) {
            $this->assertTrue(true);
        }
    }

    public function testDoFetchAll()
    {
        $redisInstance = new \Redis();
        $redisInstance->connect('127.0.0.1', 6379); 
        $redisDriver = new RedisDriver($redisInstance);

        $redisDriver->rebuild();

        $reflection = new \ReflectionObject($redisDriver);
        $methodDoFetchAll = $reflection->getMethod('doFetchAll');
        $methodDoFetchAll->setAccessible(true);

        $resultA = $methodDoFetchAll->invokeArgs($redisDriver, ['log']);
        $resultB = $methodDoFetchAll->invokeArgs($redisDriver, ['rule']);
        $resultC = $methodDoFetchAll->invokeArgs($redisDriver, ['session']);

        $this->assertSame($resultA, []);
        $this->assertSame($resultB, []);
        $this->assertSame($resultC, []);

        $data = ['tragedy' => '19890604'];
    
        $redisDriver->save('19.89.6.4', $data, 'log');
        $resultD = $methodDoFetchAll->invokeArgs($redisDriver, ['log']);

        $this->assertSame($resultD['19.89.6.4']['log_data'], $data);
    }

    public function testCheckExist()
    {
        $redisInstance = new \Redis();
        $redisInstance->connect('127.0.0.1', 6379); 
        $redisDriver = new RedisDriver($redisInstance);

        $redisDriver->rebuild();

        $reflection = new \ReflectionObject($redisDriver);
        $methodCheckExist = $reflection->getMethod('checkExist');
        $methodCheckExist->setAccessible(true);

        $resultA = $methodCheckExist->invokeArgs($redisDriver, ['64.64.64.64', 'log']);
        $resultB = $methodCheckExist->invokeArgs($redisDriver, ['64.64.64.64', 'rule']);
        $resultC = $methodCheckExist->invokeArgs($redisDriver, ['8a7d7ba288ca0f0ea1ecf975b026e8e1', 'session']);

        $this->assertSame($resultA, false);
        $this->assertSame($resultB, false);
        $this->assertSame($resultC, false);
    }

    public function testDoSave()
    {
        $redisInstance = new \Redis();
        $redisInstance->connect('127.0.0.1', 6379); 
        $redisDriver = new RedisDriver($redisInstance);

        $redisDriver->rebuild();

        $reflection = new \ReflectionObject($redisDriver);
        $methodDoSave = $reflection->getMethod('doSave');
        $methodDoSave->setAccessible(true);

        $data = ['revolution' => 'freedom'];
        $expire = 3;

        $resultA = $methodDoSave->invokeArgs($redisDriver, ['19.89.4.15', $data, 'log', $expire]);
        $resultB = $methodDoSave->invokeArgs($redisDriver, ['19.89.6.4', $data, 'rule', $expire]);
        $resultC = $methodDoSave->invokeArgs($redisDriver, ['8a7d7ba288ca0f0ea1ecf975b026e8e1', $data, 'session', $expire]);

        $this->assertSame($resultA, true);
        $this->assertSame($resultB, true);
        $this->assertSame($resultC, true);

        $reflection = new \ReflectionObject($redisDriver);
        $methodCheckExist = $reflection->getMethod('checkExist');
        $methodCheckExist->setAccessible(true);

        $resultA = $methodCheckExist->invokeArgs($redisDriver, ['19.89.4.15', 'log']);
        $resultB = $methodCheckExist->invokeArgs($redisDriver, ['19.89.6.4', 'rule']);
        $resultC = $methodCheckExist->invokeArgs($redisDriver, ['8a7d7ba288ca0f0ea1ecf975b026e8e1', 'session']);

        $this->assertSame($resultA, true);
        $this->assertSame($resultB, true);
        $this->assertSame($resultC, true);

        sleep(4);

        $resultA = $methodCheckExist->invokeArgs($redisDriver, ['19.89.4.15', 'log']);
        $resultB = $methodCheckExist->invokeArgs($redisDriver, ['19.89.6.4', 'rule']);
        $resultC = $methodCheckExist->invokeArgs($redisDriver, ['8a7d7ba288ca0f0ea1ecf975b026e8e1', 'session']);

        $this->assertSame($resultA, false);
        $this->assertSame($resultB, false);
        $this->assertSame($resultC, false);
    }

    public function testDoDelete()
    {
        $redisInstance = new \Redis();
        $redisInstance->connect('127.0.0.1', 6379); 
        $redisDriver = new RedisDriver($redisInstance);

        $redisDriver->rebuild();

        $reflection = new \ReflectionObject($redisDriver);
        $methodDoDelete = $reflection->getMethod('doDelete');
        $methodDoDelete->setAccessible(true);

        $resultA = $methodDoDelete->invokeArgs($redisDriver, ['19.89.6.4', 'forgotten']);
        $this->assertSame($resultA, false);
    }

    public function testGetKeyName()
    {
        $redisInstance = new \Redis();
        $redisInstance->connect('127.0.0.1', 6379); 
        $redisDriver = new RedisDriver($redisInstance);

        $reflection = new \ReflectionObject($redisDriver);
        $methodGetKeyName = $reflection->getMethod('getKeyName');
        $methodGetKeyName->setAccessible(true);

        $result = $methodGetKeyName->invokeArgs($redisDriver, ['19.89.6.4', 'democracy']);
        $this->assertSame($result, '');
    }

    public function testGetNamespace()
    {
        $redisInstance = new \Redis();
        $redisInstance->connect('127.0.0.1', 6379); 
        $redisDriver = new RedisDriver($redisInstance);

        $reflection = new \ReflectionObject($redisDriver);
        $methodGetNamespace = $reflection->getMethod('getNamespace');
        $methodGetNamespace->setAccessible(true);

        $result = $methodGetNamespace->invokeArgs($redisDriver, ['democracy']);
        $this->assertSame($result, '');
    }
}