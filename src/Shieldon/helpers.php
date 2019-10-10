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

    $num = func_num_args();

    $filename    = func_get_arg(0); // required.
    $langcode    = func_get_arg(1); // required.
    $placeholder = ($num > 2) ? func_get_arg(2) : '';
    $replacement = ($num > 3) ? func_get_arg(3) : [];

    // Fetch current user's session language code.
    $lang = $_SESSION['lang'] ?? 'en';

    if (empty($i18n[$filename])) {
        $i18n[$filename] = include __DIR__ . '/../localization/' . $lang . '/' . $filename . '.php';
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
