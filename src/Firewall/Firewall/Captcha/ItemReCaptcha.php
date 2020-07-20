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

namespace Shieldon\Firewall\Firewall\Captcha;

use Shieldon\Firewall\Captcha\CaptchaInterface;
use Shieldon\Firewall\Captcha\ImageCaptcha;

/**
 * Get File driver.
 */
class ItemReCaptcha
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
        $type = $setting['config']['type'] ?? 'alnum';
        $length = $setting['config']['length'] ?? 8;

        switch ($type) {
            case 'numeric':
                $imageCaptchaConfig['pool'] = '0123456789';
                break;

            case 'alpha':
                $imageCaptchaConfig['pool'] = '0123456789abcdefghijklmnopqrstuvwxyz';
                break;

            case 'alnum':
            default:
                $imageCaptchaConfig['pool'] = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        }

        $imageCaptchaConfig['word_length'] = $length;

        return new ImageCaptcha($imageCaptchaConfig);
    }
}