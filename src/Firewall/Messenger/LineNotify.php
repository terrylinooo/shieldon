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

use Shieldon\Messenger\LineNotify as LineNotifyTest;

/**
 * The sandbox for Line Notify.
 */
class LineNotify
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
     * Test Line Notify.
     *
     * @param array $getParams The GET params passed from tryMessenger method.
     * @param array $message   The message title and body.
     *
     * @return bool
     */
    private function sandbox($getParams, $message)
    {
        $accessToken = $getParams['accessToken'] ?? '';

        if (!empty($accessToken)) {
            $messenger = new LineNotifyTest($accessToken);

            if ($messenger->send($message['body'])) {
                return true;
            }
        }
        return false;
    }
}