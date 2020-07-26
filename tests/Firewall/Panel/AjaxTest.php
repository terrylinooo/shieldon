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

namespace Shieldon\FirewallTest\Panel;

class AjaxTest extends \Shieldon\FirewallTest\ShieldonTestCase
{
    use RouteTestTrait;

    public function testChangeLocalePage()
    {
        $_SERVER['REQUEST_URI'] = '/firewall/panel/ajax/changeLocale';

        $this->route();

        $this->expectOutputString('{"status":"success","lang_code":"en","session_lang_code":"en"}');
    }

    public function testTryMessenger()
    {
        $_SERVER['REQUEST_URI'] = '/firewall/panel/ajax/tryMessenger';

        $this->route();

        $this->expectOutputString('{"status":"undefined","result":{"moduleName":"","postKey":"messengers____confirm_test"}}');
    }
}