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
     * @param array $args
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
    private function sandbox($getParams, $message)
    {
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
                return false;
            }
        }

        if (!$this->checkHost($host)) {
            return false;
        }

        if ('ssl' === $type || 'tls' === $type) {
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

        if ($messenger->send($message['body'])) {
            return true;
        }
        
        return false;
    }

    /**
     * Check the SMTP host.
     *
     * @param string $host The IP address or server domain name.
     *
     * @return bool
     */
    private function checkHost(string $host): bool
    {
        if (
            !filter_var($host, FILTER_VALIDATE_IP) && 
            !filter_var($host, FILTER_VALIDATE_DOMAIN)
        ) {
            return false;
        }
        return true;
    }
}