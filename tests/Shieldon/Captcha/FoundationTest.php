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


class FoundationTest extends \PHPUnit\Framework\TestCase
{
    public function testResponse()
    {
        $captchaInstance = new Foundation();

        $result = $captchaInstance->response();
        $this->assertFalse($result);

        $_POST['shieldon_captcha'] = 'ok';

        $result = $captchaInstance->response();
        $this->assertTrue($result);
    }

    public function testForm()
    {
        $html  = '<input id="shieldon-captcha-example" type="hidden" name="shieldon_captcha">';
        $html .= '<script>document.getElementById("shieldon-captcha-example").value = "ok";</script>';

        $captchaInstance = new Foundation();

        $result = $captchaInstance->form();
        $this->assertSame($result, $html);
    }
}