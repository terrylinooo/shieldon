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

namespace Shieldon\FirewallTest\Driver;

class FileDriverTest extends \Shieldon\FirewallTest\ShieldonTestCase
{
    public function test__construct()
    {
        try {
            $file = new \Shieldon\Firewall\Driver\FileDriver(BOOTSTRAP_DIR . '/../tmp/shieldon');
        } catch (\Exception $e) {
            $this->assertTrue(false);
        }

        if ($file instanceof FileDriver) {
            $this->assertTrue(true);
        }
    }

    public function testDoFetchAll()
    {
        $fileDriver = new \Shieldon\Firewall\Driver\FileDriver(BOOTSTRAP_DIR . '/../tmp/shieldon');
        $fileDriver->rebuild();

        $reflection = new \ReflectionObject($fileDriver);
        $methodDoFetchAll = $reflection->getMethod('doFetchAll');
        $methodDoFetchAll->setAccessible(true);

        $resultA = $methodDoFetchAll->invokeArgs($fileDriver, ['filter']);
        $resultB = $methodDoFetchAll->invokeArgs($fileDriver, ['rule']);
        $resultC = $methodDoFetchAll->invokeArgs($fileDriver, ['session']);

        $this->assertSame($resultA, []);
        $this->assertSame($resultB, []);
        $this->assertSame($resultC, []);

        $data = ['tragedy' => '19890604'];
    
        $fileDriver->save('19.89.6.4', $data, 'filter');
        $resultD = $methodDoFetchAll->invokeArgs($fileDriver, ['filter']);

        foreach ($resultD as $result) {
            $this->assertSame($result['log_data'], $data);
        }
    }

    public function testCheckExist()
    {
        $fileDriver = new \Shieldon\Firewall\Driver\FileDriver(BOOTSTRAP_DIR . '/../tmp/shieldon');
        $fileDriver->rebuild();

        $reflection = new \ReflectionObject($fileDriver);
        $methodCheckExist = $reflection->getMethod('checkExist');
        $methodCheckExist->setAccessible(true);

        $resultA = $methodCheckExist->invokeArgs($fileDriver, ['64.64.64.64', 'filter']);
        $resultB = $methodCheckExist->invokeArgs($fileDriver, ['64.64.64.64', 'rule']);
        $resultC = $methodCheckExist->invokeArgs($fileDriver, ['8a7d7ba288ca0f0ea1ecf975b026e8e1', 'session']);

        $this->assertSame($resultA, false);
        $this->assertSame($resultB, false);
        $this->assertSame($resultC, false);
    }

    public function testDoSave()
    {
        $fileDriver = new \Shieldon\Firewall\Driver\FileDriver(BOOTSTRAP_DIR . '/../tmp/shieldon');
        $fileDriver->rebuild();

        $reflection = new \ReflectionObject($fileDriver);
        $methodDoSave = $reflection->getMethod('doSave');
        $methodDoSave->setAccessible(true);

        $data = ['revolution' => 'freedom'];

        $resultA = $methodDoSave->invokeArgs($fileDriver, ['19.89.4.15', $data, 'filter']);
        $resultB = $methodDoSave->invokeArgs($fileDriver, ['19.89.6.4', $data, 'rule']);
        $resultC = $methodDoSave->invokeArgs($fileDriver, ['8a7d7ba288ca0f0ea1ecf975b026e8e1', $data, 'session']);

        $this->assertSame($resultA, true);
        $this->assertSame($resultB, true);
        $this->assertSame($resultC, true);

        $reflection = new \ReflectionObject($fileDriver);
        $methodCheckExist = $reflection->getMethod('checkExist');
        $methodCheckExist->setAccessible(true);

        $resultA = $methodCheckExist->invokeArgs($fileDriver, ['19.89.4.15', 'filter']);
        $resultB = $methodCheckExist->invokeArgs($fileDriver, ['19.89.6.4', 'rule']);
        $resultC = $methodCheckExist->invokeArgs($fileDriver, ['8a7d7ba288ca0f0ea1ecf975b026e8e1', 'session']);

        $this->assertSame($resultA, true);
        $this->assertSame($resultB, true);
        $this->assertSame($resultC, true);
    }

    public function testDoDelete()
    {
        $fileDriver = new \Shieldon\Firewall\Driver\FileDriver(BOOTSTRAP_DIR . '/../tmp/shieldon');
        $fileDriver->rebuild();

        $reflection = new \ReflectionObject($fileDriver);
        $methodDoDelete = $reflection->getMethod('doDelete');
        $methodDoDelete->setAccessible(true);

        $resultA = $methodDoDelete->invokeArgs($fileDriver, ['19.89.6.4', 'forgotten']);
        $this->assertSame($resultA, false);
    }

    public function testGetFilename()
    {
        $fileDriver = new \Shieldon\Firewall\Driver\FileDriver(BOOTSTRAP_DIR . '/../tmp/shieldon');

        $reflection = new \ReflectionObject($fileDriver);
        $methodGetFilename = $reflection->getMethod('getFilename');
        $methodGetFilename->setAccessible(true);

        $result = $methodGetFilename->invokeArgs($fileDriver, ['19.89.6.4', 'forgotten']);
        $this->assertSame($result, '');
    }

    public function testGetDirectory()
    {
        $fileDriver = new \Shieldon\Firewall\Driver\FileDriver(BOOTSTRAP_DIR . '/../tmp/shieldon');

        $reflection = new \ReflectionObject($fileDriver);
        $methodGetFilename = $reflection->getMethod('getDirectory');
        $methodGetFilename->setAccessible(true);

        $result = $methodGetFilename->invokeArgs($fileDriver, ['no_exist_type']);
        $this->assertSame($result, '');
    }

    public function testCreateDirectory()
    {
        if (file_exists(BOOTSTRAP_DIR . '/../tmp/test_file_driver')) {
            $dir = BOOTSTRAP_DIR . '/../tmp/test_file_driver';
            if (is_dir($dir)) {
                $it = new \RecursiveDirectoryIterator($dir, \RecursiveDirectoryIterator::SKIP_DOTS);
                $files = new \RecursiveIteratorIterator($it, \RecursiveIteratorIterator::CHILD_FIRST);
    
                foreach ($files as $file) {
                    if ($file->isDir()) {
                        rmdir($file->getRealPath());
                    } else {
                        unlink($file->getRealPath());
                    }
                }
                unset($it, $files);

                if (is_dir($dir)) {
                    rmdir($dir);
                }
            }
        }

        $fileDriver = new \Shieldon\Firewall\Driver\FileDriver(BOOTSTRAP_DIR . '/../tmp/test_file_driver');

        $reflection = new \ReflectionObject($fileDriver);
        $methodCreateDirectory = $reflection->getMethod('createDirectory');
        $methodCreateDirectory->setAccessible(true);

        $result = $methodCreateDirectory->invokeArgs($fileDriver, []);

        $this->assertTrue($result);

        $fileDriver->rebuild();

        $result = $methodCreateDirectory->invokeArgs($fileDriver, []);

        $this->assertFalse($result);
    }

    public function testCheckDirectory()
    {
        $fileDriver = new \Shieldon\Firewall\Driver\FileDriver(BOOTSTRAP_DIR . '/../tmp/shieldon');
        $fileDriver->rebuild();

        $reflection = new \ReflectionObject($fileDriver);
        
        $methodCheckDirectory = $reflection->getMethod('checkDirectory');
        $methodCheckDirectory->setAccessible(true);

        $result = $methodCheckDirectory->invokeArgs($fileDriver, []);
        $this->assertTrue($result);

        $fileDriver = new \Shieldon\Firewall\Driver\FileDriver(BOOTSTRAP_DIR . '/../tmp/shieldon_not_exist');
        $reflection = new \ReflectionObject($fileDriver);
        $methodCheckDirectory = $reflection->getMethod('checkDirectory');
        $methodCheckDirectory->setAccessible(true);

        // Test exception.
        $this->expectException(\RuntimeException::class);
        $result = $methodCheckDirectory->invokeArgs($fileDriver, []);
    }
}
