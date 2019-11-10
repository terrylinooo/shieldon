<?php declare(strict_types=1);
/*
 * This file is part of the Shieldon package.
 *
 * (c) Terry L. <contact@terryl.in>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

date_default_timezone_set('UTC');

define('BOOTSTRAP_DIR', __DIR__);

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
    $shieldon = new \Shieldon\Shieldon();

    switch ($driver) {

        case 'file':
            $shieldon->setDriver(new \Shieldon\Driver\FileDriver(BOOTSTRAP_DIR . '/../tmp/shieldon'));
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

            $shieldon->setDriver(new \Shieldon\Driver\MysqlDriver($pdoInstance));
            break;

        case 'redis':
            $redisInstance = new \Redis();
            $redisInstance->connect('127.0.0.1', 6379); 
            $shieldon->setDriver(new \Shieldon\Driver\RedisDriver($redisInstance));
            break;

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
            $shieldon->setDriver(new \Shieldon\Driver\MemcacheDriver($memcacheInstance));
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
            $shieldon->setDriver(new \Shieldon\Driver\MongoDriver($mongoInstance));
            break;

        case 'sqlite':
        default:
            $dbLocation = save_testing_file('shieldon_unittest.sqlite3');

            try {
                $pdoInstance = new \PDO('sqlite:' . $dbLocation);
                $shieldon->setDriver(new \Shieldon\Driver\SqliteDriver($pdoInstance));
            } catch(\PDOException $e) {
                throw $e->getMessage();
            }
  
            break;
    }

    return $shieldon;
}

function rand_ip()
{
    return rand(1,255) . '.' . rand(1,255) . '.' . rand(1,255) . '.' . rand(1,255);
}

require __DIR__ . '/../autoload.php';
require __DIR__ . '/../vendor/shieldon/messenger/autoload.php';


