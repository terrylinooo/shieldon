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

namespace Shieldon\Firewall;

use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;
use Shieldon\Firewall\HttpFactory;
use Shieldon\Firewall\Utils\Container;

/**
 * This value will be only displayed on Firewall Panel.
 */
define('SHIELDON_FIREWALL_VERSION', '2.0');

/**
 * Just use PSR-4 autoloader to load those helper functions.
 */
class Helpers
{

}

/**
 * Get locale message.
 *
 * @return string
 */
function __(): string
{
    /**
     * Load locale string from i18n files and store them into this array 
     * for further use.
     * 
     * @var array 
     */
    static $i18n;

    /**
     * Check the file exists for not.
     *
     * @var array 
     */
    static $fileChecked;

    $num = func_num_args();

    $filename    = func_get_arg(0); // required.
    $langcode    = func_get_arg(1); // required.
    $placeholder = ($num > 2) ? func_get_arg(2) : '';
    $replacement = ($num > 3) ? func_get_arg(3) : [];
    $lang        = 'en';

    if (isset($_SESSION['SHIELDON_PANEL_LANG'])) {
        $lang = $_SESSION['SHIELDON_PANEL_LANG'];
    } elseif (isset($_SESSION['SHIELDON_UI_LANG'])) {
        $lang = $_SESSION['SHIELDON_UI_LANG'];
    }

    if (empty($i18n[$filename]) && empty($fileChecked[$filename])) {

        $fileChecked[$filename] = true;

        $lang = str_replace('-', '_', $lang);

        if (stripos($lang, 'zh_') !== false) {
            if (stripos($lang, 'zh_CN') !== false) {
                $lang = 'zh_CN';
            } else {
                $lang = 'zh';
            }
        }

        $file = __DIR__ . '/../../localization/' . $lang . '/' . $filename . '.php';
        
        if (file_exists($file)) {
            $i18n[$filename] = include $file;
        }
    }

    // If we don't get the string from the localization file, use placeholder instead.
    $resultString = $placeholder;

    if (! empty($i18n[$filename][$langcode])) {
        $resultString = $i18n[$filename][$langcode];
    }

    if (is_array($replacement)) { 
        /**
         * Example:
         *     __('test', 'example_string', 'Search results: {0} items. Total items: {1}.', [5, 150]);
         * 
         * Result:
         *     Search results: 5 items. Total items: 150.
         */
        foreach ($replacement as $i => $r) {
            $resultString = str_replace('{' . $i . '}', $replacement[$i], $resultString);
        }
    }
    
    return str_replace("'", '’', $resultString);
}

/**
 * Echo string from __()
 *
 * @return void
 */
function _e(): void
{
    $num = func_num_args();

    $filename    = func_get_arg(0); // required.
    $langcode    = func_get_arg(1); // required.
    $placeholder = ($num > 2) ? func_get_arg(2) : '';
    $replacement = ($num > 3) ? func_get_arg(3) : [];

    echo __($filename, $langcode, $placeholder, $replacement);
}

/**
 * Mask strings with asterisks.
 *
 * @param string $str
 *
 * @return string
 */
function mask_string($str)
{
    if (filter_var($str, FILTER_VALIDATE_IP) !== false) {
        $tmp = explode('.', $str);
        $tmp[0] = '*';
        $tmp[1] = '*';
        $masked = implode('.', $tmp);

    } else {
        $masked =  str_repeat('*', strlen($str) - 6) . substr(str, -6);
    }

    return $masked;
}

/**
 * Get current CPU usage information.
 *
 * This function is only used in sending notifications and it is unavailable on Win system.
 * If you are using shared hosting and your hosting provider has disabled `sys_getloadavg`
 * and `shell_exec`, it won't work either.
 *
 * @return string
 */
function get_cpu_usage()
{
    $return = '';

    // This feature is not available on Windows platform.
    if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
        return $return;
    }

    $cpuLoads = @sys_getloadavg();
    $cpuCores = trim(@shell_exec("grep -P '^processor' /proc/cpuinfo|wc -l"));

    if (! empty($cpuCores) && ! empty($cpuLoads)) {
        $return = round($cpuLoads[1] / ($cpuCores + 1) * 100, 0) . '%';
    }
    return $return;
}

/**
 * Get current RAM usage information. 
 *
 * If you are using shared hosting and your hosting provider has disabled `shell_exec`, 
 * This function may not work.
 *
 * @return string
 */
function get_memory_usage()
{
    $return = '';

    // This feature is not available on Windows platform.
    if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
        return $return;
    }

    $freeResult = explode("\n", trim(@shell_exec('free')));

    if (! empty($freeResult)) {
        $parsed = preg_split("/[\s]+/", $freeResult[1]);
        $return = round($parsed[2] / $parsed[1] * 100, 0) . '%';
    }
    return $return;
}

/**
 * The default settings of Shieldon core.
 *
 * @return array
 */
function get_default_properties(): array
{
    return [

        'time_unit_quota' => [
            's' => 2,
            'm' => 10,
            'h' => 30,
            'd' => 60
        ],

        'time_reset_limit' => 3600,
        'interval_check_referer' => 5,
        'interval_check_session' => 30,
        'limit_unusual_behavior' => [
            'cookie'  => 5,
            'session' => 5,
            'referer' => 10
        ],

        'cookie_name' => 'ssjd',
        'cookie_domain' => '',
        'cookie_value' => '1',
        'display_online_info' => true,
        'display_user_info' => false,

        /**
         * If you set this option enabled, Shieldon will record every CAPTCHA fails in a row, 
         * Once that user have reached the limitation number, Shieldon will put it as a blocked IP in rule table,
         * until the new data cycle begins.
         * 
         * Once that user have been blocked, they are still access the warning page, it means that they are not
         * humain for sure, so let's throw them into the system firewall and say goodbye to them forever.
         */
        'deny_attempt_enable' => [
            'data_circle'     => false,
            'system_firewall' => false,
        ],
        'deny_attempt_notify' => [
            'data_circle'     => false,
            'system_firewall' => false,
        ],
        'deny_attempt_buffer' => [
            'data_circle'     => 10,
            'system_firewall' => 10,
        ],

        /**
         * To prevent dropping social platform robots into iptables firewall, such as Facebook, Line, 
         * and others who scrape snapshots from your web pages, you should adjust the values below 
         * to fit your needs. (unit: second)
         */
        'record_attempt_detection_period' => 5, // 5 seconds.

        // Reset the counter after n second.
        'reset_attempt_counter' => 1800, // 30 minutes.

        // System-layer firewall, ip6table service watches this folder to 
        // receive command created by Shieldon Firewall.
        'iptables_watching_folder' => '/tmp/',
    ];
}

/**
 * PSR-7 HTTP server request
 *
 * @return \Psr\Http\Message\ServerRequestInterface
 */
function get_request(): ServerRequestInterface
{
    $request = Container::get('request');

    if (is_null($request)) {
        $request = HttpFactory::createRequest();
        Container::set('request', $request);
    }

    return $request;
}

/**
 * PSR-7 HTTP response.
 *
 * @return \Psr\Http\Message\ResponseInterface
 */
function get_response(): ResponseInterface
{
    $response = Container::get('response');

    if (is_null($response)) {
        $response = HttpFactory::createResponse();
        Container::set('response', $response);
    }

    return $response;
}

/**
 * Session
 *
 * @return \Shieldon\Firewall\Utils\Session
 */
function get_session()
{
    $session = Container::get('session');

    if (is_null($session)) {
        $session = HttpFactory::createSession();
        Container::set('session', $session);
    }

    return $session;
}
