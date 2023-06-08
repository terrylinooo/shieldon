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

class ReCaptchaTest extends \Shieldon\FirewallTest\ShieldonTestCase
{
    public function test__construct()
    {
        $captchaConfig = [
            'key' => '6LeIxAcTAAAAAJcZVRqyHh71UMIEGNQ_MXjiZKhI',
            'secret' => '6LeIxAcTAAAAAGG-vFI1TnRWxMZNFuojJ4WifJWe',
        ];
        
        $captchaInstance = new \Shieldon\Firewall\Captcha\ReCaptcha($captchaConfig);

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
        
        $captchaInstance = new \Shieldon\Firewall\Captcha\ReCaptcha($captchaConfig);
        $result = $captchaInstance->response();

        $this->assertFalse($result);

        $_POST['g-recaptcha-response'] = 'test';
        $this->refreshRequest();

        $result = $captchaInstance->response();

        $this->assertTrue($result);
    }

    public function testForm()
    {
        $captchaConfig = [
            'key' => '6LeIxAcTAAAAAJcZVRqyHh71UMIEGNQ_MXjiZKhI',
            'secret' => '6LeIxAcTAAAAAGG-vFI1TnRWxMZNFuojJ4WifJWe',
        ];
        
        $result = (new \Shieldon\Firewall\Captcha\ReCaptcha($captchaConfig))->form();

        $html  = '<div><div style="display: inline-block">';
        $html .= '<script src="https://www.google.com/recaptcha/api.js?hl=en"></script>';
        $html .= '<div class="g-recaptcha" data-sitekey="6LeIxAcTAAAAAJcZVRqyHh71UMIEGNQ_MXjiZKhI"></div>';
        $html .= '</div></div>';

        $this->assertSame($result, $html);

        $captchaConfig = [
            'key' => '6LeIxAcTAAAAAJcZVRqyHh71UMIEGNQ_MXjiZKhI',
            'secret' => '6LeIxAcTAAAAAGG-vFI1TnRWxMZNFuojJ4WifJWe',
            'version' => 'v3',
            'lang' => 'zh',
        ];
        
        $result = (new \Shieldon\Firewall\Captcha\ReCaptcha($captchaConfig))->form();

        $html  = '<div><div style="display: inline-block">';
        $html .= '<input type="hidden" name="g-recaptcha-response" id="g-recaptcha-response" value="">';
        $html .= '<script src="https://www.google.com/recaptcha/api.js?';
        $html .= 'render=6LeIxAcTAAAAAJcZVRqyHh71UMIEGNQ_MXjiZKhI&hl=zh"></script>';
        $html .= '<script>';
        $html .= '    grecaptcha.ready(function() {';
        $html .= '        grecaptcha.execute("6LeIxAcTAAAAAJcZVRqyHh71UMIEGNQ_MXjiZKhI",';
        $html .= ' {action: "homepage"}).then(function(token) {';
        $html .= '            document.getElementById("g-recaptcha-response").value = token;';
        $html .= '        }); ';
        $html .= '    });';
        $html .= '</script>';
        $html .= '</div></div>';

        $this->assertSame($result, $html);
    }
}
