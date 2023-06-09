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

use PHPUnit\Framework\TestCase;
use Shieldon\Firewall\Container;
use Shieldon\Firewall\HttpFactory;
use Shieldon\Firewall\Kernel\Enum;
use Shieldon\Firewall\Kernel;
use function Shieldon\Firewall\get_mock_session;

/**
 * The test case for Shieldon Firewall
 */
class ShieldonTestCase extends TestCase
{
    /**
     * Refresh the PSR-7 server request and response.
     * The session instance is also refreshed.
     *
     * @return void
     */
    public function refreshRequest(): void
    {
        if (!empty($_POST)) {
            $_SERVER['CONTENT_TYPE'] = 'application/x-www-form-urlencoded';
            $_SERVER['REQUEST_METHOD'] = 'POST';
        }
    
        Container::set('request', HttpFactory::createRequest(), true);
        Container::set('response', HttpFactory::createResponse(), true);
    }

    /**
     * Create a writable directrory for unit testing.
     *
     * @param  string $filename File name.
     * @return string $dir      The file's path.
     *
     * @return string
     */
    public function getWritableTestFilePath($filename, $dir = ''): string
    {
        if ($dir !== '') {
            $dir = BOOTSTRAP_DIR . '/../tmp/' . $dir;
        } else {
            $dir = BOOTSTRAP_DIR . '/../tmp';
        }

        if (!is_dir($dir)) {
            $originalUmask = umask(0);
            $result = mkdir($dir, 0777, true);
            umask($originalUmask);
        }
        return $dir . '/' . $filename;
    }

    /**
     * Create a Sheildon Kernel instance with specific driver.
     *
     * @param string $driver The type of the data driver
     *
     * @return Kernel
     */
    public function getKernelInstance($driver = 'sqlite'): Kernel
    {
        $kernel = new Kernel();

        switch ($driver) {
            case 'file':
                $kernel->setChannel('testsessionlimit');
                // phpcs:ignore
                $kernel->setDriver(new \Shieldon\Firewall\Driver\FileDriver(BOOTSTRAP_DIR . '/../tmp/shieldon/data_driver_file'));
                break;

            case 'mysql':
                $db = [
                    'host' => '127.0.0.1',
                    'dbname' => 'shieldon_unittest',
                    'user' => 'shieldon',
                    'pass' => 'taiwan',
                    'charset' => 'utf8',
                ];
                
                $pdoInstance = new \PDO(
                    'mysql:host=' . $db['host'] . ';dbname=' . $db['dbname'] . ';charset=' . $db['charset'],
                    $db['user'],
                    $db['pass']
                );

                $kernel->setChannel('testsessionlimit');
                $kernel->setDriver(new \Shieldon\Firewall\Driver\MysqlDriver($pdoInstance));
                break;

            case 'redis':
                $redisInstance = new \Redis();
                $redisInstance->connect('127.0.0.1', 6379);
                $kernel->setChannel('testsessionlimit');
                $kernel->setDriver(new \Shieldon\Firewall\Driver\RedisDriver($redisInstance));
                break;
            /*
            case 'memcache':
                try {
                    $memcacheInstance = new \Memcache();
                    $memcacheInstance->connect('127.0.0.1', 11211);
                } catch (\Exception $e1) {
                    try {
                        $memcacheInstance = new \Memcache();
                        $memcacheInstance->connect('192.168.95.27', 11211);
                    } catch (\Exception $e2) {
                        die('Cannot connect to Memcache server.');
                    }
                }
                $kernel->setDriver(new \Shieldon\Firewall\Driver\MemcacheDriver($memcacheInstance));
                break;

            case 'mongodb':
                try {
                    $mongoInstance = new \MongoClient('mongodb://127.0.0.1');
                } catch (\Exception $e1) {
                    try {
                        $mongoInstance = new \MongoClient('mongodb://192.168.95.27');
                    } catch (\Exception $e2) {
                        die('Cannot connect to MongoDB.');
                    }
                }
                $kernel->setDriver(new \Shieldon\Firewall\Driver\MongoDriver($mongoInstance));
                break;
            */
            case 'sqlite':
                $dbLocation = $this->getWritableTestFilePath(
                    'shieldon_unittest.sqlite3',
                    'shieldon/data_driver_sqlite'
                );

                try {
                    $pdoInstance = new \PDO('sqlite:' . $dbLocation);
                    $kernel->setChannel('testsessionlimit');
                    $driver = new \Shieldon\Firewall\Driver\SqliteDriver($pdoInstance);
                    $kernel->setDriver($driver);
                } catch (\PDOException $e) {
                    throw $e->getMessage();
                }
    
                break;
            default:
        }

        return $kernel;
    }

    /**
     * Print log in console.
     *
     * @param string $text Message body.
     * @param string $type Message type.
     * @param bool   $bg   Background color.
     *
     * @return void
     */
    public function console(string $text, string $type = 'notice', $bg = false)
    {
        $reset = "\e[0m";
    
        // Front color
        $black = "\e[30m";
        $red = "\e[31m";
        $green = "\e[32m";
        $yellow = "\e[33m";
        $blue = "\e[34m";
        
        // Background color
        $_red = "\e[41m";
        $_green = "\e[42m";
        $_yellow = "\e[43m";
        $_blue = "\e[44m";
    
        print "\n\n";
    
        if (!$bg) {
            switch ($type) {
                case 'error':
                    print $red . $text . $reset;
                    break;
        
                case 'info':
                    print $blue . $text . $reset;
                    break;
        
                case 'success':
                    print $green . $text . $reset;
                    break;
        
                case 'notice':
                default:
                    print $yellow . $text . $reset;
                    break;
            }
        } else {
            switch ($type) {
                case 'error':
                    print $_red . $black . $text . $reset;
                    break;
        
                case 'info':
                    print $_blue . $black . $text . $reset;
                    break;
        
                case 'success':
                    print $_green . $black . $text . $reset;
                    break;
        
                case 'notice':
                default:
                    print $_yellow . $black . $text . $reset;
                    break;
            }
        }
    
        print "\n";
    }

    /**
     * Get random IP address.
     *
     * @return string
     */
    public function getRandomIpAddress():string
    {
        return rand(1, 255) . '.' . rand(1, 255) . '.' . rand(1, 255) . '.' . rand(1, 255);
    }

    /**
     * Mock the user session for tests which need session to test.
     *
     * @param string|array $key
     * @param string       $value
     *
     * @return void
     */
    public function mockUserSession($key = '', $value = '')
    {
        $sessionId = '624689c34690a1d0a8c5658db66cf73d';
        $_COOKIE['_shieldon'] = $sessionId;

        $data['id'] = $sessionId;
        $data['ip'] = '192.168.95.1';
        $data['time'] = '1597028827';
        $data['microtimestamp'] = '159702882767804400';
        $data['parsed_data']['shieldon_ui_lang'] = 'en';
        $data['parsed_data']['shieldon_user_login'] = true;

        if (is_string($key) && $key !== '') {
            $data['parsed_data'][$key] = $value;
        }

        if (is_array($key)) {
            foreach ($key as $k => $v) {
                $data['parsed_data'][$k] = $v;
            }
        }

        $data['data'] = json_encode($data['parsed_data']);

        $json = json_encode($data);
        $dir = BOOTSTRAP_DIR . '/../tmp/shieldon/data_driver_file/shieldon_sessions';
        $file = $dir . '/' . $sessionId . '.json';

        $originalUmask = umask(0);

        if (!is_dir($dir)) {
            mkdir($dir, 0777, true);
        }

        umask($originalUmask);

        file_put_contents($file, $json);
    }

    /**
     * Mock Session.
     *
     * @return void
     */
    public function mockSession(): void
    {
        $sessionId = '624689c34690a1d0a8c5658db66cf73d';
        $_COOKIE['_shieldon'] = $sessionId;
        get_mock_session($sessionId);
    }
}
