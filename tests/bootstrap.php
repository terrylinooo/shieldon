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

date_default_timezone_set('UTC');

define('BOOTSTRAP_DIR', __DIR__);
define('NO_MOCK_ENV', true);

use Shieldon\Firewall\Utils\Container;
use Shieldon\Firewall\HttpFactory;
use Shieldon\Firewall\Helpers;

include __DIR__ . '/../autoload.php';
include __DIR__ . '/../vendor/autoload.php';
include __DIR__ . '/../vendor/shieldon/messenger/autoload.php';
include __DIR__ . '/Mock/MockMessenger.php';

/**
 * Create a writable directrory for unit testing.
 *
 * @param string $filename File name.
 * @return string The file's path.
 */
function save_testing_file($filename, $dir = '')
{
    if ($dir === '') {
        $dir = BOOTSTRAP_DIR . '/../tmp/' . $dir;
    } else {
        $dir = BOOTSTRAP_DIR . '/../tmp';
    }

    if (! is_dir($dir)) {
        $originalUmask = umask(0);
        $result = @mkdir($dir, 0777, true);
        umask($originalUmask);
    }
    return $dir . '/' . $filename;
}

// Mock for PHPUnit.
if (! isset($_SERVER['REMOTE_ADDR'])) {
    $_SERVER['REMOTE_ADDR'] = '127.0.0.127';
}

if (! isset($_SERVER['HTTP_CF_CONNECTING_IP'])) {
    $_SERVER['HTTP_CF_CONNECTING_IP'] = '127.0.0.128';
}

if (! isset($_SERVER['HTTP_X_FORWARDED_HOST'])) {
    $_SERVER['HTTP_X_FORWARDED_HOST'] = '127.0.0.129';
}

if (! isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
    $_SERVER['HTTP_X_FORWARDED_FOR'] = '127.0.0.130';
}

if (! isset($_SERVER['HTTPS'])) {
    $_SERVER['HTTPS'] = 'on';
}

if (! isset($_SERVER['REQUEST_URI'])) {
    $_SERVER['REQUEST_URI'] = '/';
}

if (! isset($_SERVER['HTTP_HOST'])) {
    $_SERVER['HTTP_HOST'] = 'shieldon.io';
}

/**
 * Create a Sheildon instance with specific driver.
 *
 * @param string $driver
 *
 * @return object
 */
function get_testing_shieldon_instance($driver = 'sqlite')
{
    $kernel = new \Shieldon\Firewall\Kernel();

    switch ($driver) {

        case 'file':
            $kernel->setDriver(new \Shieldon\Firewall\Driver\FileDriver(BOOTSTRAP_DIR . '/../tmp/shieldon'));
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

            $kernel->setDriver(new \Shieldon\Firewall\Driver\MysqlDriver($pdoInstance));
            break;

        case 'redis':
            $redisInstance = new \Redis();
            $redisInstance->connect('127.0.0.1', 6379); 
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
        default:
            $dbLocation = save_testing_file('shieldon_unittest.sqlite3');

            try {
                $pdoInstance = new \PDO('sqlite:' . $dbLocation);
                $kernel->setDriver(new \Shieldon\Firewall\Driver\SqliteDriver($pdoInstance));
            } catch(\PDOException $e) {
                throw $e->getMessage();
            }
  
            break;
    }

    return $kernel;
}

function rand_ip()
{
    return rand(1,255) . '.' . rand(1,255) . '.' . rand(1,255) . '.' . rand(1,255);
}

new Helpers();

function reload_request() 
{
    if (!empty($_POST)) {
        $_SERVER['CONTENT_TYPE'] = 'application/x-www-form-urlencoded';
        $_SERVER['REQUEST_METHOD'] = 'POST';
    }

    Container::set('request', HttpFactory::createRequest(), true);
    Container::set('response', HttpFactory::createResponse(), true);
    Container::set('session', HttpFactory::createSession(), true);
}

function print_cli_msg($text, $type = 'notice', $bg = false)
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