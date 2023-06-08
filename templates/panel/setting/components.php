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
    <h2><?php _e('panel', 'setting_heading_component_ip', 'IP'); ?></h2>
    <div class="toggle-container">
        <label class="rocker rocker-sm rocker-test">
            <input type="hidden" name="components__ip__enable" value="off" />
            <input type="checkbox"
                name="components__ip__enable"
                class="toggle-block" value="on"
                data-target="component-ip-section" 
                <?php $this->checked('components.ip.enable', true); ?> />
            <span class="switch-left"><i>ON</i></span>
            <span class="switch-right"><i>OFF</i></span>
        </label>
    </div>
</div>
<div class="section-body my-0" data-parent="daemon-section">
    <table class="setting-table">
        <tr>
            <td class="r1"></td>
            <td class="r2">
                <p>
                    <?php
                    _e(
                        'panel',
                        'setting_note_component_ip',
                        'Activate the IP Manager by enabling this option.'
                    );
                    ?>
                </p>
            </td>
        </tr>
    </table>
</div>

<!-------------------------------------------------------------------------------------------------------------->
<div class="section-title bg-glass mt-3">
    <h2><?php _e('panel', 'setting_heading_component_tb', 'Trusted Bots'); ?></h2>
    <div class="toggle-container">
        <label class="rocker rocker-sm rocker-test">
            <input type="hidden" name="components__trusted_bot__enable" value="off" />
            <input type="checkbox"
                name="components__trusted_bot__enable" 
                class="toggle-block" value="on" 
                data-target="component-trustedbot-section" 
                <?php $this->checked('components.trusted_bot.enable', true); ?> 
            />
            <span class="switch-left"><i>ON</i></span>
            <span class="switch-right"><i>OFF</i></span>
        </label>
    </div>
</div>
<div class="section-body my-0" data-parent="component-trustedbot-section">
    <table class="setting-table">
        <tr>
            <td class="r1"></td>
            <td class="r2">
                <p>
                    <?php
                    _e(
                        'panel',
                        'setting_note_component_tb_1',
                        'Allow popular search engines to crawl your website.'
                    );
                    ?>
                    <br />
                    <?php
                    _e(
                        'panel',
                        'setting_note_component_tb_2',
                        'Notice: Turning this option off will impact your SEO because 
                        the bots will be going to the checking process.'
                    );
                    ?>
                    <br />
                </p>
            </td>
        </tr>
        <tr>
            <td class="r1"><?php _e('panel', 'setting_label_strict_mode', 'Strict Mode'); ?></td>
            <td class="r2">
                <label class="rocker rocker-sm">
                    <input type="hidden" name="components__trusted_bot__strict_mode" value="off" />
                    <input type="checkbox"
                        name="components__trusted_bot__strict_mode"
                        class="toggle-block"
                        value="on" 
                        <?php $this->checked('components.trusted_bot.strict_mode', true); ?>
                    />
                    <span class="switch-left"><i>ON</i></span>
                    <span class="switch-right"><i>OFF</i></span>
                </label>
                <p>
                    <?php
                    _e(
                        'panel',
                        'setting_note_component_tb_3',
                        'IP resolved hostname (PTR) and IP address must match up.'
                    ); ?>
                </p>
            </td>
        </tr>
    </table>
</div>

<!-------------------------------------------------------------------------------------------------------------->
<div class="section-title bg-glass mt-3">
    <h2><?php _e('panel', 'setting_heading_component_header', 'Header'); ?></h2>
    <div class="toggle-container">
        <label class="rocker rocker-sm rocker-test">
            <input type="hidden" name="components__header__enable" value="off" />
            <input type="checkbox"
                name="components__header__enable"
                class="toggle-block"
                value="on"
                data-target="component-header-section"
                <?php $this->checked('components.header.enable', true); ?>
            />
            <span class="switch-left"><i>ON</i></span>
            <span class="switch-right"><i>OFF</i></span>
        </label>
    </div>
</div>
<div class="section-body my-0" data-parent="component-header-section">
    <table class="setting-table">
        <tr>
            <td class="r1"></td>
            <td class="r2">
                <p>
                    <?php _e('panel', 'setting_note_component_header_1', 'Analyze visitors header information.'); ?>
                </p>
            </td>
        </tr>
        <tr>
            <td class="r1"><?php _e('panel', 'setting_label_strict_mode', 'Strict Mode'); ?></td>
            <td class="r2">
                <label class="rocker rocker-sm">
                    <input type="hidden" name="components__header__strict_mode" value="off" />
                    <input type="checkbox" 
                        name="components__header__strict_mode" 
                        class="toggle-block" 
                        value="on" 
                        <?php $this->checked('components.header.strict_mode', true); ?>
                    />
                    <span class="switch-left"><i>ON</i></span>
                    <span class="switch-right"><i>OFF</i></span>
                </label>
                <p>
                    <?php
                    _e(
                        'panel',
                        'setting_note_component_header_2',
                        'Deny all vistors without common header information.'
                    );
                    ?>
                </p>
                <code>Accept, Accept-Language, Accept-Encoding</code>
                
            </td>
        </tr>
    </table>
</div>

<!-------------------------------------------------------------------------------------------------------------->
<div class="section-title bg-glass mt-3">
    <h2><?php _e('panel', 'setting_heading_component_useragent', 'User Agent'); ?></h2>
    <div class="toggle-container">
        <label class="rocker rocker-sm rocker-test">
            <input type="hidden" name="components__user_agent__enable" value="off" />
            <input type="checkbox"
                name="components__user_agent__enable"
                class="toggle-block"
                value="on"
                data-target="component-user-agent-section"
                <?php $this->checked('components.user_agent.enable', true); ?> 
            />
            <span class="switch-left"><i>ON</i></span>
            <span class="switch-right"><i>OFF</i></span>
        </label>
    </div>
</div>
<div class="section-body my-0" data-parent="component-user-agent-section">
    <table class="setting-table">
        <tr>
            <td class="r1"></td>
            <td class="r2">
                <p>
                    <?php
                    _e(
                        'panel',
                        'setting_note_component_useragent_1',
                        'Analyze visitors user-agent information.'
                    );
                    ?>
                </p>
            </td>
        </tr>
        <tr>
            <td class="r1"><?php _e('panel', 'setting_label_strict_mode', 'Strict Mode'); ?></td>
            <td class="r2">
                <label class="rocker rocker-sm">
                    <input type="hidden" name="components__user_agent__strict_mode" value="off" />
                    <input type="checkbox"
                        name="components__user_agent__strict_mode"
                        class="toggle-block"
                        value="on"
                        <?php $this->checked('components.user_agent.strict_mode', true); ?>
                    />
                    <span class="switch-left"><i>ON</i></span>
                    <span class="switch-right"><i>OFF</i></span>
                </label>
                <p>
                    <?php
                    _e(
                        'panel',
                        'setting_note_component_useragent_2',
                        'Visitors with empty user-agent information will be blocked.'
                    );
                    ?>
                </p>
            </td>
        </tr>
    </table>
</div>

<!-------------------------------------------------------------------------------------------------------------->
<div class="section-title bg-glass mt-3">
    <h2><?php _e('panel', 'setting_heading_component_rdns', 'Reverse DNS'); ?></h2>
    <div class="toggle-container">
        <label class="rocker rocker-sm rocker-test">
            <input type="hidden" name="components__rdns__enable" value="off" />
            <input type="checkbox"
                name="components__rdns__enable"
                class="toggle-block"
                value="on"
                data-target="component-rdns-section" 
                <?php $this->checked('components.rdns.enable', true); ?> 
            />
            <span class="switch-left"><i>ON</i></span>
            <span class="switch-right"><i>OFF</i></span>
        </label>
    </div>
</div>
<div class="section-body my-0" data-parent="component-rdns-section">
    <table class="setting-table">
        <tr>
            <td class="r1"></td>
            <td class="r2">
                <p>
                    <?php
                    _e(
                        'panel',
                        'setting_note_component_rdns_1',
                        'In general, an IP from Internet Service Provider (ISP) 
                        often have the RDNS set. This option only works when strict mode is on.'
                    );
                    ?>
                </p>
            </td>
        </tr>
        <tr>
            <td class="r1"><?php _e('panel', 'setting_label_strict_mode', 'Strict Mode'); ?></td>
            <td class="r2">
                <label class="rocker rocker-sm">
                    <input type="hidden" name="components__rdns__strict_mode" value="off" />
                    <input type="checkbox" 
                        name="components__rdns__strict_mode" 
                        class="toggle-block" 
                        value="on" 
                        <?php $this->checked('components.rdns.strict_mode', true); ?> 
                    />
                    <span class="switch-left"><i>ON</i></span>
                    <span class="switch-right"><i>OFF</i></span>
                </label>
                <p>
                    <?php
                    _e(
                        'panel',
                        'setting_note_component_rdns_2',
                        'Visitors with an empty RDNS record will be blocked.'
                    );
                    ?>
                </p>
            </td>
        </tr>
    </table>
</div>