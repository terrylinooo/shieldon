<?php defined('SHIELDON_VIEW') || exit('Life is short, why are you wasting time?');
/*
 * This file is part of the Shieldon package.
 *
 * (c) Terry L. <contact@terryl.in>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use function Shieldon\Helper\_e;

?>
<div class="section-title bg-glass">
    <h2><?php _e('panel', 'messenger_heading_telegram', 'Telegram'); ?></h2>
    <div class="toggle-container">
        <label class="rocker rocker-md">
            <input type="hidden" name="messengers__telegram__enable" value="off" />
            <input type="checkbox" name="messengers__telegram__enable" class="toggle-block" value="on" data-target="messenger-telegram-section" <?php $this->checked('messengers.telegram.enable', true); ?> />
            <span class="switch-left">ON</span>
            <span class="switch-right">OFF</span>
        </label>
    </div>
</div>
<div class="section-body my-0" data-parent="messenger-telegram-section">
    <table class="setting-table">
        <tr>
            <td class="r1"><?php _e('panel', 'messenger_label_api_key', 'API Key'); ?></td>
            <td class="r2">
                <input type="text" name="messengers__telegram__config__api_key" class="form-control form-control-sm col-sm-3" value="<?php $this->_('messengers.telegram.config.api_key'); ?>"><br />
            </td>
        </tr>
    </table>
</div>
<div class="section-body my-0" data-parent="messenger-telegram-section">
    <table class="setting-table">
        <tr>
            <td class="r1"><?php _e('panel', 'messenger_label_channel', 'Channel'); ?></td>
            <td class="r2">
                <input type="text" name="messengers__telegram__config__channel" class="form-control form-control-sm col-sm-3" value="<?php $this->_('messengers.telegram.config.channel'); ?>"><br />
            </td>
        </tr>
    </table>
</div>
<!-------------------------------------------------------------------------------------------------------------->
<div class="section-title bg-glass mt-3">
    <h2><?php _e('panel', 'messenger_heading_line_notify', 'Line Notify'); ?></h2>
    <div class="toggle-container">
        <label class="rocker rocker-md">
            <input type="hidden" name="messengers__line_notify__enable" value="off" />
            <input type="checkbox" name="messengers__line_notify__enable" class="toggle-block" value="on" data-target="messenger-linenotify-section" <?php $this->checked('messengers.line_notify.enable', true); ?> />
            <span class="switch-left">ON</span>
            <span class="switch-right">OFF</span>
        </label>
    </div>
</div>
<div class="section-body my-0" data-parent="messenger-linenotify-section">
    <table class="setting-table">
        <tr>
            <td class="r1"><?php _e('panel', 'messenger_label_access_token', 'Access Token'); ?></td>
            <td class="r2">
                <input type="text" name="messengers__line_notify__config__access_token" class="form-control form-control-sm col-sm-3" value="<?php $this->_('messengers.line_notify.config.access_token'); ?>"><br />
            </td>
        </tr>
    </table>
</div>
<!-------------------------------------------------------------------------------------------------------------->
<div class="section-title bg-glass mt-3">
    <h2><?php _e('panel', 'messenger_heading_sendgrid', 'SendGrid'); ?></h2>
    <div class="toggle-container">
        <label class="rocker rocker-md">
            <input type="hidden" name="messengers__sendgrid__enable" value="off" />
            <input type="checkbox" name="messengers__sendgrid__enable" class="toggle-block" value="on" data-target="messenger-sendgrid-section" <?php $this->checked('messengers.sendgrid.enable', true); ?> />
            <span class="switch-left">ON</span>
            <span class="switch-right">OFF</span>
        </label>
    </div>
</div>
<div class="section-body my-0" data-parent="messenger-sendgrid-section">
    <table class="setting-table">
        <tr>
            <td class="r1"><?php _e('panel', 'messenger_label_api_key', 'API Key'); ?></td>
            <td class="r2">
                <input type="text" name="messengers__sendgrid__config__api_key" class="form-control form-control-sm col-sm-3" value="<?php $this->_('messengers.sendgrid.config.api_key'); ?>"><br />
            </td>
        </tr>
    </table>
</div>
<div class="section-body my-0" data-parent="messenger-sendgrid-section">
    <table class="setting-table">
        <tr>
            <td class="r1"><?php _e('panel', 'messenger_label_sender', 'Sender'); ?></td>
            <td class="r2">
                <input type="text" name="messengers__sendgrid__config__sender" class="form-control form-control-sm col-sm-3" value="<?php $this->_('messengers.sendgrid.config.sender'); ?>"><br />
            </td>
        </tr>
    </table>
</div>
<div class="section-body my-0" data-parent="messenger-sendgrid-section">
    <table class="setting-table">
        <tr>
            <td class="r1"><?php _e('panel', 'messenger_label_recipients', 'Recipients'); ?></td>
            <td class="r2">
                <textarea rows="5" name="messengers__sendgrid__config__recipients" class="form-control form-control-sm col-sm-3"><?php $this->_('messengers.sendgrid.config.recipients'); ?></textarea><br />
                <p><?php _e('panel', 'messenger_desc_recipients', 'Per email address per line.'); ?></p>
            </td>
        </tr>
    </table>
</div>
