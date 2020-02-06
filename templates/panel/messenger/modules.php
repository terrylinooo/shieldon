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
<!-------------------------------------------------------------------------------------------------------------->
<div class="section-title bg-glass">
    <h2><?php _e('panel', 'messenger_heading_telegram', 'Telegram'); ?></h2>
    <div class="confirm-test-container">
        <button type="button" class="btn btn-confirm-test" data-module="telegram">Test</button>
        <span id="test-result-telegram"><i class="fas fa-pause"></i></span>
    </div>
    <div class="toggle-container toggle-sm">
        <label class="rocker rocker-sm">
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
                <input type="text" name="messengers__telegram__config__api_key" class="form-control form-control-sm col-sm-6" value="<?php $this->_('messengers.telegram.config.api_key'); ?>"><br />
            </td>
        </tr>
    </table>
</div>
<div class="section-body my-0" data-parent="messenger-telegram-section">
    <table class="setting-table">
        <tr>
            <td class="r1"><?php _e('panel', 'messenger_label_channel', 'Channel'); ?></td>
            <td class="r2">
                <input type="text" name="messengers__telegram__config__channel" class="form-control form-control-sm col-sm-6" value="<?php $this->_('messengers.telegram.config.channel'); ?>"><br />
            </td>
        </tr>
    </table>
</div>
<!-------------------------------------------------------------------------------------------------------------->
<div class="section-title bg-glass mt-3">
    <h2><?php _e('panel', 'messenger_heading_line_notify', 'Line Notify'); ?></h2>
    <div class="confirm-test-container">
        <button type="button" class="btn btn-confirm-test" data-module="linenotify">Test</button>
        <span id="test-result-linenotify"><i class="fas fa-pause"></i></span>
    </div>
    <div class="toggle-container toggle-sm">
        <label class="rocker rocker-sm">
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
                <input type="text" name="messengers__line_notify__config__access_token" class="form-control form-control-sm col-sm-6" value="<?php $this->_('messengers.line_notify.config.access_token'); ?>"><br />
            </td>
        </tr>
    </table>
</div>
<!-------------------------------------------------------------------------------------------------------------->
<div class="section-title bg-glass mt-3">
    <h2><?php _e('panel', 'messenger_heading_slack', 'Slack'); ?></h2>
    <div class="confirm-test-container">
        <button type="button" class="btn btn-confirm-test" data-module="slack">Test</button>
        <span id="test-result-slack"><i class="fas fa-pause"></i></span>
    </div>
    <div class="toggle-container toggle-sm">
        <label class="rocker rocker-sm">
            <input type="hidden" name="messengers__slack__enable" value="off" />
            <input type="checkbox" name="messengers__slack__enable" class="toggle-block" value="on" data-target="messenger-slack-section" <?php $this->checked('messengers.slack.enable', true); ?> />
            <span class="switch-left">ON</span>
            <span class="switch-right">OFF</span>
        </label>
    </div>
</div>
<div class="section-body my-0" data-parent="messenger-slack-section">
    <table class="setting-table">
        <tr>
            <td class="r1"><?php _e('panel', 'messenger_label_bot_token', 'Bot Token'); ?></td>
            <td class="r2">
                <input type="text" name="messengers__slack__config__bot_token" class="form-control form-control-sm col-sm-6" value="<?php $this->_('messengers.slack.config.bot_token'); ?>"><br />
            </td>
        </tr>
    </table>
</div>
<div class="section-body my-0" data-parent="messenger-slack-section">
    <table class="setting-table">
        <tr>
            <td class="r1"><?php _e('panel', 'messenger_label_channel', 'Channel'); ?></td>
            <td class="r2">
                <input type="text" name="messengers__slack__config__channel" class="form-control form-control-sm col-sm-6" value="<?php $this->_('messengers.slack.config.channel'); ?>"><br />
            </td>
        </tr>
    </table>
</div>
<!-------------------------------------------------------------------------------------------------------------->
<div class="section-title bg-glass mt-3">
    <h2><?php _e('panel', 'messenger_heading_slack_webhook', 'Slack Webhook'); ?></h2>
    <div class="confirm-test-container">
        <button type="button" class="btn btn-confirm-test" data-module="slack-webhook">Test</button>
        <span id="test-result-slack-webhook"><i class="fas fa-pause"></i></span>
    </div>
    <div class="toggle-container toggle-sm">
        <label class="rocker rocker-sm">
            <input type="hidden" name="messengers__slack_webhook__enable" value="off" />
            <input type="checkbox" name="messengers__slack_webhook__enable" class="toggle-block" value="on" data-target="messenger-slack-webhook-section" <?php $this->checked('messengers.slack_webhook.enable', true); ?> />
            <span class="switch-left">ON</span>
            <span class="switch-right">OFF</span>
        </label>
    </div>
</div>
<div class="section-body my-0" data-parent="messenger-slack-webhook-section">
    <table class="setting-table">
        <tr>
            <td class="r1"><?php _e('panel', 'messenger_label_webhook_url', 'Webhook URL'); ?></td>
            <td class="r2">
                <input type="text" name="messengers__slack_webhook__config__webhook_url" class="form-control form-control-sm col-sm-6" value="<?php $this->_('messengers.slack_webhook.config.webhook_url'); ?>"><br />
            </td>
        </tr>
    </table>
</div>
<!-------------------------------------------------------------------------------------------------------------->
<div class="section-title bg-glass mt-3">
    <h2><?php _e('panel', 'messenger_heading_rocket_chat', 'Rocket Chat'); ?></h2>
    <div class="confirm-test-container">
        <button type="button" class="btn btn-confirm-test" data-module="rocket-chat">Test</button>
        <span id="test-result-rocket-chat"><i class="fas fa-pause"></i></span>
    </div>
    <div class="toggle-container toggle-sm">
        <label class="rocker rocker-sm">
            <input type="hidden" name="messengers__rocket_chat__enable" value="off" />
            <input type="checkbox" name="messengers__rocket_chat__enable" class="toggle-block" value="on" data-target="messenger-rocketchat-section" <?php $this->checked('messengers.rocket_chat.enable', true); ?> />
            <span class="switch-left">ON</span>
            <span class="switch-right">OFF</span>
        </label>
    </div>
</div>
<div class="section-body my-0" data-parent="messenger-rocketchat-section">
    <table class="setting-table">
        <tr>
            <td class="r1"><?php _e('panel', 'messenger_label_server_url', 'Server URL'); ?></td>
            <td class="r2">
                <input type="text" name="messengers__rocket_chat__config__server_url" class="form-control form-control-sm col-sm-6" value="<?php $this->_('messengers.rocket_chat.config.server_url'); ?>"><br />
            </td>
        </tr>
    </table>
</div>
<div class="section-body my-0" data-parent="messenger-rocketchat-section">
    <table class="setting-table">
        <tr>
            <td class="r1"><?php _e('panel', 'messenger_label_user_id', 'User ID'); ?></td>
            <td class="r2">
                <input type="text" name="messengers__rocket_chat__config__user_id" class="form-control form-control-sm col-sm-6" value="<?php $this->_('messengers.rocket_chat.config.user_id'); ?>"><br />
            </td>
        </tr>
    </table>
</div>
<div class="section-body my-0" data-parent="messenger-rocketchat-section">
    <table class="setting-table">
        <tr>
            <td class="r1"><?php _e('panel', 'messenger_label_access_token', 'Access Token'); ?></td>
            <td class="r2">
                <input type="text" name="messengers__rocket_chat__config__access_token" class="form-control form-control-sm col-sm-6" value="<?php $this->_('messengers.rocket_chat.config.access_token'); ?>"><br />
            </td>
        </tr>
    </table>
</div>
<div class="section-body my-0" data-parent="messenger-rocketchat-section">
    <table class="setting-table">
        <tr>
            <td class="r1"><?php _e('panel', 'messenger_label_channel', 'Channel'); ?></td>
            <td class="r2">
                <input type="text" name="messengers__rocket_chat__config__channel" class="form-control form-control-sm col-sm-6" value="<?php $this->_('messengers.rocket_chat.config.channel'); ?>"><br />
            </td>
        </tr>
    </table>
</div>
<!-------------------------------------------------------------------------------------------------------------->
<div class="section-title bg-glass mt-3">
    <h2><?php _e('panel', 'messenger_heading_smtp', 'SMTP'); ?></h2>
    <div class="confirm-test-container">
        <button type="button" class="btn btn-confirm-test" data-module="smtp">Test</button>
        <span id="test-result-smtp"><i class="fas fa-pause"></i></span>
    </div>
    <div class="toggle-container toggle-sm">
        <label class="rocker rocker-sm">
            <input type="hidden" name="messengers__smtp__enable" value="off" />
            <input type="checkbox" name="messengers__smtp__enable" class="toggle-block" value="on" data-target="messenger-smtp-section" <?php $this->checked('messengers.sendgrid.enable', true); ?> />
            <span class="switch-left">ON</span>
            <span class="switch-right">OFF</span>
        </label>
    </div>
</div>
<div class="section-body my-0" data-parent="messenger-smtp-section">
    <table class="setting-table">
        <tr>
            <td class="r1"><?php _e('panel', 'messenger_label_host', 'Host'); ?></td>
            <td class="r2">
                <input type="text" name="messengers__smtp__config__host" class="form-control form-control-sm col-sm-6" value="<?php $this->_('messengers.smtp.config.host'); ?>"><br />
            </td>
        </tr>
    </table>
</div>
<div class="section-body my-0" data-parent="messenger-smtp-section">
    <table class="setting-table">
        <tr>
            <td class="r1"><?php _e('panel', 'messenger_label_port', 'Port'); ?></td>
            <td class="r2">
                <input type="text" name="messengers__smtp__config__port" class="form-control form-control-sm col-sm-6" value="<?php $this->_('messengers.smtp.config.port'); ?>"><br />
            </td>
        </tr>
    </table>
</div>
<div class="section-body my-0" data-parent="messenger-smtp-section">
    <table class="setting-table">
        <tr>
            <td class="r1"><?php _e('panel', 'messenger_label_type', 'Type'); ?></td>
            <td class="r2">
                <div class="radio-style">
                    <input name="messengers__smtp__config__type" value="" type="radio" id="smtp-type-1" <?php $this->checked('messengers.smtp.config.type', ''); ?> /> 
                    <label for="smtp-type-1" class="radio-label">
                        Non-SSL
                    </label> 
                </div>
                <div class="radio-style">
                    <input name="ip_variable_source" value="messengers__smtp__config__type" type="radio" id="smtp-type-2" <?php $this->checked('messengers.smtp.config.type', 'ssl'); ?> /> 
                    <label for="smtp-type-2" class="radio-label">
                        SSL
                    </label> 
                </div>
                <div class="radio-style">
                    <input name="ip_variable_source" value="messengers__smtp__config__type" type="radio" id="smtp-type-3" <?php $this->checked('messengers.smtp.config.type', 'tls'); ?> /> 
                    <label for="smtp-type-3" class="radio-label">
                        TLS
                    </label> 
                </div>
            </td>
        </tr>
    </table>
</div>
<div class="section-body my-0" data-parent="messenger-smtp-section">
    <table class="setting-table">
        <tr>
            <td class="r1"><?php _e('panel', 'messenger_label_user', 'User'); ?></td>
            <td class="r2">
                <input type="text" name="messengers__smtp__config__user" class="form-control form-control-sm col-sm-6" value="<?php $this->_('messengers.smtp.config.user'); ?>"><br />
            </td>
        </tr>
    </table>
</div>
<div class="section-body my-0" data-parent="messenger-smtp-section">
    <table class="setting-table">
        <tr>
            <td class="r1"><?php _e('panel', 'messenger_label_pass', 'Password'); ?></td>
            <td class="r2">
                <input type="text" name="messengers__smtp__config__pass" class="form-control form-control-sm col-sm-6" value="<?php $this->_('messengers.smtp.config.pass'); ?>"><br />
            </td>
        </tr>
    </table>
</div>
<div class="section-body my-0" data-parent="messenger-smtp-section">
    <table class="setting-table">
        <tr>
            <td class="r1"><?php _e('panel', 'messenger_label_sender', 'Sender'); ?></td>
            <td class="r2">
                <input type="text" name="messengers__sendgrid__config__sender" class="form-control form-control-sm col-sm-6" value="<?php $this->_('messengers.sendgrid.config.sender'); ?>"><br />
            </td>
        </tr>
    </table>
</div>
<div class="section-body my-0" data-parent="messenger-smtp-section">
    <table class="setting-table">
        <tr>
            <td class="r1"><?php _e('panel', 'messenger_label_recipients', 'Recipients'); ?></td>
            <td class="r2">
                <textarea rows="5" name="messengers__sendgrid__config__recipients" class="form-control form-control-sm col-sm-6"><?php $this->_('messengers.sendgrid.config.recipients'); ?></textarea><br />
                <p><?php _e('panel', 'messenger_desc_recipients', 'Per email address per line.'); ?></p>
            </td>
        </tr>
    </table>
</div>
<!-------------------------------------------------------------------------------------------------------------->
<div class="section-title bg-glass mt-3">
    <h2><?php _e('panel', 'messenger_heading_php_mail', 'Native PHP Mail'); ?></h2>
    <div class="confirm-test-container">
        <button type="button" class="btn btn-confirm-test" data-module="native-php-mail">Test</button>
        <span id="test-result-native-php-mail"><i class="fas fa-pause"></i></span>
    </div>
    <div class="toggle-container toggle-sm">
        <label class="rocker rocker-sm">
            <input type="hidden" name="messengers__native_php_mail__enable" value="off" />
            <input type="checkbox" name="messengers__native_php_mail__enable" class="toggle-block" value="on" data-target="messenger-php-mail-section" <?php $this->checked('messengers.native_php_mail.enable', true); ?> />
            <span class="switch-left">ON</span>
            <span class="switch-right">OFF</span>
        </label>
    </div>
</div>
<div class="section-body my-0" data-parent="messenger-php-mail-section">
    <table class="setting-table">
        <tr>
            <td class="r1"><?php _e('panel', 'messenger_label_sender', 'Sender'); ?></td>
            <td class="r2">
                <input type="text" name="messengers__native_php_mail__config__sender" class="form-control form-control-sm col-sm-6" value="<?php $this->_('messengers.native_php_mail.config.sender'); ?>"><br />
            </td>
        </tr>
    </table>
</div>
<div class="section-body my-0" data-parent="messenger-php-mail-section">
    <table class="setting-table">
        <tr>
            <td class="r1"><?php _e('panel', 'messenger_label_recipients', 'Recipients'); ?></td>
            <td class="r2">
                <textarea rows="5" name="messengers__native_php_mail__config__recipients" class="form-control form-control-sm col-sm-6"><?php $this->_('messengers.native_php_mail.config.recipients'); ?></textarea><br />
                <p><?php _e('panel', 'messenger_desc_recipients', 'Per email address per line.'); ?></p>
            </td>
        </tr>
    </table>
</div>
<!-------------------------------------------------------------------------------------------------------------->
<div class="section-title bg-glass mt-3">
    <h2><?php _e('panel', 'messenger_heading_sendgrid', 'SendGrid'); ?></h2>
    <div class="confirm-test-container">
        <button type="button" class="btn btn-confirm-test" data-module="sendgrid">Test</button>
        <span id="test-result-sendgrid"><i class="fas fa-pause"></i></span>
    </div>
    <div class="toggle-container toggle-sm">
        <label class="rocker rocker-sm">
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
                <input type="text" name="messengers__sendgrid__config__api_key" class="form-control form-control-sm col-sm-6" value="<?php $this->_('messengers.sendgrid.config.api_key'); ?>"><br />
            </td>
        </tr>
    </table>
</div>
<div class="section-body my-0" data-parent="messenger-sendgrid-section">
    <table class="setting-table">
        <tr>
            <td class="r1"><?php _e('panel', 'messenger_label_sender', 'Sender'); ?></td>
            <td class="r2">
                <input type="text" name="messengers__sendgrid__config__sender" class="form-control form-control-sm col-sm-6" value="<?php $this->_('messengers.sendgrid.config.sender'); ?>"><br />
            </td>
        </tr>
    </table>
</div>
<div class="section-body my-0" data-parent="messenger-sendgrid-section">
    <table class="setting-table">
        <tr>
            <td class="r1"><?php _e('panel', 'messenger_label_recipients', 'Recipients'); ?></td>
            <td class="r2">
                <textarea rows="5" name="messengers__sendgrid__config__recipients" class="form-control form-control-sm col-sm-6"><?php $this->_('messengers.sendgrid.config.recipients'); ?></textarea><br />
                <p><?php _e('panel', 'messenger_desc_recipients', 'Per email address per line.'); ?></p>
            </td>
        </tr>
    </table>
</div>
<!-------------------------------------------------------------------------------------------------------------->
<div class="section-title bg-glass mt-3">
    <h2><?php _e('panel', 'messenger_heading_mailgun', 'MailGun'); ?></h2>
    <div class="confirm-test-container">
        <button type="button" class="btn btn-confirm-test" data-module="mailgun">Test</button>
        <span id="test-result-mailgun"><i class="fas fa-pause"></i></span>
    </div>
    <div class="toggle-container toggle-sm">
        <label class="rocker rocker-sm">
            <input type="hidden" name="messengers__mailgun__enable" value="off" />
            <input type="checkbox" name="messengers__mailgun__enable" class="toggle-block" value="on" data-target="messenger-mailgun-section" <?php $this->checked('messengers.mailgun.enable', true); ?> />
            <span class="switch-left">ON</span>
            <span class="switch-right">OFF</span>
        </label>
    </div>
</div>
<div class="section-body my-0" data-parent="messenger-mailgun-section">
    <table class="setting-table">
        <tr>
            <td class="r1"><?php _e('panel', 'messenger_label_api_key', 'API Key'); ?></td>
            <td class="r2">
                <input type="text" name="messengers__mailgun__config__api_key" class="form-control form-control-sm col-sm-6" value="<?php $this->_('messengers.mailgun.config.api_key'); ?>"><br />
            </td>
        </tr>
    </table>
</div>
<div class="section-body my-0" data-parent="messenger-mailgun-section">
    <table class="setting-table">
        <tr>
            <td class="r1"><?php _e('panel', 'messenger_label_domain', 'Domain Name'); ?></td>
            <td class="r2">
                <input type="text" name="messengers__mailgun__config__domain_name" class="form-control form-control-sm col-sm-6" value="<?php $this->_('messengers.mailgun.config.domain_name'); ?>"><br />
            </td>
        </tr>
    </table>
</div>
<div class="section-body my-0" data-parent="messenger-mailgun-section">
    <table class="setting-table">
        <tr>
            <td class="r1"><?php _e('panel', 'messenger_label_sender', 'Sender'); ?></td>
            <td class="r2">
                <input type="text" name="messengers__mailgun__config__sender" class="form-control form-control-sm col-sm-6" value="<?php $this->_('messengers.mailgun.config.sender'); ?>"><br />
            </td>
        </tr>
    </table>
</div>
<div class="section-body my-0" data-parent="messenger-mailgun-section">
    <table class="setting-table">
        <tr>
            <td class="r1"><?php _e('panel', 'messenger_label_recipients', 'Recipients'); ?></td>
            <td class="r2">
                <textarea rows="5" name="messengers__mailgun__config__recipients" class="form-control form-control-sm col-sm-6"><?php $this->_('messengers.mailgun.config.recipients'); ?></textarea><br />
                <p><?php _e('panel', 'messenger_desc_recipients', 'Per email address per line.'); ?></p>
            </td>
        </tr>
    </table>
</div>

<script>

    $(function() {
        $('.btn-confirm-test').click(function() {
            var moduleName = $(this).attr('data-module');

            switch (moduleName) {

                case 'telegram':
                    break;

                case 'linenotify':
                    break;

                case 'slack':
                    break;

                case 'slack-webhook':
                    break;

                case 'rocket-chat':
                    break;

                case 'smtp':
                    break;

                case 'native-php-mail':
                    break;

                case 'sendgrid':
                    break;

                case 'mailgun':

                    break;

                default:
            }
        });
    });

</script>