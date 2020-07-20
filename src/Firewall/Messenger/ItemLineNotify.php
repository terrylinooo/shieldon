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
use Shieldon\Messenger\LineNotify;

/**
 * The get for Line Notify.
 */
class ItemLineNotify
{
    /**
     * Initialize and get the instance.
     *
     * @param array $setting The configuration of that messanger.
     *
     * @return MessengerInterface
     */
    public static function get(array $setting): MessengerInterface
    {
        $accessToken = $setting['config']['access_token'] ?? '';

        return new LineNotify($accessToken);
    }
}