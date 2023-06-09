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

namespace Shieldon\Firewall\Panel;

use Psr\Http\Message\ResponseInterface;
use Shieldon\Firewall\Panel\BaseController;
use SplFileObject;
use ReflectionObject;
use function Shieldon\Firewall\__;
use function Shieldon\Firewall\get_request;
use function explode;
use function file;
use function file_exists;
use function file_put_contents;
use function filter_var;
use function in_array;
use function is_array;
use function is_numeric;
use function sleep;
use function trim;

/**
 * The bridge between the Shieldon firewall and the iptables firewall.
 */
class Iptables extends BaseController
{
    /**
     *   Public methods       | Desctiotion
     *  ----------------------|---------------------------------------------
     *   ip4                  | The page for iptables (IPv4) management.
     *   ip6                  | The page for iptables (IPv6) management.
     *   ip4status            | The page for dispalying iptables (IPv4) status.
     *   ip6status            | The page for dispalying iptables (IPv6) status.
     *  ----------------------|---------------------------------------------
     */

    /**
     * Constructor
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * The IPv4 table.
     *
     * @return ResponseInterface
     */
    public function ip4(): ResponseInterface
    {
        return $this->iptables('IPv4');
    }

    /**
     * The IPv6 table.
     *
     * @return ResponseInterface
     */
    public function ip6(): ResponseInterface
    {
        return $this->iptables('IPv6');
    }

    /**
     * The IPv4 table.
     *
     * @return ResponseInterface
     */
    public function ip4status(): ResponseInterface
    {
        return $this->iptablesStatus('IPv4');
    }

    /**
     * The IPv6 table.
     *
     * @return ResponseInterface
     */
    public function ip6status(): ResponseInterface
    {
        return $this->iptablesStatus('IPv6');
    }

    /**
     * System layer firwall - iptables
     *
     * @param string $type The type of IP address.
     *
     * @return ResponseInterface
     */
    protected function iptables(string $type = 'IPv4'): ResponseInterface
    {
        $reflection = new ReflectionObject($this->kernel);
        $t = $reflection->getProperty('properties');
        $t->setAccessible(true);
        $properties = $t->getValue($this->kernel);

        $dir = $properties['iptables_watching_folder'];

        $commandLogFile = $dir . '/' . strtolower($type) . '_command.log';
        $iptablesQueueFile = $dir . '/iptables_queue.log';

        if ('POST' === get_request()->getMethod()) {
            $this->iptablesFormPost($type, $commandLogFile, $iptablesQueueFile);
        }

        $data = [];
        $ipCommand = '';

        if (file_exists($commandLogFile)) {
            $file = new SplFileObject($commandLogFile);

            $ipCommand = [];

            while (!$file->eof()) {
                $line = trim($file->fgets());
                $ipInfo = explode(',', $line);

                // If the column amount is at least 6 maning that the data is
                // existed so that we can use it.
                if (!empty($ipInfo[6])) {
                    $ipCommand[] = $ipInfo;
                }
            }
        }

        $data['ipCommand'] = $ipCommand;
        $data['type'] = $type;

        $data['title'] = __('panel', 'title_iptables_manager', 'IpTables Manager') . ' (' . $type . ')';

        return $this->renderPage('panel/iptables_manager', $data);
    }

    /**
     * System layer firewall - iptables Status
     * iptables -L
     *
     * @param string $type The type of IP address.
     *
     * @return ResponseInterface
     */
    protected function iptablesStatus(string $type = 'IPv4'): ResponseInterface
    {
        $data = [];

        $reflection = new ReflectionObject($this->kernel);
        $t = $reflection->getProperty('properties');
        $t->setAccessible(true);
        $properties = $t->getValue($this->kernel);

        $dir = $properties['iptables_watching_folder'];

        // The iptables log files.
        $ipStatusFile = $dir . '/ipv4_status.log';

        if ('IPv6' === $type) {
            $ipStatusFile = $dir . '/ipv6_status.log';
        }
        
        $ipStatus = '';

        if (file_exists($ipStatusFile)) {
            $ipStatus = file_get_contents($ipStatusFile);
        }

        $data['ipStatus'] = $ipStatus;
        $data['type'] = $type;

        $data['title'] = __('panel', 'title_iptables_status', 'IpTables Status') . ' (' . $type . ')';

        return $this->renderPage('panel/iptables_status', $data);
    }

    /**
     * Detect and handle form post action.
     *
     * @param string $type              IPv4 or IPv6
     * @param string $commandLogFile    The log file contains executed commands.
     * @param string $iptablesQueueFile The file contains the commands that wil
     *                                  be executed by iptables
     *
     * @return void
     */
    private function iptablesFormPost(string $type, string $commandLogFile, string $iptablesQueueFile): void
    {
        $postParams = get_request()->getParsedBody();

        if (!is_array($postParams)) {
            // @codeCoverageIgnoreStart
            return;
            // @codeCoverageIgnoreEnd
        }

        if (!$this->iptablesFormPostVerification($postParams)) {
            return;
        }

        $ip       = $postParams['ip'];
        $port     = $postParams['port'];
        $subnet   = $postParams['subnet'];
        $protocol = $postParams['protocol'];
        $action   = $postParams['action'];
        $cPort    = $postParams['port_custom'] ?? 'all';

        $ipv = substr($type, -1);

        if ($port === 'custom') {
            $port = $cPort;
        }

        /**
         * The process of add or remove command string from two files:
         *
         * (1) The command file -
         *     This file is used on display the commands on the page
         *     iptables Manager.
         * (2) The queue file -
         *     This file is a bridge between Shieldon Firewall and Iptalbes.
         *     ipbales_bridge.sh will monitor this file, once commands come,
         *     transforming the commands into iptables syntax commands and
         *     then execute the iptables commands.
         */
        if ($postParams['remove'] === 'yes') {
            $originCommandString = "add,$ipv,$ip,$subnet,$port,$protocol,$action";

            // Delete line from the log file.
            $fileArr = file($commandLogFile);
    
            if (is_array($fileArr)) {
                $keyFound = array_search(trim($originCommandString), $fileArr);

                // Remove the row from the file content.
                unset($fileArr[$keyFound]);
    
                $t = [];
                $i = 0;
                foreach ($fileArr as $f) {
                    $t[$i] = trim($f);
                    $i++;
                }

                // Save the filtered content.
                file_put_contents($commandLogFile, implode(PHP_EOL, $t));
            }
    
            // Pass the command to the iptables bridge file to remove the rule
            // which is in the Iptable rule list.
            $applyCommand = "delete,$ipv,$ip,$subnet,$port,$protocol,$action";
            file_put_contents($iptablesQueueFile, $applyCommand . "\n", FILE_APPEND | LOCK_EX);
            sleep(1);

            // Finish this action, return.
            return;
        }

        $applyCommand = "add,$ipv,$ip,$subnet,$port,$protocol,$action";
        file_put_contents($iptablesQueueFile, $applyCommand . "\n", FILE_APPEND | LOCK_EX);

        // Becase we need system cronjob done, and then the web page will show the actual results.
        sleep(10);
    }

    /**
     * Verify the form fields.
     *
     * @param array $postParams The PSR-7 variable of $_POST
     *
     * @return bool
     */
    private function iptablesFormPostVerification(array $postParams): bool
    {
        if (!$this->checkFieldIp($postParams)) {
            return false;
        }

        if (!$this->checkFieldPort($postParams)) {
            return false;
        }

        if (!$this->checkFieldSubnet($postParams)) {
            return false;
        }

        if (!$this->checkFieldProtocol($postParams)) {
            return false;
        }

        if (!$this->checkFieldAction($postParams)) {
            return false;
        }

        return true;
    }

    /**
     * Verify the form  field - Ip.
     *
     * @param array $postParams The PSR-7 variable of $_POST
     *
     * @return bool
     */
    private function checkFieldIp($postParams): bool
    {
        if (filter_var(explode('/', $postParams['ip'])[0], FILTER_VALIDATE_IP)) {
            return true;
        }
        return false;
    }

    /**
     * Verify the form field - Port.
     *
     * @param array $postParams The PSR-7 variable of $_POST
     *
     * @return bool
     */
    private function checkFieldPort($postParams): bool
    {
        if (is_numeric($postParams['port']) ||
            in_array($postParams['port'], ['all', 'custom'])
        ) {
            return true;
        }
        return false;
    }

    /**
     * Verify the form field - Subnet.
     *
     * @param array $postParams The PSR-7 variable of $_POST
     *
     * @return bool
     */
    private function checkFieldSubnet($postParams): bool
    {
        if (is_numeric($postParams['subnet']) ||
            $postParams['subnet'] === 'null'
        ) {
            return true;
        }
        return false;
    }

    /**
     * Verify the form field - Protocol.
     *
     * @param array $postParams The PSR-7 variable of $_POST
     *
     * @return bool
     */
    private function checkFieldProtocol($postParams): bool
    {
        if (in_array($postParams['protocol'], ['tcp', 'udp', 'all'])) {
            return true;
        }
        return false;
    }

    /**
     * Verify the form field - Action.
     *
     * @param array $postParams The PSR-7 variable of $_POST
     *
     * @return bool
     */
    private function checkFieldAction($postParams): bool
    {
        if (in_array($postParams['action'], ['allow', 'deny'])) {
            return true;
        }
        return false;
    }
}
