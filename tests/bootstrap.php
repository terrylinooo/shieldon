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
function saveTestingFile($filename, $dir = '')
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

require __DIR__ . '/../src/autoload.php';


