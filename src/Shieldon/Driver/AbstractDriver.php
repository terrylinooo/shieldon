<?php declare(strict_types=1);
/*
 * This file is part of the Shieldon package.
 *
 * (c) Terry L. <contact@terryl.in>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Shieldon\Driver;

use ShieldTon\DriverInterface;

/**
 * Abstract Driver.
 */
abstract class AbstractDriver implements DriverInterface
{
    /**
     * Undocumented function
     *
     * @param array $config
     * @return bool
     */
    public function connect(array $config)
    {
        $configDefault = [
            'ip'   => '127.0.0.1',
            'port' => '',
            'user' => '',
            'pass' => '',
        ];

        foreach ($configDefault as $v) {
            if (! empty($config[$v])) {
                $this->{$v} = $config[$v];
            }
        }
    }
}