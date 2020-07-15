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

namespace Shieldon\FirewallTest\Panel;

class AjaxTest extends \PHPUnit\Framework\TestCase
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