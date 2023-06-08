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

namespace Shieldon\Firewall\Firewall\Messenger;

use Shieldon\Messenger\Messenger\MessengerInterface;
use Shieldon\Messenger\Mailgun;
use function Shieldon\Firewall\__;

/**
 * Get an item of messenger. It is Mailgun.
 */
class ItemMailgun
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
        $apiKey     = $setting['config']['api_key'] ?? '';
        $domain     = $setting['config']['domain_name'] ?? '';
        $sender     = $setting['config']['sender'] ?? '';
        $recipients = $setting['config']['recipients'] ?? [];

        $instance = new Mailgun($apiKey, $domain);
        $instance->setSubject(__('core', 'messenger_text_mail_subject'));
        $instance->addSender($sender);

        foreach ($recipients as $recipient) {
            $instance->addRecipient($recipient);
        }

        return $instance;
    }
}
