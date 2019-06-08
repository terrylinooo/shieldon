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


class RecaptchaTest extends \PHPUnit\Framework\TestCase
{
    public function test__construct()
    {
        $captchaConfig = [
            'key' => '6LeIxAcTAAAAAJcZVRqyHh71UMIEGNQ_MXjiZKhI',
            'secret' => '6LeIxAcTAAAAAGG-vFI1TnRWxMZNFuojJ4WifJWe',
        ];
        
        $captchaInstance = new Recaptcha($captchaConfig);

        $reflection = new \ReflectionObject($captchaInstance);
        $p1 = $reflection->getProperty('key');
        $p1->setAccessible(true);
        $p2 = $reflection->getProperty('secret');
        $p2->setAccessible(true);
     
        $key = $p1->getValue($captchaInstance);
        $secret = $p2->getValue($captchaInstance);

        $this->assertSame($key, '6LeIxAcTAAAAAJcZVRqyHh71UMIEGNQ_MXjiZKhI');
        $this->assertSame($secret, '6LeIxAcTAAAAAGG-vFI1TnRWxMZNFuojJ4WifJWe');
    }

    public function testResponse()
    {
        $captchaConfig = [
            'key' => '6LeIxAcTAAAAAJcZVRqyHh71UMIEGNQ_MXjiZKhI',
            'secret' => '6LeIxAcTAAAAAGG-vFI1TnRWxMZNFuojJ4WifJWe',
        ];
        
        $captchaInstance = new Recaptcha($captchaConfig);
        $result = $captchaInstance->response();
        $this->assertFalse($result);

        $_POST['g-recaptcha-response'] = 'test';

        $result = $captchaInstance->response();
        $this->assertTrue($result);
    }

    public function testForm()
    {
        $captchaConfig = [
            'key' => '6LeIxAcTAAAAAJcZVRqyHh71UMIEGNQ_MXjiZKhI',
            'secret' => '6LeIxAcTAAAAAGG-vFI1TnRWxMZNFuojJ4WifJWe',
        ];
        
        $captchaInstance = new Recaptcha($captchaConfig);
        $result = $captchaInstance->form();

        $html  = '';
        $html .= '<script src="https://www.google.com/recaptcha/api.js?hl=en"></script>';
        $html .= '<div class="g-recaptcha" data-sitekey="6LeIxAcTAAAAAJcZVRqyHh71UMIEGNQ_MXjiZKhI"></div>';

        $this->assertSame($result, $html);

        $captchaConfig = [
            'key' => '6LeIxAcTAAAAAJcZVRqyHh71UMIEGNQ_MXjiZKhI',
            'secret' => '6LeIxAcTAAAAAGG-vFI1TnRWxMZNFuojJ4WifJWe',
            'version' => 'v3',
            'lang' => 'zh',
        ];
        
        $captchaInstance = new Recaptcha($captchaConfig);
        $result = $captchaInstance->form();

        $html  = '';
        $html .= '<input type="hidden" name="g-recaptcha-response" id="g-recaptcha-response" value="">';
        $html .= '<script src="https://www.google.com/recaptcha/api.js?render=6LeIxAcTAAAAAJcZVRqyHh71UMIEGNQ_MXjiZKhI&hl=zh"></script>';
        $html .= '<script>';
        $html .= '    grecaptcha.ready(function() {';
        $html .= '        grecaptcha.execute("6LeIxAcTAAAAAJcZVRqyHh71UMIEGNQ_MXjiZKhI", {action: "homepage"}).then(function(token) {';
        $html .= '            document.getElementById("g-recaptcha-response").value = token;';
        $html .= '        }); ';
        $html .= '    });';
        $html .= '</script>';

        $this->assertSame($result, $html);
    }
}