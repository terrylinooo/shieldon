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
use Shieldon\Messenger\Mail;
use function Shieldon\Firewall\__;

/**
 * The get for PHP native mail.
 */
class ItemNativePhpMail
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
        $sender     = $setting['config']['sender'] ?? '';
        $recipients = $setting['config']['recipients'] ?? [];

        $instance = new Mail();
        $instance->setSubject(__('core', 'messenger_text_mail_subject'));
        $instance->addSender($sender);

        foreach ($recipients as $recipient) {
            $instance->addRecipient($recipient);
        }

        return $instance;
    }
}
