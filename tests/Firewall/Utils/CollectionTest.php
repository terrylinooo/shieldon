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

namespace Shieldon\FirewallTest\Utils;

class CollectionTest extends \PHPUnit\Framework\TestCase
{
    public function testAll()
    {
        $_SESSION['test1'] = 'a';
        $_SESSION['test2'] = 'b';
        $_SESSION['test3'] = 'c';
        $_SESSION['test4'] = 'd';
        $_SESSION['test5'] = 'e';
        $_SESSION['test6'] = 'f';

        $session = new \Shieldon\Firewall\Utils\Session();
        $sess = $session->createFromGlobal();

        $this->assertSame($sess->get('test1'), 'a');
        $this->assertSame($sess->get('test2'), 'b');
        $this->assertSame($sess->get('test3'), 'c');
        $this->assertSame($sess->get('test4'), 'd');
        $this->assertSame($sess->get('test5'), 'e');
        $this->assertSame($sess->get('test6'), 'f');

        $sess->remove('test4');

        $this->assertSame($sess->get('test4'), '');
        $this->assertFalse(isset($_SESSION['test4']));

        $this->assertTrue($sess->has('test2'));

        $sess->clear();

        $this->assertSame($_SESSION, []);
    }
}