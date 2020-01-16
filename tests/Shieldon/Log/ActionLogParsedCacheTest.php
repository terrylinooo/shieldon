<?php declare(strict_types=1);
/*
 * This file is part of the Shieldon package.
 *
 * (c) Terry L. <contact@terryl.in>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Shieldon\Log;

class ActionLogParsedCacheTest extends \PHPUnit\Framework\TestCase
{
    public function test__construct()
    {
        try {
            $logger = new ActionLogParsedCache(BOOTSTRAP_DIR . '/../tmp/shieldon');
        } catch(\Exception $e) {
            $this->assertTrue(false);
        }
    }

    public function testSaveAndGet()
    {
        $logger = new ActionLogParsedCache(BOOTSTRAP_DIR . '/../tmp/shieldon');

        foreach(['yesterday', 'last_month', 'this_month', 'past_seven_hours', 'today'] as $period) {
            $data['foo'] = 'bar';
            $logger->save($period, $data);
            $s = $logger->get($period);
            $this->assertSame($s['foo'], $data['foo']);
        }
    }
}