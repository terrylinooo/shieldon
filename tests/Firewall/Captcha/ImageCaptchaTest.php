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

use Shieldon\Firewall\Helpers;

class ImageCaptchaTest extends \Shieldon\FirewallTest\ShieldonTestCase
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

        $captchaInstance = new \Shieldon\Firewall\Captcha\ImageCaptcha($config);

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
                'grid' => '153',
            ],
        ];

        $captchaInstance = new \Shieldon\Firewall\Captcha\ImageCaptcha($config);

        $reflection = new \ReflectionObject($captchaInstance);
        $p = $reflection->getProperty('properties');
        $p->setAccessible(true);
     
        $results = $p->getValue($captchaInstance);

        $this->assertSame($results['colors']['text'], [20, 153, 255]);
        $this->assertSame($results['colors']['grid'], [153, 200, 255]);
    }

    public function testResponse()
    {
        $this->mockSession();
        
        new Helpers();

        $this->mockUserSession(
            'shieldon_image_captcha_hash',
            '$2y$10$fg4oDCcCUY.w2OJUCzR/SubQ1tFP8QFIladHwlexF1.ye.8.fEAP.'
        );

        //$_SESSION['shieldon_image_captcha_hash'] = '$2y$10$fg4oDCcCUY.w2OJUCzR/SubQ1tFP8QFIladHwlexF1.ye.8.fEAP.';
        $_POST['shieldon_image_captcha'] = 'hh';
        $this->refreshRequest();

        $captchaInstance = new \Shieldon\Firewall\Captcha\ImageCaptcha();
        $result = $captchaInstance->response();

        $this->assertFalse($result);
    }

    public function testResponse2()
    {
        $this->mockSession();

        new Helpers();

        $this->mockUserSession(
            'shieldon_image_captcha_hash',
            '$2y$10$fg4oDCcCUY.w2OJUCzR/SubQ1tFP8QFIladHwlexF1.ye.8.fEAP.'
        );

        $_POST['shieldon_image_captcha'] = 'IA63BXxo';
        $this->refreshRequest();

        $captchaInstance = new \Shieldon\Firewall\Captcha\ImageCaptcha();
        $result = $captchaInstance->response();
        $this->assertTrue($result);
    }

    public function testForm()
    {
        $this->mockSession();
        
        $config = [
            'colors' => '',
        ];

        $captchaInstance = new \Shieldon\Firewall\Captcha\ImageCaptcha($config);

        $result = $captchaInstance->form();
        $this->assertStringContainsString('base64', $result);
    }
}
