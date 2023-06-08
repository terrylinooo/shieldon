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

namespace Shieldon\Firewall\Firewall\Captcha;

use Shieldon\Firewall\Captcha\CaptchaInterface;
use function array_map;
use function explode;
use function implode;
use function ucwords;

/*
 * The factory creates driver instances.
 */
class CaptchaFactory
{
    /**
     * Create a captcha instance.
     *
     * @param string $type    The driver's type string.
     * @param array  $setting The configuration of that driver.
     *
     * @return CaptchaInterface
     */
    public static function getInstance(string $type, array $setting): CaptchaInterface
    {
        $className = '\Shieldon\Firewall\Firewall\Captcha\Item' . self::getCamelCase($type);

        return $className::get($setting);
    }

    /**
     * Check whether a messenger is available or not.
     *
     * @param array  $setting The configuration of that messanger.
     *
     * @return bool
     */
    public static function check(array $setting): bool
    {
        if (empty($setting['enable'])) {
            return false;
        }

        return true;
    }

    /**
     * Covert string with dashes into camel-case string.
     *
     * @param string $string A string with dashes.
     *
     * @return string
     */
    public static function getCamelCase(string $string = ''): string
    {
        $str = explode('-', $string);
        $str = implode(
            '',
            array_map(
                function ($word) {
                    return ucwords($word);
                },
                $str
            )
        );
        return $str;
    }
}
