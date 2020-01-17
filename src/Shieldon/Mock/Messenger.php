<?php declare(strict_types=1);
/*
 * This file is part of the Messenger package.
 *
 * (c) Terry L. <contact@terryl.in>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Shieldon\Mock;

use Messenger\MessengerInterface;

/**
 * For unit-testing purpose.
 * 
 * @author Terry L. <contact@terryl.in>
 * @since 1.0.0
 */
class Messenger implements MessengerInterface
{
    /**
     * Constructor.
     */
    public function __construct()
    {
        // Nothing to do.
    }

    /**
     * @inheritDoc
     */
    public function send(string $message, array $logData = []): void
    {
        echo "\n" . $this->provider() . "\n";;

        if (! empty($logData)) {
            $message .= "\n";

            foreach ($logData as $key => $value) {
                $message .= $key . ': ' . $value . "\n";
            }
        }

        echo "\n--- BEGIN - Mock of sending message ---\n\n";
        echo $message;
        echo "\n--- END - Mock of sending message ---\n";
    }

    /**
     * @inheritDoc
     */
    public function provider(): string
    {
        return '';
    }
}