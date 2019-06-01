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

class HeaderTest extends \PHPUnit\Framework\TestCase
{
    public function testSetStrict()
    {
        $headerComponent = new Header();
        $headerComponent->setStrict(false);

        $reflection = new \ReflectionObject($headerComponent);
        $t = $reflection->getProperty('strictMode');
        $t->setAccessible(true);
  
        $this->assertEquals('strictMode' , $t->name);
        $this->assertFalse($t->getValue($headerComponent));
    }

    public function testIsDenied()
    {
        $headerComponent = new Header();

        $_SERVER['HTTP_TEST_VAR'] = 'This is a test string.';
        $headerComponent->setDeniedItem('test');

        $result = $headerComponent->isDenied();
        $this->assertTrue($result);

        $_SERVER['HTTP_TEST_VAR'] = 'This is a t2est string.';

        $result = $headerComponent->isDenied();
        $this->assertFalse($result);
    }

    public function testGetHeaders()
    {
        $headerComponent = new Header();

        $_SERVER['HTTP_TEST_VAR'] = 'This is a test string.';
        $_SERVER['HTTP_TEST_VAR2'] = 'This is a testt string.';

        $results = $headerComponent->getHeaders();

        $this->assertSame($results, [
            'Test-Var' => 'This is a test string.',
            'Test-Var2' => 'This is a testt string.',
        ]);
    }

    public function testSetDeniedList()
    {
        $list = ['gzip', 'robot'];

        $headerComponent = new Header();
        $headerComponent->setDeniedList($list);

        $deniedList = $headerComponent->getDeniedList();

        $this->assertSame($deniedList, $list);
    }


    public function testSetDeniedItem()
    {
        $string = 'gizp';

        $headerComponent = new Header();
        $headerComponent->setDeniedItem($string);

        $deniedList = $headerComponent->getDeniedList();

        if (in_array($string, $deniedList)) {
            $this->assertTrue(true);
        } else {
            $this->assertTrue(false);
        }
    }

    public function testGetDeniedList()
    {
        $headerComponent = new Header();
        $deniedList = $headerComponent->getDeniedList();

        $this->assertSame($deniedList, []);
    }

    public function testRemoveItem()
    {
        $string = 'gzip';

        $headerComponent = new Header();
        $headerComponent->setDeniedItem($string);

        $deniedList = $headerComponent->getDeniedList();

        $this->assertSame($deniedList, ['gzip']);

        $headerComponent->removeItem('gzip');
        $deniedList = $headerComponent->getDeniedList();

        $this->assertSame($deniedList, []);
    }
}
