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

    if (isset($_SESSION['shieldon_panel_lang'])) {
        $lang = $_SESSION['shieldon_panel_lang'];
    } elseif (isset($_SESSION['shieldon_ui_lang'])) {
        $lang = $_SESSION['shieldon_ui_lang'];
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

        $file = __DIR__ . '/../localization/' . $lang . '/' . $filename . '.php';
        
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