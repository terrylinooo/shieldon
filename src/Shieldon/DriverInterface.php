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

interface Driver
{
    /**
     * Checks whether the given record will be handled by this handler.
     *
     * @param array $config Configuration setting for storage driver.
     *
     * @return bool
     */
    public function connect(array $config): bool;

}


