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

class IptablesTest extends \Shieldon\FirewallTest\ShieldonTestCase
{
    use RouteTestTrait;

    public function testiptablesManager()
    {
        $this->assertOutputContainsString(
            'firewall/panel/iptables/ip4',
            'iptables Manager (IPv4)'
        );

        $this->assertOutputContainsString(
            'firewall/panel/iptables/ip6',
            'iptables Manager (IPv6)'
        );
    }

    public function testiptablesStatus()
    {
        $this->assertOutputContainsString(
            'firewall/panel/iptables/ip4status',
            'IpTables Status (IPv4)'
        );

        $this->assertOutputContainsString(
            'firewall/panel/iptables/ip6status',
            'IpTables Status (IPv6)'
        );
    }

    private function getMockPostData($type = 1)
    {
        /*
            Port:

            all - All
            21 - FTP
            22 - SSH
            23 - Telnet
            25 - SMTP
            80 - HTTP
            110 - POP3
            143 - IMAP
            443 - HTTPS
            3306 - MySQL
            6379 - Redis
            27017 - MongoDB
            custom
        */

        switch ($type) {
            case 1:
                $_POST['ip'] = '33.33.33.33';
                $_POST['port'] = 'custom';
                $_POST['subnet'] = '16';
                $_POST['protocol'] = 'tcp'; // all | tcp | ump
                $_POST['action'] = 'allow'; // allow | deny
                $_POST['port_custom'] = '8080';
                $_POST['remove'] = 'no';
                break;
            case 2:
                $_POST['ip'] = '33.33.33.33';
                $_POST['port'] = 'custom';
                $_POST['subnet'] = '16';
                $_POST['protocol'] = 'tcp'; // all | tcp | ump
                $_POST['action'] = 'allow'; // allow | deny
                $_POST['port_custom'] = '8080';
                $_POST['remove'] = 'yes';
                break;
            case 3:
                $_POST['ip'] = ''; // testCheckFieldIp
                $_POST['port'] = 'custom';
                $_POST['subnet'] = '16';
                $_POST['protocol'] = 'tcp';
                $_POST['action'] = 'allow';
                $_POST['port_custom'] = '8080';
                $_POST['remove'] = 'no';
                break;
            case 4:
                $_POST['ip'] = '33.33.33.33';
                $_POST['port'] = '';
                $_POST['subnet'] = '16';
                $_POST['protocol'] = 'tcp';
                $_POST['action'] = 'allow';
                $_POST['port_custom'] = '8080';
                $_POST['remove'] = 'no';
                break;
            case 5:
                $_POST['ip'] = '33.33.33.33';
                $_POST['port'] = 'custom';
                $_POST['subnet'] = '';
                $_POST['protocol'] = 'tcp';
                $_POST['action'] = 'allow';
                $_POST['port_custom'] = '8080';
                $_POST['remove'] = 'no';
                break;
            case 6:
                $_POST['ip'] = '33.33.33.33';
                $_POST['port'] = 'custom';
                $_POST['subnet'] = '16';
                $_POST['protocol'] = '';
                $_POST['action'] = 'allow';
                $_POST['port_custom'] = '8080';
                $_POST['remove'] = 'no';
                break;
            case 7:
                $_POST['ip'] = '33.33.33.33';
                $_POST['port'] = 'custom';
                $_POST['subnet'] = '16';
                $_POST['protocol'] = 'tcp';
                $_POST['action'] = '';
                $_POST['port_custom'] = '8080';
                $_POST['remove'] = 'no';
                break;
            case 8:
                $_POST['ip'] = '33.33.33.33';
                $_POST['port'] = 'custom';
                $_POST['subnet'] = '16';
                $_POST['protocol'] = 'tcp';
                $_POST['action'] = 'allow';
                $_POST['port_custom'] = '';
                $_POST['remove'] = 'no';
                break;
            case 8:
                $_POST['ip'] = '33.33.33.33';
                $_POST['port'] = 'custom';
                $_POST['subnet'] = '16';
                $_POST['protocol'] = 'tcp';
                $_POST['action'] = 'allow';
                $_POST['port_custom'] = '8080';
                $_POST['remove'] = '';
                break;
        }

        return $_POST;
    }

    public function testiptablesFormPostInvalidFiles()
    {
        if (!defined('SHIELDON_PANEL_BASE')) {
            define('SHIELDON_PANEL_BASE', 'firewall/panel');
        }

        $queueFilePath = $this->getWritableTestFilePath('iptables_queue.log', 'shieldon/iptables');
        $logIp4FilePath = $this->getWritableTestFilePath('ipv4_command.log', 'shieldon/iptables');
        $logIp6FilePath = $this->getWritableTestFilePath('ipv6_command.log', 'shieldon/iptables');
        $statusIp4FilePath = $this->getWritableTestFilePath('ipv4_status.log', 'shieldon/iptables');
        $statusIp6FilePath = $this->getWritableTestFilePath('ipv6_status.log', 'shieldon/iptables');
 
        if (file_exists($queueFilePath)) {
            unlink($queueFilePath);
        }

        if (file_exists($logIp4FilePath)) {
            unlink($logIp4FilePath);
        }

        if (file_exists($statusIp6FilePath)) {
            unlink($statusIp6FilePath);
        }

        if (file_exists($statusIp4FilePath)) {
            unlink($statusIp4FilePath);
        }

        if (file_exists($statusIp6FilePath)) {
            unlink($statusIp6FilePath);
        }

        $queueFilePath = $this->getWritableTestFilePath('iptables_queue.log', 'shieldon/iptables');
        $logIp4FilePath = $this->getWritableTestFilePath('ipv4_command.log', 'shieldon/iptables');
        $logIp6FilePath = $this->getWritableTestFilePath('ipv6_command.log', 'shieldon/iptables');
        $statusIp4FilePath = $this->getWritableTestFilePath('ipv4_status.log', 'shieldon/iptables');
        $statusIp6FilePath = $this->getWritableTestFilePath('ipv6_status.log', 'shieldon/iptables');

        // phpcs:ignore
        file_put_contents($logIp4FilePath, 'add,4,33.33.33.33,16,8080,tcp,allow' . "\n" . 'add,4,33.34.34.34,16,8080,tcp,allow');
        file_put_contents($logIp6FilePath, 'add,2607:f0d0:1002:51::4,8080,tcp,allow');
        file_put_contents($statusIp4FilePath, 'test');
        file_put_contents($statusIp6FilePath, 'test');

        $firewall = new \Shieldon\Firewall\Firewall();
        $firewall->configure(BOOTSTRAP_DIR . '/../tmp/shieldon');
        $firewall->setConfig('messengers.telegram.enable', true);
        $firewall->setConfig('messengers.telegram.confirm_test', true);
        $firewall->setConfig('messengers.components.ip.enable', false);
        $firewall->setConfig('messengers.components.rdns.enable', false);
        $firewall->setConfig('messengers.components.header.enable', false);
        $firewall->setConfig('messengers.components.user_agent.enable', false);
        $firewall->setConfig('messengers.components.trusted_bot.enable', false);
        $firewall->setConfig('messengers.filters.fequency.enable', false);
        $firewall->setConfig('messengers.filters.referer.enable', false);
        $firewall->setConfig('messengers.filters.cookie.enable', false);
        $firewall->setConfig('messengers.filters.cookie.enable', false);
        $firewall->setConfig('messengers.filters.cookie.enable', false);
        $firewall->setConfig('iptables.enable', true);
        $firewall->setConfig('iptables.config.watching_folder', BOOTSTRAP_DIR . '/../tmp/shieldon/iptables');
        $firewall->setConfig('ip6tables.enable', true);
        $firewall->setConfig('ip6tables.config.watching_folder', BOOTSTRAP_DIR . '/../tmp/shieldon/iptables');
        $firewall->setup();
        $firewall->getKernel()->disableFilters();
        $firewall->getKernel()->disableComponents();

        $firewall->getKernel()->setProperty(
            'iptables_watching_folder',
            BOOTSTRAP_DIR . '/../tmp/shieldon/iptables'
        );

        // Start testing...

        $_POST = $this->getMockPostData(1);
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $this->refreshRequest();

        $resolver = new \Shieldon\Firewall\HttpResolver();
        $controllerClass = new \Shieldon\Firewall\Panel\Iptables();
        ob_start();
        $resolver(call_user_func([$controllerClass, 'ip4']));
        $output = ob_get_contents();
        ob_end_clean();

        $string = file_get_contents($queueFilePath);
        $this->assertSame(trim($string), 'add,4,33.33.33.33,16,8080,tcp,allow');

         // Start testing...

        $_POST = $this->getMockPostData(2);
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $this->refreshRequest();

        $resolver = new \Shieldon\Firewall\HttpResolver();
        $controllerClass = new \Shieldon\Firewall\Panel\Iptables();
        ob_start();
        $resolver(call_user_func([$controllerClass, 'ip4']));
        $output = ob_get_contents();
        ob_end_clean();

        $string = file_get_contents($queueFilePath);

        // phpcs:ignore
        $this->assertSame(trim($string), 'add,4,33.33.33.33,16,8080,tcp,allow' . "\n". 'delete,4,33.33.33.33,16,8080,tcp,allow');
    }

    public function testCheckFieldIp()
    {
        $_POST = $this->getMockPostData(3);
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $this->refreshRequest();

        $this->assertOutputContainsString(
            'firewall/panel/iptables/ip4',
            'iptables Manager (IPv4)'
        );
    }

    public function testCheckFieldPort()
    {
        $_POST = $this->getMockPostData(4);
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $this->refreshRequest();

        $this->assertOutputContainsString(
            'firewall/panel/iptables/ip4',
            'iptables Manager (IPv4)'
        );
    }

    public function testCheckFieldSubnet()
    {
        $_POST = $this->getMockPostData(5);
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $this->refreshRequest();

        $this->assertOutputContainsString(
            'firewall/panel/iptables/ip4',
            'iptables Manager (IPv4)'
        );
    }

    public function testCheckFieldProtocol()
    {
        $_POST = $this->getMockPostData(6);
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $this->refreshRequest();

        $this->assertOutputContainsString(
            'firewall/panel/iptables/ip4',
            'iptables Manager (IPv4)'
        );
    }

    public function testCheckFieldAction()
    {
        $_POST = $this->getMockPostData(7);
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $this->refreshRequest();

        $this->assertOutputContainsString(
            'firewall/panel/iptables/ip4',
            'iptables Manager (IPv4)'
        );
    }
}
