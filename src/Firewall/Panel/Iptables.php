<?php
/*
 * This file is part of the Shieldon package.
 *
 * (c) Terry L. <contact@terryl.in>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Shieldon\Firewall\Panel;

use Psr\Http\Message\ResponseInterface;
use Shieldon\Firewall\Panel\BaseController;
use function Shieldon\Firewall\get_request;

use SplFileObject;
use ReflectionObject;

use function filter_var;
use function is_numeric;
use function in_array;
use function file;
use function file_put_contents;
use function sleep;
use function file_exists;
use function explode;
use function trim;

/**
 * The bridge between the Shieldon firewall and the Iptables firewall.
 */
class Iptables extends BaseController
{
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
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function ip4status(): ResponseInterface
    {
        return $this->iptablesStatus('IPv4');
    }

    /**
     * The IPv6 table.
     *
     * @return \Psr\Http\Message\ResponseInterface
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
     * @return void
     */
    protected function iptables(string $type = 'IPv4'): ResponseInterface
    {
        $postParams = get_request()->getParsedBody();

        $reflection = new ReflectionObject($this->kernel);
        $t = $reflection->getProperty('properties');
        $t->setAccessible(true);
        $properties = $t->getValue($this->kernel);

        $iptablesWatchingFolder = $properties['iptables_watching_folder'];

        // The iptables log files.
        $ipCommandFile = $iptablesWatchingFolder . '/ipv4_command.log';

        if ('IPv6' === $type) {
            $ipCommandFile = $iptablesWatchingFolder . '/ipv6_command.log';
        }

        $iptablesQueueFile = $iptablesWatchingFolder . '/iptables_queue.log';

        $con1 = (
            isset($postParams['ip']) &&
            filter_var(explode('/', $postParams['ip'])[0], FILTER_VALIDATE_IP)
        );

        $con2 = (
            isset($postParams['port']) &&
            (
                is_numeric($postParams['port']) ||
                $postParams['port'] === 'all' ||
                $postParams['port'] === 'custom'
            )
        );

        $con3 = (
            isset($postParams['subnet']) && 
            (
                is_numeric($postParams['subnet']) || 
                $postParams['subnet'] === 'null'
            )
        );

        $con4 = (
            isset($postParams['protocol']) && 
            in_array($postParams['protocol'], ['tcp', 'udp', 'all'])
        );

        $con5 = (
            isset($postParams['action']) && 
            in_array($postParams['action'], ['allow', 'deny'])
        );

        if ($con1 && $con2 && $con3 && $con4 && $con5) {
            $ip       = $postParams['ip'];
            $port     = $postParams['port'];
            $subnet   = $postParams['subnet'];
            $protocol = $postParams['protocol'];
            $action   = $postParams['action'];
            $cPort    = $postParams['port_custom'] ?? 'all';

            $isRemoval = false;

            if (isset($postParams['remove']) && $postParams['remove'] === 'yes') {
                $isRemoval = true;
            }

            if ('custom' === $port) {
                $port = $cPort;
            }

            $ipv = '4';

            if ('IPv6' === $type) {
                $ipv = '6';
            }

            $applyCommand = "add,$ipv,$ip,$subnet,$port,$protocol,$action";

            if ($isRemoval) {
                $originCommandString = "add,$ipv,$ip,$subnet,$port,$protocol,$action";

                // Delete line from the log file.
                $fileArr = file($ipCommandFile);
                unset($fileArr[array_search(trim($originCommandString), $fileArr)]);

                $t = [];
                $i = 0;
                foreach ($fileArr as $f) {
                    $t[$i] = trim($f);
                    $i++;
                }
                file_put_contents($ipCommandFile, implode(PHP_EOL, $t));

                $applyCommand = "delete,$ipv,$ip,$subnet,$port,$protocol,$action";
            }

            // Add a command to the watching file.
            file_put_contents($iptablesQueueFile, $applyCommand . "\n", FILE_APPEND | LOCK_EX);

            if (!$isRemoval) {

                // Becase we need system cronjob done, and then the web page will show the actual results.
                sleep(10);
            } else {
                sleep(1);
            }
        }

        $data[] = [];

        $ipCommand = '';

        if (file_exists($ipCommandFile)) {
            $file = new SplFileObject($ipCommandFile);

            $ipCommand = [];

            while (!$file->eof()) {
                $line = trim($file->fgets());
                $ipInfo = explode(',', $line);

                if (!empty($ipInfo[4])) {
                    $ipCommand[] = $ipInfo;
                }
            }
        }

        $data['ipCommand'] = $ipCommand;
        $data['type'] = $type;

        $data['title'] = __('panel', 'title_iptables_manager', 'Iptables Manager') . ' (' . $type . ')';

        return $this->renderPage('panel/iptables_manager', $data);
    }

    /**
     * System layer firwall - iptables Status
     * iptables -L
     * 
     * @param string $type The type of IP address.
     *
     * @return \Psr\Http\Message\ResponseInterface
     */
    protected function iptablesStatus(string $type = 'IPv4'): ResponseInterface
    {
        $data[] = [];

        $reflection = new ReflectionObject($this->kernel);
        $t = $reflection->getProperty('properties');
        $t->setAccessible(true);
        $properties = $t->getValue($this->kernel);

        $iptablesWatchingFolder = $properties['iptables_watching_folder'];

        // The iptables log files.
        $ipStatusFile = $iptablesWatchingFolder . '/ipv4_status.log';

        if ('IPv6' === $type) {
            $ipStatusFile = $iptablesWatchingFolder . '/ipv6_status.log';
        }
        
        $ipStatus = '';

        if (file_exists($ipStatusFile)) {
            $ipStatus = file_get_contents($ipStatusFile);
        }

        $data['ipStatus'] = $ipStatus;
        $data['type'] = $type;

        $data['title'] = __('panel', 'title_iptables_status', 'Iptables Status') . ' (' . $type . ')';

        return $this->renderPage('panel/iptables_status', $data);
    }
}

