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

namespace Shieldon\Firewall\Captcha;

use Shieldon\Firewall\Captcha\CaptchaProvider;

use function Shieldon\Firewall\get_request;
use function Shieldon\Firewall\unset_superglobal;

/**
 * Basic form.
 */
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
        $postParams = get_request()->getParsedBody();
 
        if (empty($postParams['shieldon_captcha'])) {
            return false;
        }

        $flag = false;

        if ($postParams['shieldon_captcha'] === 'ok') {
            $flag = true;
        }

        // Prevent detecting POST method on RESTful frameworks.
        unset_superglobal('shieldon_captcha', 'post');

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
