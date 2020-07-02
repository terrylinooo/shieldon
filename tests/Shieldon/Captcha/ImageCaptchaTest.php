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

namespace Shieldon\Captcha;

class ImageCaptchaTest extends \PHPUnit\Framework\TestCase
{
    public function test__construct()
    {
        $config = [
            'img_width' => 280,
            'img_height' => 40,
            'word_length' => 6,
            'font_spacing' => 10,
            'pool' => '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ',
            'colors' => [
                'background' => [255, 255, 255],
                'border' => [153, 200, 255],
                'text' => [51, 153, 255],
                'grid' => [153, 200, 255]
            ]
        ];

        $captchaInstance = new ImageCaptcha($config);

        $reflection = new \ReflectionObject($captchaInstance);
        $p = $reflection->getProperty('properties');
        $p->setAccessible(true);
     
        $results = $p->getValue($captchaInstance);

        $this->assertSame($results, $config);

        $config = [
            'colors' => [
                'background' => [255, 255, 255],
                'border' => [153, 200, 255],
                'text' => [20, 153, 255],
                'grid' => '153'
            ]
        ];

        $captchaInstance = new ImageCaptcha($config);

        $reflection = new \ReflectionObject($captchaInstance);
        $p = $reflection->getProperty('properties');
        $p->setAccessible(true);
     
        $results = $p->getValue($captchaInstance);

        $this->assertSame($results['colors']['text'], [20, 153, 255]);
        $this->assertSame($results['colors']['grid'], [153, 200, 255]);
    }

    public function testResponse()
    {
        $_SESSION['shieldon_image_captcha_hash'] = '$2y$10$fg4oDCcCUY.w2OJUCzR/SubQ1tFP8QFIladHwlexF1.ye.8.fEAP.';
        $_POST['shieldon_image_captcha'] = '';
        reload_request();

        $captchaInstance = new ImageCaptcha();
        $result = $captchaInstance->response();

        $this->assertFalse($result);

        $_POST['shieldon_image_captcha'] = 'IA63BXxo';
        reload_request();

        $result = $captchaInstance->response();
        $this->assertTrue($result);
    }

    public function testForm()
    {
        $config = [
            'colors' => ''
        ];

        $captchaInstance = new ImageCaptcha($config);

        $result = $captchaInstance->form();
        $this->assertStringContainsString('base64', $result);
    }
}