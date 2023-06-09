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

namespace Shieldon\Firewall\Kernel;

use Shieldon\Firewall\Kernel\Enum;
use function Shieldon\Firewall\__;
use function Shieldon\Firewall\get_cpu_usage;
use function Shieldon\Firewall\get_memory_usage;
use function file_exists;
use function file_put_contents;
use function filter_var;
use function is_writable;
use function time;

/*
 * @since 1.0.0
 */
trait RuleTrait
{
    /**
     * The events.
     *
     * @var array
     */
    protected $event = [

        // Update rule table when this value true.
        'update_rule_table' => false,

        // Send notifications when this value true.
        'trigger_messengers' => false,
    ];

    /**
     * Set the message body.
     *
     * @param string $message The message text.
     *
     * @return void
     */
    abstract protected function setMessageBody(string $message = ''): void;

    /**
     * Save and return the result identifier.
     * This method is for passing value from traits.
     *
     * @param int $resultCode The result identifier.
     *
     * @return int
     */
    abstract protected function setResultCode(int $resultCode): int;

    /**
     * Look up the rule table.
     *
     * If a specific IP address doesn't exist, return false.
     * Otherwise, return true.
     *
     * @return bool
     */
    protected function isRuleExist()
    {
        $ipRule = $this->driver->get($this->ip, 'rule');

        if (empty($ipRule)) {
            return false;
        }

        $this->reason = $ipRule['reason'];

        $ruleType = (int) $ipRule['type'];

        // Apply the status code.
        $this->setResultCode($ruleType);

        if ($ruleType === Enum::ACTION_ALLOW) {
            return true;
        }

        // Current visitor has been blocked. If he still attempts accessing the site,
        // then we can drop him into the permanent block list.
        $attempts = $ipRule['attempts'] ?? 0;
        $attempts = (int) $attempts;
        $now = time();
        $logData = [];
        $handleType = 0;

        $logData['log_ip']     = $ipRule['log_ip'];
        $logData['ip_resolve'] = $ipRule['ip_resolve'];
        $logData['time']       = $now;
        $logData['type']       = $ipRule['type'];
        $logData['reason']     = $ipRule['reason'];
        $logData['attempts']   = $attempts;

        // @since 0.2.0
        $attemptPeriod = $this->properties['record_attempt_detection_period'];
        $attemptReset  = $this->properties['reset_attempt_counter'];

        $lastTimeDiff = $now - $ipRule['time'];

        if ($lastTimeDiff <= $attemptPeriod) {
            $logData['attempts'] = ++$attempts;
        }

        if ($lastTimeDiff > $attemptReset) {
            $logData['attempts'] = 0;
        }

        if ($ruleType === Enum::ACTION_TEMPORARILY_DENY) {
            $ratd = $this->determineAttemptsTemporaryDeny($logData, $handleType, $attempts);
            $logData = $ratd['log_data'];
            $handleType = $ratd['handle_type'];
        }

        if ($ruleType === Enum::ACTION_DENY) {
            $rapd = $this->determineAttemptsPermanentDeny($logData, $handleType, $attempts);
            $logData = $rapd['log_data'];
            $handleType = $rapd['handle_type'];
        }

        // We only update data when `deny_attempt_enable` is enable.
        // Because we want to get the last visited time and attempt counter.
        // Otherwise, we don't update it everytime to avoid wasting CPU resource.
        if ($this->event['update_rule_table']) {
            $this->driver->save($this->ip, $logData, 'rule');
        }

        // Notify this event to messenger.
        if ($this->event['trigger_messengers']) {
            $message = $this->prepareMessengerBody($logData, $handleType);

            // Method from MessageTrait.
            $this->setMessageBody($message);
        }

        return true;
    }

    /**
     * Record the attempts when the user is temporarily denied by rule table.
     *
     * @param array $logData    The log data.
     * @param int   $handleType The type for i18n string of the message.
     * @param int   $attempts   The attempt times.
     *
     * @return array
     */
    protected function determineAttemptsTemporaryDeny(array $logData, int $handleType, int $attempts): array
    {
        if ($this->properties['deny_attempt_enable']['data_circle']) {
            $this->event['update_rule_table'] = true;

            $buffer = $this->properties['deny_attempt_buffer']['data_circle'];

            if ($attempts >= $buffer) {
                if ($this->properties['deny_attempt_notify']['data_circle']) {
                    $this->event['trigger_messengers'] = true;
                }

                $logData['type'] = Enum::ACTION_DENY;

                // Reset this value for next checking process - iptables.
                $logData['attempts'] = 0;
                $handleType = 1;
            }
        }

        return [
            'log_data' => $logData,
            'handle_type' => $handleType,
        ];
    }

    /**
     * Record the attempts when the user is permanently denied by rule table.
     *
     * @param array $logData    The log data.
     * @param int   $handleType The type for i18n string of the message.
     * @param int   $attempts   The attempt times.
     *
     * @return array
     */
    protected function determineAttemptsPermanentDeny(array $logData, int $handleType, int $attempts): array
    {
        if ($this->properties['deny_attempt_enable']['system_firewall']) {
            $this->event['update_rule_table'] = true;
            // For the requests that are already banned, but they are still attempting access, that means
            // that they are programmably accessing your website. Consider put them in the system-layer fireall
            // such as IPTABLE.
            $bufferIptable = $this->properties['deny_attempt_buffer']['system_firewall'];

            if ($attempts >= $bufferIptable) {
                if ($this->properties['deny_attempt_notify']['system_firewall']) {
                    $this->event['trigger_messengers'] = true;
                }

                $folder = rtrim($this->properties['iptables_watching_folder'], '/');

                if (file_exists($folder) && is_writable($folder)) {
                    $filePath = $folder . '/iptables_queue.log';

                    // command, ipv4/6, ip, subnet, port, protocol, action
                    // add,4,127.0.0.1,null,all,all,drop  (example)
                    // add,4,127.0.0.1,null,80,tcp,drop   (example)
                    $command = 'add,4,' . $this->ip . ',null,all,all,deny';

                    if (filter_var($this->ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6)) {
                        $command = 'add,6,' . $this->ip . ',null,all,allow';
                    }

                    // Add this IP address to itables_queue.log
                    // Use `bin/iptables.sh` for adding it into IPTABLES. See document for more information.
                    file_put_contents($filePath, $command . "\n", FILE_APPEND | LOCK_EX);

                    $logData['attempts'] = 0;
                    $handleType = 2;
                }
            }
        }

        return [
            'log_data' => $logData,
            'handle_type' => $handleType,
        ];
    }

    /**
     * Prepare the message body for messenger modules to sent.
     *
     * @param array $logData    The log data.
     * @param int   $handleType The type for i18n string of the message.
     *
     * @return string
     */
    protected function prepareMessengerBody(array $logData, int $handleType): string
    {
        // The data strings that will be appended to message body.
        $prepareMessageData = [
            __('core', 'messenger_text_ip')       => $logData['log_ip'],
            __('core', 'messenger_text_rdns')     => $logData['ip_resolve'],
            __('core', 'messenger_text_reason')   => __('core', 'messenger_text_reason_code_' . $logData['reason']),
            __('core', 'messenger_text_handle')   => __('core', 'messenger_text_handle_type_' . $handleType),
            __('core', 'messenger_text_system')   => '',
            __('core', 'messenger_text_cpu')      => get_cpu_usage(),
            __('core', 'messenger_text_memory')   => get_memory_usage(),
            __('core', 'messenger_text_time')     => date('Y-m-d H:i:s', $logData['time']),
            __('core', 'messenger_text_timezone') => date_default_timezone_get(),
        ];

        $message = __('core', 'messenger_notification_subject', 'Notification for {0}', [$this->ip]) . "\n\n";

        foreach ($prepareMessageData as $key => $value) {
            $message .= $key . ': ' . $value . "\n";
        }

        return $message;
    }
}
