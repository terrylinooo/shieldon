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
    }

    public function testResponse()
    {
        $_SESSION['shieldon_image_captcha_hash'] = '$argon2i$v=19$m=1024,t=2,p=2$RWVKVXJ4T3NHQWZqRXFaUg$Oh6emqczEG0+UffyT2+t2XmKBufSmkxcIaGMmUv+7oE';
        $_POST['shieldon_image_captcha'] = '';

        $captchaInstance = new ImageCaptcha();
        $result = $captchaInstance->response();

        $this->assertFalse($result);

        $_POST['shieldon_image_captcha'] = 'IA63BXxo';

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