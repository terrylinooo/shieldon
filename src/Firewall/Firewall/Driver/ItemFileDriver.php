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

namespace Shieldon\Firewall\Firewall\Driver;

use Shieldon\Firewall\Driver\FileDriver;

/**
 * Get File driver.
 */
class ItemFileDriver
{
    /**
     * Initialize and get the instance.
     *
     * @param array $setting The configuration of that driver.
     *
     * @return FileDriver|null
     */
    public static function get(array $setting)
    {
        $instance = null;

        if (empty($setting['directory_path'])) {
            return $instance;
        }

        $instance = new FileDriver($setting['directory_path']);

        return $instance;
    }
}
