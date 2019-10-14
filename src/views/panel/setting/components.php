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
use function Shieldon\Helper\mask_string;

?>
<div class="section-title bg-glass">
    <h2>IP</h2>
    <div class="toggle-container">
        <label class="rocker rocker-md">
            <input type="hidden" name="components__ip__enable" value="off" />
            <input type="checkbox" name="components__ip__enable" class="toggle-block" value="on" data-target="component-ip-section" <?php $this->checked('components.ip.enable', true); ?> />
            <span class="switch-left">ON</span>
            <span class="switch-right">OFF</span>
        </label>
    </div>
</div>
<div class="section-body my-0" data-parent="daemon-section">
    <table class="setting-table">
        <tr>
            <td class="r1"></td>
            <td class="r2">
                <p>Enabling this option will activate the IP Manager.</p>
            </td>
        </tr>
    </table>
</div>

<!-------------------------------------------------------------------------------------------------------------->
<div class="section-title bg-glass mt-3">
    <h2>Trusted Bots</h2>
    <div class="toggle-container">
        <label class="rocker rocker-md">
            <input type="hidden" name="components__trusted_bot__enable" value="off" />
            <input type="checkbox" name="components__trusted_bot__enable" class="toggle-block" value="on" data-target="component-trustedbot-section" <?php $this->checked('online_session_limit.enable', true); ?> />
            <span class="switch-left">ON</span>
            <span class="switch-right">OFF</span>
        </label>
    </div>
</div>
<div class="section-body my-0" data-parent="component-trustedbot-section">
    <table class="setting-table">
        <tr>
            <td class="r1"></td>
            <td class="r2">
                <p>
                    Allow popular search engines crawl your webiste.<br />
                    Notice: Turning this option off will impact your SEO because the bots will be going to checking process.
                </p>
            </td>
        </tr>
        <tr>
            <td class="r1">Strict Mode</td>
            <td class="r2">
                <label class="rocker rocker-sm">
                    <input type="hidden" name="components__trusted_bot__strict_mode" value="off" />
                    <input type="checkbox" name="components__trusted_bot__strict_mode" class="toggle-block" value="on" <?php $this->checked('components.trusted_bot.strict_mode', true); ?>>
                    <span class="switch-left">ON</span>
                    <span class="switch-right">OFF</span>
                </label>
                <p>IP resolved hostname and IP address must match.</p>
            </td>
        </tr>
    </table>
</div>

<!-------------------------------------------------------------------------------------------------------------->
<div class="section-title bg-glass mt-3">
    <h2>Header</h2>
    <div class="toggle-container">
        <label class="rocker rocker-md">
            <input type="hidden" name="components__header__enable" value="off" />
            <input type="checkbox" name="components__header__enable" class="toggle-block" value="on" data-target="component-header-section" <?php $this->checked('components.header.enable', true); ?> />
            <span class="switch-left">ON</span>
            <span class="switch-right">OFF</span>
        </label>
    </div>
</div>
<div class="section-body my-0" data-parent="component-header-section">
    <table class="setting-table">
        <tr>
            <td class="r1"></td>
            <td class="r2">
                <p>
                    Analysis header information from visitors.
                </p>
            </td>
        </tr>
        <tr>
            <td class="r1">Strict Mode</td>
            <td class="r2">
                <label class="rocker rocker-sm">
                    <input type="hidden" name="components__header__strict_mode" value="off" />
                    <input type="checkbox" name="components__header__strict_mode" class="toggle-block" value="on" <?php $this->checked('components.header.strict_mode', true); ?>>
                    <span class="switch-left">ON</span>
                    <span class="switch-right">OFF</span>
                </label>
                <p>Deny all vistors without common header information.</p>
                
            </td>
        </tr>
    </table>
</div>

<!-------------------------------------------------------------------------------------------------------------->
<div class="section-title bg-glass mt-3">
    <h2>User Agent</h2>
    <div class="toggle-container">
        <label class="rocker rocker-md">
            <input type="hidden" name="components__user_agent__enable" value="off" />
            <input type="checkbox" name="components__user_agent__enable" class="toggle-block" value="on" data-target="component-user-agent-section" <?php $this->checked('components.user_agent.enable', true); ?> />
            <span class="switch-left">ON</span>
            <span class="switch-right">OFF</span>
        </label>
    </div>
</div>
<div class="section-body my-0" data-parent="component-user-agent-section">
    <table class="setting-table">
        <tr>
            <td class="r1"></td>
            <td class="r2">
                <p>
                    Analysis user-agent information from visitors.
                </p>
            </td>
        </tr>
        <tr>
            <td class="r1">Strict Mode</td>
            <td class="r2">
                <label class="rocker rocker-sm">
                    <input type="hidden" name="components__user_agent__strict_mode" value="off" />
                    <input type="checkbox" name="components__user_agent__strict_mode" class="toggle-block" value="on" <?php $this->checked('components.user_agent.strict_mode', true); ?>>
                    <span class="switch-left">ON</span>
                    <span class="switch-right">OFF</span>
                </label>
                <p>Visitors with empty user-agent information will be blocked.</p>
            </td>
        </tr>
    </table>
</div>

<!-------------------------------------------------------------------------------------------------------------->
<div class="section-title bg-glass mt-3">
    <h2>Reverse DNS</h2>
    <div class="toggle-container">
        <label class="rocker rocker-md">
            <input type="hidden" name="components__rdns__enable" value="off" />
            <input type="checkbox" name="components__rdns__enable" class="toggle-block" value="on" data-target="component-rdns-section" <?php $this->checked('components.rdns.enable', true); ?> />
            <span class="switch-left">ON</span>
            <span class="switch-right">OFF</span>
        </label>
    </div>
</div>
<div class="section-body my-0" data-parent="component-rdns-section">
    <table class="setting-table">
        <tr>
            <td class="r1"></td>
            <td class="r2">
                <p>
                In general, an IP from Internet Service Provider (ISP) will have RDNS set. This option only works when strict mode is on.
                </p>
            </td>
        </tr>
        <tr>
            <td class="r1">Strict Mode</td>
            <td class="r2">
                <label class="rocker rocker-sm">
                    <input type="hidden" name="components__rdns__strict_mode" value="off" />
                    <input type="checkbox" name="components__rdns__strict_mode" class="toggle-block" value="on" <?php $this->checked('components.rdns.strict_mode', true); ?>>
                    <span class="switch-left">ON</span>
                    <span class="switch-right">OFF</span>
                </label>
                <p>
                    Visitors with empty RDNS record will be blocked.<br />
                    IP resolved hostname (RDNS) and IP address must match.
                </p>
            </td>
        </tr>
    </table>
</div>