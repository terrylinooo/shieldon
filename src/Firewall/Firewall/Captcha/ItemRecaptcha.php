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
use Shieldon\Firewall\Captcha\ReCaptcha;

/**
 * Get File driver.
 */
class ItemRecaptcha
{
    /**
     * Initialize and get the instance.
     *
     * @param array $setting The configuration of that driver.
     *
     * @return CaptchaInterface
     */
    public static function get(array $setting): CaptchaInterface
    {
        $recaptchaSetting = [
            'key'     => $setting['config']['site_key'],
            'secret'  => $setting['config']['secret_key'],
            'version' => $setting['config']['version'],
            'lang'    => $setting['config']['lang'],
        ];

        return new ReCaptcha($recaptchaSetting);
    }
}
