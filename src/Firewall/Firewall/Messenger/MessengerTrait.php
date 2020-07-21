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

namespace Shieldon\Firewall\Firewall\Messenger;

/*
 * Messenger Trait is loaded in Firewall instance only.
 */
trait MessengerTrait
{
    /**
     * Fetch value from configuration.
     *
     * @param string $option
     * @param string $section
     *
     * @return mixed
     */
    abstract function getOption(string $option, string $section = '');

    /**
     * Set the messenger modules.
     *
     * @return void
     */
    protected function setMessengers(): void
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
                if (MessengerFactory::check($messenger, $setting)) {
    
                    $this->kernel->add(
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
    protected function setMessageEvents(): void
    {
        $setting = $this->getOption('failed_attempts_in_a_row', 'events');

        $notifyDataCircle = $setting['data_circle']['messenger'] ?: false;
        $notifySystemFirewall = $setting['system_firewall']['messenger'] ?: false;

        $this->kernel->setProperty('deny_attempt_notify', [
            'data_circle' => $notifyDataCircle,
            'system_firewall' => $notifySystemFirewall,
        ]);
    }
}
