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

class BashScriptTest extends \Shieldon\FirewallTest\ShieldonTestCase
{
    protected $iptablesWatchingFolder = '';

    public function prepareFiles()
    {
        $this->iptablesWatchingFolder = BOOTSTRAP_DIR . '/../tmp/iptables';
     
        if (!is_dir($this->iptablesWatchingFolder)) {
            $originalUmask = umask(0);
            mkdir($this->iptablesWatchingFolder, 0777, true);
            umask($originalUmask);

            // Create default log files.
            if (is_writable($this->iptablesWatchingFolder)) {
                fopen($this->iptablesWatchingFolder . '/iptables_queue.log', 'w+');
                fopen($this->iptablesWatchingFolder . '/ipv4_status.log', 'w+');
                fopen($this->iptablesWatchingFolder . '/ipv6_status.log', 'w+');
                fopen($this->iptablesWatchingFolder . '/ipv4_command.log', 'w+');
                fopen($this->iptablesWatchingFolder . '/ipv6_command.log', 'w+');
            }
        }
    }

    /**
     * This testing method does not work in Windows system.
     *
     * @return void
     */
    public function testCommandBridge()
    {
        if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
            $this->markTestSkipped('[Warning] BashScriptTest is not available in Windows system!');
        }

        $this->prepareFiles();
        
        $bashScriptPath = BOOTSTRAP_DIR . '/../bin/iptables_bridge.sh';

        // Add a command.
        $queueFilePath = $this->iptablesWatchingFolder . '/iptables_queue.log';
        $commandFilePath = $this->iptablesWatchingFolder . '/ipv4_command.log';

        // Clear the conent from this file.
        file_put_contents($queueFilePath, '');
        file_put_contents($commandFilePath, '');

        // command, ipv4/6, ip, subnet, port, protocol, action
        // add,4,127.0.0.1,all,80,tcp,drop   (example)
        $command = 'add,4,33.33.33.34,all,all,all,deny';

        // Add this IP address to itables_queue.log
        // Use `bin/iptables.sh` for adding it into IPTABLES. See document for more information.
        file_put_contents($queueFilePath, $command . "\n", FILE_APPEND | LOCK_EX);

        @exec('sudo bash ' . $bashScriptPath . ' --watch=' . $this->iptablesWatchingFolder);

        $resultString = file_get_contents($commandFilePath);

        $this->assertSame(trim($resultString), $command);
    }
}
