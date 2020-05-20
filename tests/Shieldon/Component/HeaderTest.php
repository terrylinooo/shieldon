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
        $request = new \Shieldon\Mock\MockRequest();
        $request->server->clear();
        $request->server->set('HTTP_TEST_VAR', 'This is a test string.');
        $request->apply();

        $headerComponent = new Header();
        $headerComponent->setDeniedItem('test');
        $result = $headerComponent->isDenied();

        $this->assertTrue($result);

        $request->server->set('HTTP_TEST_VAR', 'This is a t2est string.');
        $request->apply();

        $headerComponent = new Header();
        $result = $headerComponent->isDenied();

        $this->assertFalse($result);

        $headerComponent->setStrict(true);
        $result = $headerComponent->isDenied();

        $this->assertTrue($result);
    }

    public function testGetHeaders()
    {
        $request = new \Shieldon\Mock\MockRequest();
        $request->server->clear();
        $request->server->set('HTTP_TEST_VAR', 'This is a test string.');
        $request->server->set('HTTP_TEST_VAR2', 'This is a testt string.');
        $request->apply();

        $headerComponent = new Header();
        $results = $headerComponent->getHeaders();

        $this->assertSame($results, [
            'test-var' => 'This is a test string.',
            'test-var2' => 'This is a testt string.',
        ]);

        $request->server->remove('HTTP_TEST_VAR');
        $request->server->remove('HTTP_TEST_VAR2');
        $request->apply();

        $headerComponent = new Header();
        $results = $headerComponent->getHeaders();

        $this->assertSame($results, []);
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

    public function testGetDenyStatusCode()
    {
        $headerComponent = new Header();
        $statusCode = $headerComponent->getDenyStatusCode();

        $this->assertSame(83, $statusCode);
    }
}
