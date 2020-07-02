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

namespace Shieldon\Security;

class httpAuthenticationTest extends \PHPUnit\Framework\TestCase
{
    public function test__construct()
    {
        $_SERVER['REQUEST_URI'] = '/wp-amdin';

        $httpAuthInstance = new httpAuthentication();

        $reflection = new \ReflectionObject($httpAuthInstance);

        $httpAuthInstance->set([
            [
                'url' => '/wp-amdin', 
                'user' => 'wp_shieldon_user',
                'pass' => '$2y$10$eA/S6rH3JDkYV9nrrUvuMOTh8Q/ts33DdCerbNAUpdwtSl3Xq9cQq'
            ],
            [
                'url' => '/phpmyadmin', 
                'user' => 'sqluser',
                'pass' => '$2y$10$eA/S6rH3JDkYV9nrrUvuMOTh8Q/ts33DdCerbNAUpdwtSl3Xq9cQq'
            ],
        ]);

        $t = $reflection->getProperty('protectedUrlList');
        $t->setAccessible(true);
        $protectedUrlList = $t->getValue($httpAuthInstance);

        $this->assertEquals(count($protectedUrlList), 2);
    }
}

