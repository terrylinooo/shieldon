<?php
/*
 * This file is part of the Messenger package.
 *
 * (c) Terry L. <contact@terryl.in>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Shieldon\FirewallTest\Mock;

use Shieldon\Messenger\Messenger\MessengerInterface;

/**
 * For unit-testing purpose.
 *
 * @author Terry L. <contact@terryl.in>
 * @since 1.0.0
 */
class MockMessenger implements MessengerInterface
{
    /**
     * Constructor.
     */
    public function __construct()
    {
        // Nothing to do.
    }

    /**
     * Send the message.
     *
     * @inheritDoc
     */
    public function send(string $message): bool
    {
        if (!empty($message)) {
            echo "\n" . $this->provider() . "\n";
            echo "\n--- BEGIN - Mock of sending message ---\n\n";
            echo $message;
            echo "\n--- END - Mock of sending message ---\n";

            return true;
        }

        return false;
    }

    /**
     * Get the provider name.
     *
     * @inheritDoc
     */
    public function provider(): string
    {
        return '';
    }

    /**
     * Set the timeout.
     *
     * @inheritDoc
     */
    public function setTimeout(int $timeout = 0): void
    {
    }
}
