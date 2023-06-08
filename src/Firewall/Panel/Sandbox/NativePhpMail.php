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

namespace Shieldon\Firewall\Panel\Sandbox;

use Shieldon\Messenger\Mail as MailTest;
use function explode;
use function filter_var;
use function function_exists;
use function str_replace;

/**
 * The sandbox for PHP native mail.
 */
class NativePhpMail
{
    /**
     * Invoker.
     *
     * @param array $args The arguments.
     *
     * @return bool
     */
    public function __invoke(array $args): bool
    {
        return $this->sandbox($args[0], $args[1]);
    }

    /**
     * Test PHP native mail.
     *
     * @param array $getParams The GET params passed from tryMessenger method.
     * @param array $message   The message title and body.
     *
     * @return bool
     */
    private function sandbox($getParams, $message)
    {
        $sender = $getParams['sender'] ?? '';
        $recipients = $getParams['recipients'] ?? '';

        if (!empty($sender) && !empty($recipients)) {
            $recipients = str_replace("\r", '|', $recipients);
            $recipients = str_replace("\n", '|', $recipients);
            $recipients = explode('|', $recipients);

            $messenger = new MailTest();

            foreach ($recipients as $recipient) {
                if (filter_var($recipient, FILTER_VALIDATE_EMAIL)) {
                    $messenger->addRecipient($recipient);
                }
            }

            if (filter_var($sender, FILTER_VALIDATE_EMAIL)) {
                $messenger->addSender($sender);
            }

            $messenger->setSubject($message['title']);

            if (!defined('PHP_UNIT_TEST') && $messenger->send($message['body'])) {
                // @codeCoverageIgnoreStart
                return true;
                // @codeCoverageIgnoreEnd
            }
        }

        // @codeCoverageIgnoreStart
        return false;
        // @codeCoverageIgnoreEnd
    }
}
