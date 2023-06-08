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

use function is_array;

/*
 * Messenger Trait is loaded in Firewall instance only.
 */
trait MessengerTrait
{
    /**
     * Get options from the configuration file.
     * This method is same as `$this->getConfig()` but returning value from array directly.
     *
     * @param string $option  The option of the section in the the configuration.
     * @param string $section The section in the configuration.
     *
     * @return mixed
     */
    abstract protected function getOption(string $option, string $section = '');

    /**
     * Set the messenger modules.
     *
     * @return void
     */
    protected function setupMessengers(): void
    {
        $messengerList = [
            'telegram',
            'line_notify',
            'sendgrid',
            'native_php_mail',
            'smtp',
            'mailgun',
            'rocket_chat',
            'slack',
            'slack_webhook',
        ];

        foreach ($messengerList as $messenger) {
            $setting = $this->getOption($messenger, 'messengers');

            if (is_array($setting)) {
                // Initialize messenger instances from the factory/
                if (MessengerFactory::check($setting)) {
                    $this->kernel->setMessenger(
                        MessengerFactory::getInstance(
                            // The ID of the messenger module in the configuration.
                            $messenger,
                            // The settings of the messenger module in the configuration.
                            $setting
                        )
                    );
                }
            }

            unset($setting);
        }
    }

    /**
     * Set messenger events.
     *
     * @return void
     */
    protected function setupMessageEvents(): void
    {
        $setting = $this->getOption('failed_attempts_in_a_row', 'events');

        $notifyDataCircle = $setting['data_circle']['messenger'] ?: false;
        $notifySystemFirewall = $setting['system_firewall']['messenger'] ?: false;

        $this->kernel->setProperty(
            'deny_attempt_notify',
            [
                'data_circle'     => $notifyDataCircle,
                'system_firewall' => $notifySystemFirewall,
            ]
        );
    }
}
