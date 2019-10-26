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


class FileDriverTest extends \PHPUnit\Framework\TestCase
{
    public function test__construct()
    {
        try {
            $file = new FileDriver(BOOTSTRAP_DIR . '/../tmp/shieldon');
        } catch(\Exception $e) {
            $this->assertTrue(false);
        }

        if ($file instanceof FileDriver) {
            $this->assertTrue(true);
        }
    }

    public function testDoFetchAll()
    {
        $fileDriver = new \Shieldon\Driver\FileDriver(BOOTSTRAP_DIR . '/../tmp/shieldon');
        $fileDriver->rebuild();

        $reflection = new \ReflectionObject($fileDriver);
        $methodDoFetchAll = $reflection->getMethod('doFetchAll');
        $methodDoFetchAll->setAccessible(true);

        $resultA = $methodDoFetchAll->invokeArgs($fileDriver, ['filter_log']);
        $resultB = $methodDoFetchAll->invokeArgs($fileDriver, ['rule']);
        $resultC = $methodDoFetchAll->invokeArgs($fileDriver, ['session']);

        $this->assertSame($resultA, []);
        $this->assertSame($resultB, []);
        $this->assertSame($resultC, []);

        $data = ['tragedy' => '19890604'];
    
        $fileDriver->save('19.89.6.4', $data, 'filter_log');
        $resultD = $methodDoFetchAll->invokeArgs($fileDriver, ['filter_log']);

        foreach ($resultD as $result) {
            $this->assertSame($result['log_data'], $data);
        }    
    }

    public function testCheckExist()
    {
        $fileDriver = new \Shieldon\Driver\FileDriver(BOOTSTRAP_DIR . '/../tmp/shieldon');
        $fileDriver->rebuild();

        $reflection = new \ReflectionObject($fileDriver);
        $methodCheckExist = $reflection->getMethod('checkExist');
        $methodCheckExist->setAccessible(true);

        $resultA = $methodCheckExist->invokeArgs($fileDriver, ['64.64.64.64', 'filter_log']);
        $resultB = $methodCheckExist->invokeArgs($fileDriver, ['64.64.64.64', 'rule']);
        $resultC = $methodCheckExist->invokeArgs($fileDriver, ['8a7d7ba288ca0f0ea1ecf975b026e8e1', 'session']);

        $this->assertSame($resultA, false);
        $this->assertSame($resultB, false);
        $this->assertSame($resultC, false);
    }

    public function testDoSave()
    {
        $fileDriver = new \Shieldon\Driver\FileDriver(BOOTSTRAP_DIR . '/../tmp/shieldon');
        $fileDriver->rebuild();

        $reflection = new \ReflectionObject($fileDriver);
        $methodDoSave = $reflection->getMethod('doSave');
        $methodDoSave->setAccessible(true);

        $data = ['revolution' => 'freedom'];

        $resultA = $methodDoSave->invokeArgs($fileDriver, ['19.89.4.15', $data, 'filter_log']);
        $resultB = $methodDoSave->invokeArgs($fileDriver, ['19.89.6.4', $data, 'rule']);
        $resultC = $methodDoSave->invokeArgs($fileDriver, ['8a7d7ba288ca0f0ea1ecf975b026e8e1', $data, 'session']);

        $this->assertSame($resultA, true);
        $this->assertSame($resultB, true);
        $this->assertSame($resultC, true);

        $reflection = new \ReflectionObject($fileDriver);
        $methodCheckExist = $reflection->getMethod('checkExist');
        $methodCheckExist->setAccessible(true);

        $resultA = $methodCheckExist->invokeArgs($fileDriver, ['19.89.4.15', 'filter_log']);
        $resultB = $methodCheckExist->invokeArgs($fileDriver, ['19.89.6.4', 'rule']);
        $resultC = $methodCheckExist->invokeArgs($fileDriver, ['8a7d7ba288ca0f0ea1ecf975b026e8e1', 'session']);

        $this->assertSame($resultA, true);
        $this->assertSame($resultB, true);
        $this->assertSame($resultC, true);
    }

    public function testDoDelete()
    {
        $fileDriver = new \Shieldon\Driver\FileDriver(BOOTSTRAP_DIR . '/../tmp/shieldon');
        $fileDriver->rebuild();

        $reflection = new \ReflectionObject($fileDriver);
        $methodDoDelete = $reflection->getMethod('doDelete');
        $methodDoDelete->setAccessible(true);

        $resultA = $methodDoDelete->invokeArgs($fileDriver, ['19.89.6.4', 'forgotten']);
        $this->assertSame($resultA, false);
    }

    public function testGetFilename()
    {
        $fileDriver = new \Shieldon\Driver\FileDriver(BOOTSTRAP_DIR . '/../tmp/shieldon');

        $reflection = new \ReflectionObject($fileDriver);
        $methodGetFilename = $reflection->getMethod('getFilename');
        $methodGetFilename->setAccessible(true);

        $result = $methodGetFilename->invokeArgs($fileDriver, ['19.89.6.4', 'forgotten']);
        $this->assertSame($result, '');
    }

    public function testGetDirectory()
    {
        $fileDriver = new \Shieldon\Driver\FileDriver(BOOTSTRAP_DIR . '/../tmp/shieldon');

        $reflection = new \ReflectionObject($fileDriver);
        $methodGetFilename = $reflection->getMethod('getDirectory');
        $methodGetFilename->setAccessible(true);

        $result = $methodGetFilename->invokeArgs($fileDriver, ['no_exist_type']);
        $this->assertSame($result, '');
    }

    public function testCreateDirectory()
    {
        $fileDriver = new \Shieldon\Driver\FileDriver(BOOTSTRAP_DIR . '/../tmp/shieldon');
        $fileDriver->rebuild();

        $reflection = new \ReflectionObject($fileDriver);
        $methodCreateDirectory = $reflection->getMethod('createDirectory');
        $methodCreateDirectory->setAccessible(true);

        $result = $methodCreateDirectory->invokeArgs($fileDriver, []);

        $this->assertTrue($result);
    }

    public function testCheckDirectory()
    {
        $fileDriver = new \Shieldon\Driver\FileDriver(BOOTSTRAP_DIR . '/../tmp/shieldon');
        $fileDriver->rebuild();

        $reflection = new \ReflectionObject($fileDriver);
        
        $methodCheckDirectory = $reflection->getMethod('checkDirectory');
        $methodCheckDirectory->setAccessible(true);

        $result = $methodCheckDirectory->invokeArgs($fileDriver, []);
        $this->assertTrue($result);

        $fileDriver = new \Shieldon\Driver\FileDriver(BOOTSTRAP_DIR . '/../tmp/shieldon_not_exist');
        $reflection = new \ReflectionObject($fileDriver);
        $methodCheckDirectory = $reflection->getMethod('checkDirectory');
        $methodCheckDirectory->setAccessible(true);

        // Test exception.
        $this->expectException(\RuntimeException::class);
        $result = $methodCheckDirectory->invokeArgs($fileDriver, []);
    }
}