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

namespace Shieldon\Firewall\Panel\get;

use Shieldon\Messenger\Messenger\MessengerInterface;
use Shieldon\Messenger\RocketChat;

/**
 * The get for RocketChat.
 */
class ItemRocketChat
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
        $serverUrl   = $setting['config']['server_url']   ?? '';
        $userId      = $setting['config']['user_id']      ?? '';
        $accessToken = $setting['config']['access_token'] ?? '';
        $channel     = $setting['config']['channel']      ?? '';

        return new RocketChat($accessToken, $userId, $serverUrl, $channel);
    }
}