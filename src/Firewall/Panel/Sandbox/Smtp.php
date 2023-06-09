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

use Exception;
use Shieldon\Messenger\Smtp as SmtpTest;
use function explode;
use function filter_var;
use function str_replace;

/**
 * The sandbox for SMTP.
 */
class Smtp
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
     * Test SMTP.
     *
     * @param array $getParams The GET params passed from tryMessenger method.
     * @param array $message   The message title and body.
     *
     * @return bool
     */
    private function sandbox($getParams, $message): bool
    {
        $type = '';
        $host = '';
        $user = '';
        $pass = '';
        $port = '';
        $sender = '';
        $recipients = '';

        $params = [
            'type',
            'host',
            'user',
            'pass',
            'port',
            'sender',
            'recipients',
        ];

        foreach ($params as $param) {
            ${$param} = $getParams[$param] ?? '';

            if (empty(${$param})) {
                // @codeCoverageIgnoreStart
                return false;
                // @codeCoverageIgnoreEnd
            }
        }

        if (!$this->checkHost($host)) {
            return false;
        }

        if (in_array($type, ['ssl', 'tls'])) {
            $host = $type . '://' . $host;
        }

        $recipients = str_replace("\r", '|', $recipients);
        $recipients = str_replace("\n", '|', $recipients);
        $recipients = explode('|', $recipients);

        $messenger = new SmtpTest($user, $pass, $host, (int) $port);

        foreach ($recipients as $recipient) {
            if (filter_var($recipient, FILTER_VALIDATE_EMAIL)) {
                $messenger->addRecipient($recipient);
            }
        }

        if (filter_var($sender, FILTER_VALIDATE_EMAIL)) {
            $messenger->addSender($sender);
        }

        $messenger->setSubject($message['title']);

        // @codeCoverageIgnoreStart
        try {
            if ($messenger->send($message['body'])) {
                return true;
            }
        } catch (Exception $e) {
            return false;
        }
        return false;
    }
    // @codeCoverageIgnoreEnd

    /**
     * Check the SMTP host.
     *
     * @param string $host The IP address or server domain name.
     *
     * @return bool
     */
    private function checkHost(string $host): bool
    {
        if (!filter_var($host, FILTER_VALIDATE_IP) &&
            !filter_var($host, FILTER_VALIDATE_DOMAIN, FILTER_FLAG_HOSTNAME)
        ) {
            return false;
        }
        return true;
    }
}
