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

namespace Shieldon\FirewallTest\Log;

class ActionLogParsedCacheTest extends \Shieldon\FirewallTest\ShieldonTestCase
{
    public function test__construct()
    {
        try {
            $logger = new \Shieldon\Firewall\Log\ActionLogParsedCache(BOOTSTRAP_DIR . '/../tmp/shieldon');
        } catch (\Exception $e) {
            $this->assertTrue(false);
        }
    }

    public function testSaveAndGet()
    {
        $logger = new \Shieldon\Firewall\Log\ActionLogParsedCache(BOOTSTRAP_DIR . '/../tmp/shieldon');

        foreach (['yesterday', 'last_month', 'this_month', 'past_seven_hours', 'today'] as $period) {
            $data['foo'] = 'bar';
            $logger->save($period, $data);
            $s = $logger->get($period);
            $this->assertSame($s['foo'], $data['foo']);
        }
    }
}
