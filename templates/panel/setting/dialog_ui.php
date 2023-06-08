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

defined('SHIELDON_VIEW') || die('Illegal access');

use function Shieldon\Firewall\_e;

?>
<div class="section-title bg-glass">
    <h2><?php _e('panel', 'setting_heading_dailogui', 'Dialog UI'); ?></h2>
</div>
<div class="section-body my-0">
    <table class="setting-table">
        <tr>
            <td class="r1"><?php _e('panel', 'setting_label_language', 'Language'); ?></td>
            <td class="r2">
                <div class="col-sm-12">
                    <div class="radio-style">
                        <input name="dialog_ui__lang"
                            value="en"
                            type="radio"
                            id="lang-1"
                            <?php $this->checked('dialog_ui.lang', 'en'); ?> 
                        /> 
                        <label for="lang-1" class="radio-label">
                            English (en)
                        </label> 
                    </div>
                    <div class="radio-style">
                        <input name="dialog_ui__lang"
                            value="zh"
                            type="radio"
                            id="lang-2"
                            <?php $this->checked('dialog_ui.lang', 'zh'); ?>
                        /> 
                        <label for="lang-2" class="radio-label">
                            中文 (zh)
                        </label> 
                    </div>
                    <div class="radio-style">
                        <input name="dialog_ui__lang"
                            value="zh_CN"
                            type="radio"
                            id="lang-3"
                            <?php $this->checked('dialog_ui.lang', 'zh_CN'); ?>
                        /> 
                        <label for="lang-3" class="radio-label">
                            中文 (简体) (zh_CN)
                        </label> 
                    </div>
                </div>
            </td>
        </tr>
    </table>
</div>
<div class="section-body my-0">
    <table class="setting-table">
        <tr>
            <td class="r1"><?php _e('panel', 'setting_label_background_image', 'Background Image'); ?></td>
            <td class="r2">
                <input type="text"
                    name="dialog_ui__background_image"
                    class="form-control form-control-sm col-sm-3"
                    value="<?php $this->_('dialog_ui.background_image'); ?>"
                />
                <br />
                <p>
                    <?php
                    _e(
                        'panel',
                        'setting_note_background_image',
                        'Please add a full URL or relative path of the image.'
                    );
                    ?>
                </p>
            </td>
        </tr>
    </table>
</div>
<div class="section-body my-0">
    <table class="setting-table">
        <tr>
            <td class="r1">
                <?php _e('panel', 'setting_label_background_color', 'Background Color'); ?>
            </td>
            <td class="r2">
                <input type="text"
                    name="dialog_ui__bg_color"
                    class="form-control form-control-sm col-sm-3"
                    value="<?php $this->_('dialog_ui.bg_color'); ?>"
                />
                <br />
                <p>
                    <?php
                    _e(
                        'panel',
                        'setting_note_background_color',
                        'You can specify a background color if you don’t want to use a background image.'
                    );
                    ?>
                </p>
            </td>
        </tr>
    </table>
</div>
<div class="section-body my-0">
    <table class="setting-table">
        <tr>
            <td class="r1">
                <?php
                _e(
                    'panel',
                    'setting_label_background_color',
                    'Background Color'
                );
                ?>
                <br />
                <small>
                    <?php _e('panel', 'setting_label_dialog_header', 'Dialog Header'); ?>
                </small>
            </td>
            <td class="r2">
                <input type="text"
                    name="dialog_ui__header_bg_color"
                    class="form-control form-control-sm col-sm-3"
                    value="<?php $this->_('dialog_ui.header_bg_color'); ?>"
                />
                <br />
                <p>
                    <?php _e('panel', 'setting_text_for_example', 'For example'); ?>:
                    <code>#00aeff</code> or <code>rgb(0,174,255)</code>
                </p>
            </td>
        </tr>
    </table>
</div>
<div class="section-body my-0">
    <table class="setting-table">
        <tr>
            <td class="r1">
                <?php _e('panel', 'setting_label_font_color', 'Font Color'); ?><br />
                <small><?php _e('panel', 'setting_label_dialog_header', 'Dialog Header'); ?></small>
            </td>
            <td class="r2">
                <input type="text"
                    name="dialog_ui__header_color"
                    class="form-control form-control-sm col-sm-3"
                    value="<?php $this->_('dialog_ui.header_color'); ?>"
                />
                <br />
                <p>
                    <?php _e('panel', 'setting_text_for_example', 'For example'); ?>:
                    <code>#00aeff</code> or <code>rgb(0,174,255)</code>
                </p>
            </td>
        </tr>
    </table>
</div>
<div class="section-body my-0">
    <table class="setting-table">
        <tr>
            <td class="r1"><?php _e('panel', 'setting_label_shadow_opacity', 'Shadow Opacity'); ?></td>
            <td class="r2">
                <input type="text"
                    name="dialog_ui__shadow_opacity"
                    class="form-control form-control-sm col-sm-3"
                    value="<?php $this->_('dialog_ui.shadow_opacity'); ?>"
                />
                <br />
                <p>
                    <?php
                    _e(
                        'panel',
                        'setting_note_shadow_opacity',
                        'The range from 0 to 1, for example, 0.2 stands for 20% opacity.'
                    );
                    ?>
                </p>
            </td>
        </tr>
    </table>
</div>
<div class="section-title bg-glass mt-3">
    <h2><?php _e('panel', 'setting_heading_dialog_information', 'Information Disclosure'); ?></h2>
</div>
<div class="section-body my-0">
    <table class="setting-table">
        <tr>
            <td class="r1"><?php _e('panel', 'setting_label_dialog_user_inforamtion', 'User Information'); ?></td>
            <td class="r2">
                <label class="rocker rocker-sm">
                    <input type="hidden" name="dialog_info_disclosure__user_inforamtion" value="off" />
                    <input type="checkbox"
                        name="dialog_info_disclosure__user_inforamtion"
                        class="toggle-block" value="on"
                        <?php $this->checked('dialog_info_disclosure.user_inforamtion', true); ?>
                    />
                    <span class="switch-left"><i>ON</i></span>
                    <span class="switch-right"><i>OFF</i></span>
                </label>
                <p>
                    <?php
                    _e(
                        'panel',
                        'setting_note_dialog_user_inforamtion',
                        'Display IP address, RDNS and user-agent.'
                    );
                    ?>
                </p>
            </td>
        </tr>
    </table>
</div>
<div class="section-body my-0">
    <table class="setting-table">
        <tr>
            <td class="r1"><?php _e('panel', 'setting_label_dialog_http_status', 'HTTP Status Code'); ?></td>
            <td class="r2">
                <label class="rocker rocker-sm">
                    <input type="hidden" name="dialog_info_disclosure__http_status_code" value="off" />
                    <input type="checkbox"
                        name="dialog_info_disclosure__http_status_code"
                        class="toggle-block"
                        value="on"
                        <?php $this->checked('dialog_info_disclosure.http_status_code', true); ?> 
                    />
                    <span class="switch-left"><i>ON</i></span>
                    <span class="switch-right"><i>OFF</i></span>
                </label>
                <p>
                    <?php
                    _e(
                        'panel',
                        'setting_note_dialog_http_status',
                        'Display HTTP status code to the users.'
                    );
                    ?>
                </p>
            </td>
        </tr>
    </table>
</div>
<div class="section-body my-0">
    <table class="setting-table">
        <tr>
            <td class="r1">
                <?php _e('panel', 'setting_label_dialog_reason_code', 'Reason Code'); ?>
            </td>
            <td class="r2">
                <label class="rocker rocker-sm">
                    <input type="hidden" name="dialog_info_disclosure__reason_code" value="off" />
                    <input type="checkbox"
                        name="dialog_info_disclosure__reason_code"
                        class="toggle-block"
                        value="on"
                        <?php $this->checked('dialog_info_disclosure.reason_code', true); ?>
                    />
                    <span class="switch-left"><i>ON</i></span>
                    <span class="switch-right"><i>OFF</i></span>
                </label>
                <p>
                    <?php
                    _e(
                        'panel',
                        'setting_note_dialog_reason_code',
                        'Display the reason code what causes a user blocked.'
                    );
                    ?>
                    <br />
                    <?php
                    _e(
                        'panel',
                        'setting_note_dialog_reason_notice',
                        'Not recommended to display such information, 
                        people with bad intentions might get to know how to get through the protection.'
                    );
                    ?>
                </p>
            </td>
        </tr>
    </table>
</div>
<div class="section-body my-0">
    <table class="setting-table">
        <tr>
            <td class="r1"><?php _e('panel', 'setting_label_dialog_reason_text', 'Reason Text'); ?></td>
            <td class="r2">
                <label class="rocker rocker-sm">
                    <input type="hidden" name="dialog_info_disclosure__reason_text" value="off" />
                    <input type="checkbox"
                        name="dialog_info_disclosure__reason_text"
                        class="toggle-block"
                        value="on"
                        <?php $this->checked('dialog_info_disclosure.reason_text', true); ?>
                    />
                    <span class="switch-left"><i>ON</i></span>
                    <span class="switch-right"><i>OFF</i></span>
                </label>
                <p>
                    <?php
                    _e(
                        'panel',
                        'setting_note_dialog_reason_text',
                        'Display the reason text what causes a user blocked.'
                    );
                    ?>
                    <br />
                    <?php
                    _e(
                        'panel',
                        'setting_note_dialog_reason_notice',
                        'Not recommended to display such information, 
                        people with bad intentions might get to know how to get through the protection.'
                    );
                    ?>
                </p>
            </td>
        </tr>
    </table>
</div>
<div class="section-body my-0">
    <table class="setting-table">
        <tr>
            <td class="r1"><?php _e('panel', 'setting_label_dialog_user_amount', 'Online user count'); ?></td>
            <td class="r2">
                <label class="rocker rocker-sm">
                    <input type="hidden" name="dialog_info_disclosure__online_user_amount" value="off" />
                    <input type="checkbox"
                        name="dialog_info_disclosure__online_user_amount"
                        class="toggle-block"
                        value="on"
                        <?php $this->checked('dialog_info_disclosure.online_user_amount', true); ?>
                    />
                    <span class="switch-left"><i>ON</i></span>
                    <span class="switch-right"><i>OFF</i></span>
                </label>
                <p>
                    <?php
                    _e(
                        'panel',
                        'setting_note_dialog_user_amount',
                        'Display the total amount of online users as showing the dialog of session limit.'
                    );
                    ?>
                </p>
            </td>
        </tr>
    </table>
</div>