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

namespace Shieldon\FirewallTest\Panel;

class ReportTest extends \Shieldon\FirewallTest\ShieldonTestCase
{
    use RouteTestTrait;

    private function prepareSampleLogs()
    {
        $dir = BOOTSTRAP_DIR . '/../tmp/shieldon/action_logs';

        $it = new \RecursiveDirectoryIterator($dir, \RecursiveDirectoryIterator::SKIP_DOTS);
        $files = new \RecursiveIteratorIterator($it, \RecursiveIteratorIterator::CHILD_FIRST);

        foreach ($files as $file) {
            if ($file->isDir()) {
                rmdir($file->getRealPath());
            } else {
                unlink($file->getRealPath());
            }
        }

        // Copy sample files.
        $dir2 = BOOTSTRAP_DIR . '/../tests/samples/action_logs';

        $it = new \RecursiveDirectoryIterator($dir2, \RecursiveDirectoryIterator::SKIP_DOTS);
        $files = new \RecursiveIteratorIterator($it, \RecursiveIteratorIterator::CHILD_FIRST);

        foreach ($files as $file) {
            if (!$file->isDir()) {
                copy($file->getRealPath(), $dir . '/' . $file->getFilename());
            }
        }
    }

    public function testActionLog()
    {
        $this->prepareSampleLogs();

        $this->assertOutputContainsString(
            'firewall/panel/report/actionLog',
            'Action Logs'
        );
    }

    public function testActionLogWithCachedData()
    {
        $this->assertOutputContainsString(
            'firewall/panel/report/actionLog',
            'Action Logs'
        );
    }

    public function testOperationStatus()
    {
        $this->assertOutputContainsString(
            'firewall/panel/report/operation',
            'Operation Status'
        );
    }
}
