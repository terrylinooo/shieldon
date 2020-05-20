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
        $request = new \Shieldon\Mock\MockRequest();
        $request->apply();

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
        $request = new \Shieldon\Mock\MockRequest();
        $request->session->set('shieldon_image_captcha_hash', password_hash('IA63BXxo', PASSWORD_BCRYPT));
        $request->post->set('shieldon_image_captcha', '');
        $request->apply();

        $captchaInstance = new ImageCaptcha();
        $result = $captchaInstance->response();

        $this->assertFalse($result);

        $request->post->set('shieldon_image_captcha', 'IA63BXxo');
        $request->apply();

        $captchaInstance = new ImageCaptcha();
        $result = $captchaInstance->response();
        $this->assertTrue($result);
    }

    public function testForm()
    {
        $request = new \Shieldon\Mock\MockRequest();
        $request->apply();

        $config = [
            'colors' => ''
        ];

        $captchaInstance = new ImageCaptcha($config);

        $result = $captchaInstance->form();
        $this->assertStringContainsString('base64', $result);
    }
}