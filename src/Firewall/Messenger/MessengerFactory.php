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

namespace Shieldon\Firewall\Messenger;

use Shieldon\Messenger\Messenger\MessengerInterface;

/*
 * The factory creates messenger instances.
 */
class MessengerFactory
{
    /**
     * Create a messenger instance.
     *
     * @param string $messenger The messenger's ID string.
     * @param array  $setting   The configuration of that messanger.
     *
     * @return MessengerInterface
     */
    public static function getInstance(string $messenger, array $setting): MessengerInterface
    {
        $className = 'Item' . self::getCamelCase($messenger);

        return $className::get($setting);
    }

    /**
     * Check whether a messenger is available or not.
     *
     * @param string $messenger The messenger's ID string.
     * @param array  $setting   The configuration of that messanger.
     *
     * @return bool
     */
    public static function check(string $messenger, array $setting): bool
    {
        // If the settings is not set correctly.
        if (empty($setting['enable']) || empty($setting['confirm_test'])) {
            return false;
        }

        // If the class doesn't exist.
        if (!file_exists(__DIR__ . '/' . self::getCamelCase($messenger) . '.php')) {
            return false;
        }

        return true;
    }

    /**
     * Covert string with dashes into camel-case string.
     *
     * @param string $string A string with dashes.
     *
     * @return string
     */
    public static function getCamelCase(string $string = '')
    {
        $str = explode('-', $string);
        $str = implode('', array_map(function($word) {
            return ucwords($word); 
        }, $str));

        return $str;
    }
}