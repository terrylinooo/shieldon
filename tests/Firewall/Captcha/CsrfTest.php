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

class CsrfTest extends \Shieldon\FirewallTest\ShieldonTestCase
{
    public function test__construct()
    {
        $captchaInstance = new \Shieldon\Firewall\Captcha\Csrf([
            'name' => 'pool',
            'value' => '209b131bfec1c01c0f84d858bbf0ff47',
        ]);

        $reflection = new \ReflectionObject($captchaInstance);
        $p1 = $reflection->getProperty('name');
        $p1->setAccessible(true);
        $p2 = $reflection->getProperty('value');
        $p2->setAccessible(true);
     
        $name = $p1->getValue($captchaInstance);
        $value = $p2->getValue($captchaInstance);

        $this->assertSame($name, 'pool');
        $this->assertSame($value, '209b131bfec1c01c0f84d858bbf0ff47');
    }

    public function testResponse()
    {
        $captchaInstance = new \Shieldon\Firewall\Captcha\Csrf([
            'name' => 'pool',
            'value' => '209b131bfec1c01c0f84d858bbf0ff47',
        ]);

        $_POST['pool'] = '209b131bfec1c01c0f84d858bbf0ff47';

        $result = $captchaInstance->response();

        $this->assertTrue($result);
    }

    public function testForm()
    {
        $captchaInstance = new \Shieldon\Firewall\Captcha\Csrf([
            'name' => 'pool',
            'value' => '209b131bfec1c01c0f84d858bbf0ff47',
        ]);

        $result = $captchaInstance->form();
        $this->assertSame($result, '<input type="hidden" name="pool" value="209b131bfec1c01c0f84d858bbf0ff47">');
    }
}
