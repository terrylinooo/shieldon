<?php defined('SHIELDON_VIEW') || exit('Life is short, why are you wasting time?');
/*
 * This file is part of the Shieldon package.
 *
 * (c) Terry L. <contact@terryl.in>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use function Shieldon\_e;

?>
<div class="section-title bg-glass">
    <h2><?php _e('panel', 'messenger_heading_events', 'Events'); ?></h2>
</div>
<div class="section-body my-0" data-parent="daemon-section">
<p><?php _e('panel', 'messenger_desc_events', 'When they occur, what are the events that you would like to receive notifications sent by Messenger modules.'); ?></p>
    <table class="setting-table">
        <tr>
            <td class="r1"><?php _e('panel', 'messenger_label_event_1', 'Ban a user permanently in current data cycle.'); ?></td>
            <td class="r2">
                <p><?php _e('panel', 'messenger_desc_event_1', 'This event is triggered typically when a user fails too many times due to invalid CAPTCHA in a row.'); ?></p>
                <label class="rocker rocker-sm">
                    <input type="hidden" name="events__failed_attempts_in_a_row__data_circle__messenger" value="off" />
                    <input type="checkbox" name="events__failed_attempts_in_a_row__data_circle__messenger" class="toggle-block" value="on" <?php $this->checked('events.failed_attempts_in_a_row.data_circle.messenger', true); ?>>
                    <span class="switch-left">ON</span>
                    <span class="switch-right">OFF</span>
                </label>
            </td>
        </tr>
    </table>
</div>
<div class="section-body my-0" data-parent="daemon-section">
    <table class="setting-table">
        <tr>
            <td class="r1"><?php _e('panel', 'messenger_label_event_2', 'Ban a user permanently in system firwall'); ?></td>
            <td class="r2">
                <p><?php _e('panel', 'messenger_desc_event_2', 'This event is triggered typically when a user is already banned permanently in curent data cycle, but they are still access the warning pages too many times in a row, we can confirm that they are malicious bots.'); ?></p>
                <label class="rocker rocker-sm">
                    <input type="hidden" name="events__failed_attempts_in_a_row__system_firewall__messenger" value="off" />
                    <input type="checkbox" name="events__failed_attempts_in_a_row__system_firewall__messenger" class="toggle-block" value="on" <?php $this->checked('events.failed_attempts_in_a_row.system_firewall.messenger', true); ?>>
                    <span class="switch-left">ON</span>
                    <span class="switch-right">OFF</span>
                </label>
            </td>
        </tr>
    </table>
</div>