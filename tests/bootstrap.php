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

use Shieldon\Firewall\Helpers;

include __DIR__ . '/../autoload.php';
include __DIR__ . '/../vendor/autoload.php';
include __DIR__ . '/../vendor/shieldon/messenger/autoload.php';

// Mock for PHPUnit.
if (!isset($_SERVER['REMOTE_ADDR'])) {
    $_SERVER['REMOTE_ADDR'] = '127.0.0.127';
}

if (!isset($_SERVER['HTTP_CF_CONNECTING_IP'])) {
    $_SERVER['HTTP_CF_CONNECTING_IP'] = '127.0.0.128';
}

if (!isset($_SERVER['HTTP_X_FORWARDED_HOST'])) {
    $_SERVER['HTTP_X_FORWARDED_HOST'] = '127.0.0.129';
}

if (!isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
    $_SERVER['HTTP_X_FORWARDED_FOR'] = '127.0.0.130';
}

if (!isset($_SERVER['HTTPS'])) {
    $_SERVER['HTTPS'] = 'on';
}

if (!isset($_SERVER['REQUEST_URI'])) {
    $_SERVER['REQUEST_URI'] = '/';
}

if (!isset($_SERVER['HTTP_HOST'])) {
    $_SERVER['HTTP_HOST'] = 'shieldon.io';
}

new Helpers();

function test_event_disptcher()
{
    echo 'This is a function call.';
}
