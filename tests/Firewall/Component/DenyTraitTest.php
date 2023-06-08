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

namespace Shieldon\FirewallTest\Component;

class DenyTraitTest extends \Shieldon\FirewallTest\ShieldonTestCase
{
    public function testGetDeniedItems()
    {
        $rdns = new \Shieldon\Firewall\Component\Rdns();
   
        $deniedList = $rdns->getDeniedItems();
        $rdnsList = [
            'unknown_1' => '.webcrawler.link',
        ];

        $this->assertEquals($deniedList, $rdnsList);
    }

    public function testGetDeniedItem()
    {
        $rdns = new \Shieldon\Firewall\Component\Rdns();
   
        $deniedList = $rdns->getDeniedItem('unknown_1');

        $rdnsList = '.webcrawler.link';

        $this->assertEquals($deniedList, $rdnsList);
    }

    public function testRemoveDeniedItem()
    {
        $rdns = new \Shieldon\Firewall\Component\Rdns();

        $this->assertTrue($rdns->hasDeniedItem('unknown_1'));

        $rdns->removeDeniedItem('unknown_1');

  
        $deniedList = $rdns->getDeniedItems();

        $this->assertEquals($deniedList, []);
    }

    public function testRemoveDeniedItems()
    {
        $rdns = new \Shieldon\Firewall\Component\Rdns();

        $rdns->removeDeniedItems();

        $deniedList = $rdns->getDeniedItems();

        $this->assertEquals($deniedList, []);
    }

    public function testgetDenyWithPrefix()
    {
        $rdns = new \Shieldon\Firewall\Component\Rdns();

        $deniedList = $rdns->getDenyWithPrefix('unknown');

        $rdnsList = [
            'unknown_1' => '.webcrawler.link',
        ];

        $this->assertEquals($deniedList, $rdnsList);
    }

    // removeDenyWithPrefix

    public function testremoveDenyWithPrefix()
    {
        $rdns = new \Shieldon\Firewall\Component\Rdns();

        $rdns->removeDenyWithPrefix('unknown');

        $rdnsList = [];

        $deniedList = $rdns->getDeniedItems();

        $this->assertEquals($deniedList, $rdnsList);
    }
}
