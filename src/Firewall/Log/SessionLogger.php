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

namespace Shieldon\Firewall\Log;

use function date;
use function file_put_contents;
use function is_dir;
use function mkdir;
use function umask;
use function debug_backtrace;
use const PHP_EOL;
use const FILE_APPEND;

/**
 * Only use this class for debugging after running the unit tests.
 */
final class SessionLogger
{
    /**
     * Log the message for debugging.
     *
     * @param string $text The message.
     *
     * @return void
     */
    public static function log(string $text = ''): void
    {
        $dir = BOOTSTRAP_DIR . '/../tmp/shieldon/session_logs';
        $file = $dir . '/' . date('Y-m-d') . '.json';
    
        $originalUmask = umask(0);

        if (!is_dir($dir)) {
            mkdir($dir, 0777, true);
        }
    
        umask($originalUmask);

        $method = json_encode(debug_backtrace()[3], JSON_PRETTY_PRINT);

        $content = date('Y-m-d H:i:s') . ' [text]' . $text . "\n";
        $content .= $method;

        file_put_contents($file, $content . PHP_EOL, FILE_APPEND);
    }
}
