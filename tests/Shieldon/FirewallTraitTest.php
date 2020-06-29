<?php declare(strict_types=1);
/*
 * This file is part of the Shieldon package.
 *
 * (c) Terry L. <contact@terryl.in>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Shieldon;

use PHPUnit\Framework\TestCase;

class FirewallTraitTest extends TestCase
{
    public function testConfig()
    {
        $mock = $this->getMockForTrait('Shieldon\FirewallTrait');
        $mock->setConfig('a', 'one');
        $mock->setConfig('b.c', 'two');
        $mock->setConfig('d.e.f', 'three');
        $mock->setConfig('g.h.i.j', 'four');
        $mock->setConfig('k.l.m.o.p', 'five');
        $mock->setConfig('q.r.s.t.u.v', 'six');

        $a = $mock->getConfig('a');
        $b = $mock->getConfig('b.c');
        $c = $mock->getConfig('d.e.f');
        $d = $mock->getConfig('g.h.i.j');
        $e = $mock->getConfig('k.l.m.o.p');
        $f = $mock->getConfig('q.r.s.t.u.v');

        $this->assertSame($a, 'one');
        $this->assertSame($b, 'two');
        $this->assertSame($c, 'three');
        $this->assertSame($d, 'four');
        $this->assertSame($e, 'five');
        $this->assertSame($f, '');
    }
}