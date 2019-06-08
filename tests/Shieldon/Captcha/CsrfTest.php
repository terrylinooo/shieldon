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


class CsrfTest extends \PHPUnit\Framework\TestCase
{
    public function test__construct()
    {
        $captchaInstance = new Csrf([
            'name' => 'pooh',
            'value' => '209b131bfec1c01c0f84d858bbf0ff47',
        ]);

        $reflection = new \ReflectionObject($captchaInstance);
        $p1 = $reflection->getProperty('name');
        $p1->setAccessible(true);
        $p2 = $reflection->getProperty('value');
        $p2->setAccessible(true);
     
        $name = $p1->getValue($captchaInstance);
        $value = $p2->getValue($captchaInstance);

        $this->assertSame($name, 'pooh');
        $this->assertSame($value, '209b131bfec1c01c0f84d858bbf0ff47');
    }

    public function testResponse()
    {
        $captchaInstance = new Csrf([
            'name' => 'pooh',
            'value' => '209b131bfec1c01c0f84d858bbf0ff47',
        ]);

        $result = $captchaInstance->response();

        $this->assertTrue($result);
    }

    public function testForm()
    {
        $captchaInstance = new Csrf([
            'name' => 'pooh',
            'value' => '209b131bfec1c01c0f84d858bbf0ff47',
        ]);

        $result = $captchaInstance->form();
        $this->assertSame($result, '<input type="hidden" name="pooh" value="209b131bfec1c01c0f84d858bbf0ff47">');
    }
}