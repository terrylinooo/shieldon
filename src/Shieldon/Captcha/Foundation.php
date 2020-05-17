<?php declare(strict_types=1);
/*
 * This file is part of the Shieldon package.
 *
 * (c) Terry L. <contact@terryl.in>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Shieldon\Captcha;

class Foundation implements CaptchaInterface
{

    /**
     * Constructor.
     *
     * It will implement default configuration settings here.
     *
     * @array $config
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Response the result.
     *
     * @return bool
     */
    public function response(): bool
    {
        if (empty($_POST['shieldon_captcha'])) {
            return false;
        }

        $flag = false;

        if ($_POST['shieldon_captcha'] === 'ok') {
            $flag = true;
        }

        // Prevent detecting POST method on RESTful frameworks.
        unset($_POST['shieldon_captcha']);

        return $flag;
    }

    /**
     * Output a required HTML.
     *
     * @return string
     */
    public function form(): string
    {
        $html  = '<input id="shieldon-captcha-example" type="hidden" name="shieldon_captcha">';
        $html .= '<script>document.getElementById("shieldon-captcha-example").value = "ok";</script>';

        return $html;
    }
}
