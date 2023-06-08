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
    <h2><?php _e('panel', 'setting_heading_recaptcha', 'reCAPTCHA'); ?></h2>
    <div class="toggle-container">
        <label class="rocker rocker-sm rocker-test">
            <input type="hidden" name="captcha_modules__recaptcha__enable" value="off" />
            <input type="checkbox"
                name="captcha_modules__recaptcha__enable"
                class="toggle-block"
                value="on"
                data-target="captcha-recaptcha-section"
                <?php $this->checked('captcha_modules.recaptcha.enable', true); ?> />
            <span class="switch-left"><i>ON</i></span>
            <span class="switch-right"><i>OFF</i></span>
        </label>
    </div>
</div>
<div class="section-body my-0" data-parent="captcha-recaptcha-section">
    <table class="setting-table">
        <tr>
            <td class="r1">
                <?php _e('panel', 'setting_label_recaptcha_key', 'Site Key'); ?>
            </td>
            <td class="r2">
                <input type="text"
                    name="captcha_modules__recaptcha__config__site_key"
                    class="form-control form-control-sm col-sm-3"
                    value="<?php $this->_('captcha_modules.recaptcha.config.site_key'); ?>">
                <br />
                <p>
                    <?php
                    _e(
                        'panel',
                        'setting_note_recaptcha_key',
                        'Enter Google reCaptcha site key for your webiste.'
                    );
                    ?>
                </p>
            </td>
        </tr>
    </table>
</div>
<div class="section-body my-0" data-parent="captcha-recaptcha-section">
    <table class="setting-table">
        <tr>
            <td class="r1">
                <?php _e('panel', 'setting_label_recaptcha_secret', 'Secret Key'); ?>
            </td>
            <td class="r2">
                <input type="text"
                    name="captcha_modules__recaptcha__config__secret_key"
                    class="form-control form-control-sm col-sm-3"
                    value="<?php $this->_('captcha_modules.recaptcha.config.secret_key'); ?>">
                <br />
                <p>
                    <?php
                    _e(
                        'panel',
                        'setting_note_recaptcha_secret',
                        'Enter Google reCahptcha secret key for your webiste.'
                    );
                    ?>
                </p>
            </td>
        </tr>
    </table>
</div>
<div class="section-body my-0" data-parent="captcha-recaptcha-section">
    <table class="setting-table">
        <tr>
            <td class="r1"><?php _e('panel', 'setting_label_recaptcha_version', 'Version'); ?></td>
            <td class="r2">
                <div class="col-sm-12">
                    <div class="radio-style">
                        <input name="captcha_modules__recaptcha__config__version"
                            value="v2"
                            type="radio"
                            id="recaptcha-version-v2" 
                            <?php $this->checked('captcha_modules.recaptcha.config.version', 'v2'); ?> /> 
                        <label for="recaptcha-version-v2" class="radio-label">
                            v2
                        </label> 
                    </div>
                    <div class="radio-style">
                        <input name="captcha_modules__recaptcha__config__version"
                            value="v3"
                            type="radio"
                            id="recaptcha-version-v3" 
                            <?php $this->checked('captcha_modules.recaptcha.config.version', 'v3'); ?> /> 
                        <label for="recaptcha-version-v3" class="radio-label">
                            v3
                        </label> 
                    </div>
                    <p>
                        <?php
                        _e(
                            'panel',
                            'setting_note_recaptcha_version',
                            'Please use corresponding key for that version you choose, otherwise it won’t work.'
                        );
                        ?>
                    </p>
                </div>
            </td>
        </tr>
    </table>
</div>
<div class="section-body my-0" data-parent="captcha-recaptcha-section">
    <table class="setting-table">
        <tr>
            <td class="r1"><?php _e('panel', 'setting_label_recaptcha_lang', 'Language Code'); ?></td>
            <td class="r2">
                <input type="text"
                    name="captcha_modules__recaptcha__config__lang"
                    class="form-control form-control-sm col-sm-3"
                    value="<?php $this->_('captcha_modules.recaptcha.config.lang'); ?>">
                <br />
                <p>
                    <?php
                    _e(
                        'panel',
                        'setting_note_recaptcha_lang',
                        'ISO 639 - ISO 3166 code. For example, zh-TW stands for Tranditional Chinese of Taiwan.'
                    );
                    ?>
                </p>
            </td>
        </tr>
    </table>
</div>
                
<!-------------------------------------------------------------------------------------------------------------->
<div class="section-title bg-glass mt-3">
    <h2><?php _e('panel', 'setting_heading_image_captcha', 'Image'); ?></h2>
    <div class="toggle-container">
        <label class="rocker rocker-sm rocker-test">
            <input type="hidden" name="captcha_modules__image__enable" value="off" />
            <input type="checkbox"
                name="captcha_modules__image__enable"
                class="toggle-block"
                value="on"
                data-target="captcha-image-section" 
                <?php $this->checked('captcha_modules.image.enable', true); ?> />
            <span class="switch-left"><i>ON</i></span>
            <span class="switch-right"><i>OFF</i></span>
        </label>
    </div>
</div>
<div class="section-body my-0" data-parent="captcha-image-section">
    <table class="setting-table">
        <tr>
            <td class="r1"><?php _e('panel', 'table_label_type', 'Type'); ?></td>
            <td class="r2">
                <div class="col-sm-12">
                    <div class="radio-style">
                        <input name="captcha_modules__image__config__type"
                            value="alnum"
                            type="radio"
                            id="captcha-image-alnum" 
                            <?php $this->checked('captcha_modules.image.config.type', 'alnum'); ?> /> 
                        <label for="captcha-image-alnum" class="radio-label">
                            <?php
                            _e(
                                'panel',
                                'setting_note_image_captcha_1',
                                'Alpha-numeric string with lower and uppercase characters.'
                            );
                            ?>
                        </label> 
                    </div>
                    <div class="radio-style">
                        <input name="captcha_modules__image__config__type"
                            value="alpha"
                            type="radio"
                            id="captcha-image-alpha" 
                            <?php $this->checked('captcha_modules.image.config.type', 'alpha'); ?> /> 
                        <label for="captcha-image-alpha" class="radio-label">
                            <?php
                            _e(
                                'panel',
                                'setting_note_image_captcha_2',
                                'A string with lower and uppercase letters only.'
                            );
                            ?>
                        </label> 
                    </div>
                    <div class="radio-style">
                        <input name="captcha_modules__image__config__type"
                            value="numeric"
                            type="radio"
                            id="captcha-image-numeric" 
                            <?php $this->checked('captcha_modules.image.config.type', 'numeric'); ?> /> 
                        <label for="captcha-image-numeric" class="radio-label">
                            <?php _e('panel', 'setting_note_image_captcha_3', 'Numeric string only.'); ?>
                        </label> 
                    </div>
                </div>
            </td>
        </tr>
    </table>
</div>
<div class="section-body my-0" data-parent="captcha-image-section">
    <table class="setting-table">
        <tr>
            <td class="r1"><?php _e('panel', 'setting_label_length', 'Length'); ?></td>
            <td class="r2">
                <input type="text"
                    name="captcha_modules__image__config__length"
                    class="form-control form-control-sm col-sm-3"
                    value="<?php $this->_('captcha_modules.image.config.length'); ?>">
                <br />
                <p>
                    <?php
                    _e(
                        'panel',
                        'setting_note_image_captcha_length',
                        'How many characters do you like to display on CAPTCHA.'
                    );
                    ?>
                </p>
            </td>
        </tr>
    </table>
</div>