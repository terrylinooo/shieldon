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

namespace Shieldon\Firewall\Firewall\Messenger;

use Shieldon\Messenger\Messenger\MessengerInterface;
use Shieldon\Messenger\Telegram;

/**
 * The get for Telegram.
 */
class ItemTelegram
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
        $apiKey  = $setting['config']['api_key'] ?? '';
        $channel = $setting['config']['channel'] ?? '';
        
        return new Telegram($apiKey, $channel);
    }
}