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
    <h2><?php _e('panel', 'setting_heading_filter_frequency', 'Frequency'); ?></h2>
    <div class="toggle-container">
        <label class="rocker rocker-sm rocker-test">
            <input type="hidden" name="filters__frequency__enable" value="off" />
            <input type="checkbox"
                name="filters__frequency__enable"
                class="toggle-block"
                value="on"
                data-target="filters-frequency-section"
                <?php $this->checked('filters.frequency.enable', true); ?>
            />
            <span class="switch-left"><i>ON</i></span>
            <span class="switch-right"><i>OFF</i></span>
        </label>
    </div>
</div>
<div class="section-body my-0" data-parent="filters-frequency-section">
    <table class="setting-table">
        <tr>
            <td class="r1"></td>
            <td class="r2">
                <p>
                    <?php
                    _e(
                        'panel',
                        'setting_note_filter_frequency_1',
                        'Don’t worry about human visitors, and if they reach the limitation 
                        and get banned, they can easily continue surfing your website by solving the CAPTCHA.'
                    );
                    ?>
                    <br />
                    <?php
                    _e(
                        'panel',
                        'setting_note_filter_frequency_2',
                        'The following image is an example.'
                    );
                    ?>
                </p>
            </td>
        </tr>
        <tr>
            <td class="r1">
                <?php _e('panel', 'setting_label_secondly_limit', 'Secondly Limit'); ?>
            </td>
            <td class="r2">
                <input type="text"
                    name="filters__frequency__config__quota_s"
                    class="form-control form-control-sm col-sm-3"
                    value="<?php $this->_('filters.frequency.config.quota_s'); ?>"
                />
                <br />
                <p>
                    <?php _e('panel', 'setting_note_secondly_limit', 'Page views per vistor per second.'); ?>
                </p>
            </td>
        </tr>
        <tr>
            <td class="r1">
                <?php _e('panel', 'setting_label_minutely_limit', 'Minutely Limit'); ?>
            </td>
            <td class="r2">
                <input type="text"
                    name="filters__frequency__config__quota_m"
                    class="form-control form-control-sm col-sm-3"
                    value="<?php $this->_('filters.frequency.config.quota_m'); ?>"
                />
                <br />
                <p>
                    <?php
                    _e(
                        'panel',
                        'setting_note_minutely_limit',
                        'Page views per vistor per minute.'
                    );
                    ?>
                </p>
            </td>
        </tr>
        <tr>
            <td class="r1">
                <?php _e('panel', 'setting_label_hourly_limit', 'Hourly Limit'); ?>
            </td>
            <td class="r2">
                <input type="text"
                    name="filters__frequency__config__quota_h"
                    class="form-control form-control-sm col-sm-3"
                    value="<?php $this->_('filters.frequency.config.quota_h'); ?>"
                />
                <br />
                <p>
                    <?php _e('panel', 'setting_note_hourly_limit', 'Page views per vistor per hour.'); ?>
                </p>
            </td>
        </tr>
        <tr>
            <td class="r1">
                <?php _e('panel', 'setting_label_daily_limit', 'Daily Limit'); ?>
            </td>
            <td class="r2">
                <input type="text"
                    name="filters__frequency__config__quota_d"
                    class="form-control form-control-sm col-sm-3"
                    value="<?php $this->_('filters.frequency.config.quota_d'); ?>"
                />
                <br />
                <p>
                    <?php _e('panel', 'setting_note_daily_limit', 'Page views per vistor per day.'); ?>
                </p>
            </td>
        </tr>
    </table>
</div>

<!-------------------------------------------------------------------------------------------------------------->
<div class="section-title bg-glass mt-3">
    <h2><?php _e('panel', 'setting_label_cookie', 'Cookie'); ?></h2>
    <div class="toggle-container">
        <label class="rocker rocker-sm rocker-test">
            <input type="hidden" name="filters__cookie__enable" value="off" />
            <input type="checkbox"
                name="filters__cookie__enable"
                class="toggle-block"
                value="on"
                data-target="filters-cookie-section"
                <?php $this->checked('filters.cookie.enable', true); ?>
            />
            <span class="switch-left"><i>ON</i></span>
            <span class="switch-right"><i>OFF</i></span>
        </label>
    </div>
</div>
<div class="section-body my-0" data-parent="filters-cookie-section">
    <table class="setting-table">
        <tr>
            <td class="r1"></td>
            <td class="r2">
                <p>
                    Check cookie generated by JavaScript.
                </p>
                <code class="p-3 border bg-light d-inline-block text-dark">
                    <span class="text-muted">// You have to inject this variable to the template to make it work.</span>
                    <br />
                    $jsCode = $firewall->getKernel()->outputJsSnippet();
                </code><br /><br />
            </td>
        </tr>
        <tr>
            <td class="r1">
                <?php _e('panel', 'setting_label_quota', 'Quota'); ?>
            </td>
            <td class="r2">
                <input type="text"
                    name="filters__cookie__config__quota"
                    class="form-control form-control-sm col-sm-3"
                    value="<?php $this->_('filters.cookie.config.quota'); ?>"
                />
                <br />
                <p>
                    <?php
                    _e(
                        'panel',
                        'setting_note_quota',
                        'A visitor reached this limit will get banned temporarily.'
                    );
                    ?>
                </p>
            </td>
        </tr>
    </table>
</div>
<div class="section-body my-0" data-parent="filters-cookie-section">
    <table class="setting-table">
        <tr>
            <td class="r1">
                <?php _e('panel', 'setting_label_cookie_name', 'Cookie Name'); ?>
            </td>
            <td class="r2">
                <input type="text"
                    name="filters__cookie__config__cookie_name"
                    class="form-control form-control-sm col-sm-3"
                    value="<?php $this->_('filters.cookie.config.cookie_name'); ?>"
                />
                <br />
                <p>
                    <?php _e('panel', 'setting_note_cookie_name', 'English characters only.'); ?>
                </p>
            </td>
        </tr>
    </table>
</div>
<div class="section-body my-0" data-parent="filters-cookie-section">
    <table class="setting-table">
        <tr>
            <td class="r1">
                <?php _e('panel', 'setting_label_cookie_value', 'Cookie Value'); ?>
            </td>
            <td class="r2">
                <input type="text"
                    name="filters__cookie__config__cookie_value"
                    class="form-control form-control-sm col-sm-3"
                    value="<?php $this->_('filters.cookie.config.cookie_value'); ?>"
                />
                <br />
                <p>
                    <?php _e('panel', 'setting_note_cookie_name', 'English characters only.'); ?>
                </p>
            </td>
        </tr>
    </table>
</div>
<div class="section-body my-0" data-parent="filters-cookie-section">
    <table class="setting-table">
        <tr>
            <td class="r1">
                <?php _e('panel', 'setting_label_cookie_domain', 'Cookie Domain'); ?>
            </td>
            <td class="r2">
                <input type="text"
                    name="filters__cookie__config__cookie_domain"
                    class="form-control form-control-sm col-sm-3"
                    value="<?php $this->_('filters.cookie.config.cookie_domain'); ?>"
                />
                <br />
                <p>
                    <?php _e('panel', 'setting_text_leave_blank', 'Just leave it blank to apply default.'); ?>
                </p>
            </td>
        </tr>
    </table>
</div>

<!-------------------------------------------------------------------------------------------------------------->
<div class="section-title bg-glass mt-3">
    <h2><?php _e('panel', 'setting_label_filter_session', 'Session'); ?></h2>
    <div class="toggle-container">
        <label class="rocker rocker-sm rocker-test">
            <input type="hidden" name="filters__session__enable" value="off" />
            <input type="checkbox"
                name="filters__session__enable"
                class="toggle-block"
                value="on"
                data-target="filters-session-section"
                <?php $this->checked('filters.session.enable', true); ?>
            />
            <span class="switch-left"><i>ON</i></span>
            <span class="switch-right"><i>OFF</i></span>
        </label>
    </div>
</div>
<div class="section-body my-0" data-parent="filters-session-section">
    <table class="setting-table">
        <tr>
            <td class="r1"></td>
            <td class="r2">
                <p>
                    <?php
                    _e(
                        'panel',
                        'setting_note_filter_session',
                        'Detect whether multiple sessions were created by the same visitor.'
                    );
                    ?>
                </p>
            </td>
        </tr>
        <tr>
            <td class="r1">
                <?php _e('panel', 'setting_label_quota', 'Quota'); ?>
            </td>
            <td class="r2">
                <input type="text"
                    name="filters__session__config__quota"
                    class="form-control form-control-sm col-sm-3"
                    value="<?php $this->_('filters.session.config.quota'); ?>"
                />
                <br />
                <p>
                    <?php
                    _e(
                        'panel',
                        'setting_note_quota',
                        'A visitor reached this limit will get banned temporarily.'
                    );
                    ?>
                </p>
            </td>
        </tr>
    </table>
</div>
<div class="section-body my-0" data-parent="filters-session-section">
    <table class="setting-table">
        <tr>
            <td class="r1">
                <?php _e('panel', 'setting_label_buffered_time', 'Buffered Time'); ?>
            </td>
            <td class="r2">
                <input type="text"
                    name="filters__session__config__time_buffer"
                    class="form-control form-control-sm col-sm-3"
                    value="<?php $this->_('filters.session.config.time_buffer'); ?>"
                />
                <br />
                <p>
                    <?php
                    _e(
                        'panel',
                        'setting_note_buffered_time',
                        'Start using this filter after n seconds after the first time visiting your website.'
                    );
                    ?>
                </p>
            </td>
        </tr>
    </table>
</div>

<!-------------------------------------------------------------------------------------------------------------->
<div class="section-title bg-glass mt-3">
    <h2><?php _e('panel', 'setting_label_filter_referer', 'Referrer'); ?></h2>
    <div class="toggle-container">
        <label class="rocker rocker-sm rocker-test">
            <input type="hidden" name="filters__referer__enable" value="off" />
            <input type="checkbox"
                name="filters__referer__enable"
                class="toggle-block"
                value="on"
                data-target="filters-referer-section"
                <?php $this->checked('filters.referer.enable', true); ?>
            />
            <span class="switch-left"><i>ON</i></span>
            <span class="switch-right"><i>OFF</i></span>
        </label>
    </div>
</div>
<div class="section-body my-0" data-parent="filters-referer-section">
    <table class="setting-table">
        <tr>
            <td class="r1"></td>
            <td class="r2">
                <p>
                    <?php _e('panel', 'setting_note_filter_referer', 'Check HTTP referer information.'); ?>
                    
                </p>
            </td>
        </tr>
        <tr>
            <td class="r1">
                <?php _e('panel', 'setting_label_quota', 'Quota'); ?>
            </td>
            <td class="r2">
                <input type="text"
                    name="filters__session__config__quota"
                    class="form-control form-control-sm col-sm-3"
                    value="<?php $this->_('filters.referer.config.quota'); ?>"
                />
                <br />
                <p>
                    <?php
                    _e(
                        'panel',
                        'setting_note_quota',
                        'A visitor reached this limit will get banned temporarily.'
                    );
                    ?>
                </p>
            </td>
        </tr>
    </table>
</div>
<div class="section-body my-0" data-parent="filters-referer-section">
    <table class="setting-table">
        <tr>
            <td class="r1">
                <?php _e('panel', 'setting_label_buffered_time', 'Buffered Time'); ?>
            </td>
            <td class="r2">
                <input type="text"
                    name="filters__referer__config__time_buffer"
                    class="form-control form-control-sm col-sm-3"
                    value="<?php $this->_('filters.referer.config.time_buffer'); ?>"
                />
                <br />
                <p>
                    <?php
                    _e(
                        'panel',
                        'setting_note_buffered_time',
                        'Start using this filter after n seconds after the first time visiting your website.'
                    );
                    ?>
                </p>
            </td>
        </tr>
    </table>
</div>
