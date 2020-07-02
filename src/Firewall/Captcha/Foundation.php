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

namespace Shieldon\Firewall\Captcha;
use function Shieldon\Firewall\get_request;

class Foundation extends CaptchaProvider
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
        $post = get_request()->getParsedBody();

        if (empty($post['shieldon_captcha'])) {
            return false;
        }

        $flag = false;

        if ($post['shieldon_captcha'] === 'ok') {
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
