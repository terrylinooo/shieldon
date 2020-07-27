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

    public function testIptablesManager()
    {
        $this->assertPageOutputContainsString(
            'firewall/panel/iptables/ip4',
            'Iptables Manager (IPv4)'
        );

        $this->assertPageOutputContainsString(
            'firewall/panel/iptables/ip6',
            'Iptables Manager (IPv6)'
        );
    }

    public function testIptablesStatus()
    {
        $this->assertPageOutputContainsString(
            'firewall/panel/iptables/ip4status',
            'Iptables Status (IPv4)'
        );

        $this->assertPageOutputContainsString(
            'firewall/panel/iptables/ip6status',
            'Iptables Status (IPv6)'
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
        }


        return $_POST;
    }

    public function testIptablesFormPostInvalidFiles()
    {
        if (!defined('SHIELDON_PANEL_BASE')) {
            define('SHIELDON_PANEL_BASE', 'firewall/panel');
        }

        $queueFilePath = $this->getWritableTestFilePath('iptables_queue.log', 'shieldon/iptables');
        $logIp4FilePath = $this->getWritableTestFilePath('ipv4_command.log', 'shieldon/iptables');
        $logIp6FilePath = $this->getWritableTestFilePath('ipv6_command.log', 'shieldon/iptables');
 
        if (file_exists($queueFilePath)) {
            unlink($queueFilePath);
        }

        if (file_exists($logIp4FilePath)) {
            unlink($logIp4FilePath);
        }

        if (file_exists($logIp6FilePath)) {
            unlink($logIp6FilePath);
        }

        $queueFilePath = $this->getWritableTestFilePath('iptables_queue.log', 'shieldon/iptables');
        $logIp4FilePath = $this->getWritableTestFilePath('ipv4_command.log', 'shieldon/iptables');
        $logIp6FilePath = $this->getWritableTestFilePath('ipv6_command.log', 'shieldon/iptables');

        file_put_contents($logIp4FilePath, '');
        file_put_contents($logIp6FilePath, '');

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
        $this->assertSame(trim($string), 'add,4,33.33.33.33,16,8080,tcp,allow' . "\n". 'delete,4,33.33.33.33,16,8080,tcp,allow');
    }

    public function testCheckFieldIp()
    {

    }

    public function testCheckFieldPort()
    {

    }

    public function testCheckFieldSubnet()
    {

    }

    public function testCheckFieldProtocol()
    {

    }

    public function testCheckFieldAction()
    {

    }
}
