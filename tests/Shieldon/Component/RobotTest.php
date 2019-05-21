<?php declare(strict_types=1);
/*
 * This file is part of the Shieldon package.
 *
 * (c) Terry L. <contact@terryl.in>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Shieldon\Component;

class RobotTest extends \PHPUnit\Framework\TestCase
{
    public function testSetStrict()
    {
        $robotComponent = new Robot();
        $robotComponent->setStrict(false);

        $reflection = new \ReflectionObject($robotComponent);
        $t = $reflection->getProperty('strictMode');
        $t->setAccessible(true);
  
        $this->assertEquals('strictMode' , $t->name);
        $this->assertFalse($t->getValue($robotComponent));
    }

    public function testIsDenied()
    {
        $robotComponent = new Robot();

        // By default, we ban Wayback Machine robot.
        $robotComponent->setUserAgent('archive.org_bot');
        $t = $robotComponent->isDenied();
        $this->assertTrue($t);

        // By default, we don't ban Baidu.
        $robotComponent->setUserAgent('Mozilla/5.0 (compatible; Baiduspider/2.0;+http://www.baidu.com/search/spider.html)');
        $t = $robotComponent->isDenied();
        $this->assertFalse($t);
    }

    public function testIsAllowed()
    {

    }

    public function testIsRobot()
    {

    }

    public function testIsGoogle()
    {

    }

    public function testIsYahoo()
    {

    }

    public function testIsBing()
    {

    }

    public function testIsSearchEngine()
    {

    }

    public function testIsSocialNetwork()
    {

    }
}
