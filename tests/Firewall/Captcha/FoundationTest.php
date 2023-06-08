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

namespace Shieldon\FirewallTest\Captcha;

class FoundationTest extends \Shieldon\FirewallTest\ShieldonTestCase
{
    public function testResponse()
    {
        $captchaInstance = new \Shieldon\Firewall\Captcha\Foundation();

        $result = $captchaInstance->response();
        $this->assertFalse($result);

        $_POST['shieldon_captcha'] = 'ok';
        $this->refreshRequest();

        $result = $captchaInstance->response();
        $this->assertTrue($result);
    }

    public function testForm()
    {
        $html  = '<input id="shieldon-captcha-example" type="hidden" name="shieldon_captcha">';
        $html .= '<script>document.getElementById("shieldon-captcha-example").value = "ok";</script>';

        $captchaInstance = new \Shieldon\Firewall\Captcha\Foundation();

        $result = $captchaInstance->form();
        $this->assertSame($result, $html);
    }
}
