<?php declare(strict_types=1);
/*
 * This file is part of the Shieldon package.
 *
 * (c) Terry L. <contact@terryl.in>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Shieldon\Helper;

define('SHIELDON_VERSION', '1.0.0');

/**
 * @since 3.1.0
 */

/**
 * Get locale message.
 *
 * @return string
 */
function __(): string
{
    /**
     * Load locale string from i18n files and store them into this array for futher use.
     * 
     * @var array 
     */
    static $i18n;

    /**
     * Checking the file exists for not.
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
