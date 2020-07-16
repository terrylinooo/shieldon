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

namespace Shieldon\Firewall\Panel\Sandbox;

use Shieldon\Messenger as Messenger;

/**
 * The sandbox for RocketChat.
 */
class RocketChat
{
    /**
     * Invoker.
     *
     * @param array $args
     *
     * @return bool
     */
    public function __invoke(array $args): bool
    {
        return $this->sandbox($args[0], $args[1]);
    }

    /**
     * Test RocketChat.
     *
     * @param array $getParams The GET params passed from tryMessenger method.
     * @param array $message   The message title and body.
     *
     * @return bool
     */
    private function sandbox($getParams, $message)
    {
        $serverUrl = $getParams['serverUrl'] ?? '';
        $userId = $getParams['userId'] ?? '';
        $accessToken = $getParams['accessToken'] ?? '';
        $channel = $getParams['channel'] ?? '';

        if (
            !empty($serverUrl) &&
            !empty($userId) &&
            !empty($accessToken) &&
            !empty($channel)
        ) {
            $messenger = new Messenger\RocketChat($accessToken, $userId, $serverUrl, $channel);
            if ($messenger->send($message['body'])) {
                return true;
            }
        }
        return false;
    }
}