<?php defined('SHIELDON_VIEW') || exit('Life is short, why are you wasting time?');
/*
 * This file is part of the Shieldon package.
 *
 * (c) Terry L. <contact@terryl.in>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
?>
<div class="section-title bg-glass">
    <h2>reCAPTCHA</h2>
    <div class="toggle-container">
        <label class="rocker rocker-md">
            <input type="checkbox" name="captcha_modules_recaptcha_enable" class="toggle-block" value="on" data-target="captcha-recaptcha-section" <?php $this->checked('captcha_modules.recaptcha.enable', true); ?> />
            <span class="switch-left"><i class="fas fa-check"></i></span>
            <span class="switch-right"><i class="fas fa-times"></i></span>
        </label>
    </div>
</div>
<div class="section-body my-0" data-parent="captcha-recaptcha-section">
    <table class="setting-table">
        <tr>
            <td class="r1"></td>
            <td class="r2">
                <p>
                    Check HTTP referrer information.
                </p>
            </td>
        </tr>
        <tr>
            <td class="r1">Site Key</td>
            <td class="r2">
                <input type="text" name="captcha_modules_recaptcha_config_site_key" class="form-control form-control-sm col-sm-3" value="<?php $this->_('captcha_modules.recaptcha.config.site_key'); ?>"><br />
                <p>Enter Google reCaptcha site key for your webiste.</p>
            </td>
        </tr>
    </table>
</div>
<div class="section-body my-0" data-parent="captcha-recaptcha-section">
    <table class="setting-table">
        <tr>
            <td class="r1">Secret Key</td>
            <td class="r2">
                <input type="text" name="captcha_modules_recaptcha_config_secret_key" class="form-control form-control-sm col-sm-3" value="<?php $this->_('captcha_modules.recaptcha.config.secret_key'); ?>"><br />
                <p>Enter Google reCahptcha secret key for your webiste.</p>
            </td>
        </tr>
    </table>
</div>
<div class="section-body my-0" data-parent="captcha-recaptcha-section">
    <table class="setting-table">
        <tr>
            <td class="r1">Version</td>
            <td class="r2">
                <div class="container">
                    <div ckass="row">
                        <div class="col-sm-12">
                            <div class="radio-style">
                                <input name="captcha_modules_recaptcha_config_version" value="v2" type="radio" id="recaptcha-version-v2" <?php $this->checked('captcha_modules.recaptcha.config.version', 'v2'); ?> /> 
                                <label for="recaptcha-version-v2" class="radio-label">
                                    v2
                                </label> 
                            </div>
                            <div class="radio-style">
                                <input name="captcha_modules_recaptcha_config_version" value="v3" type="radio" id="recaptcha-version-v3" <?php $this->checked('captcha_modules.recaptcha.config.version', 'v3'); ?> /> 
                                <label for="recaptcha-version-v3" class="radio-label">
                                    v3
                                </label> 
                            </div>
                            <p>Please use corresponding key for that version you choose, otherwise it won't work.</p>
                        </div>
                    </div>
                </div>

            </td>
        </tr>
    </table>
</div>
<div class="section-body my-0" data-parent="captcha-recaptcha-section">
    <table class="setting-table">
        <tr>
            <td class="r1">Language Code</td>
            <td class="r2">
                <input type="text" name="captcha_modules_recaptcha_config_lang" class="form-control form-control-sm col-sm-3" value="<?php $this->_('captcha_modules.recaptcha.config.lang'); ?>"><br />
                <p>ISO 639 - ISO 3166 code. For example, zh-TW stands for Tranditional Chinese of Taiwan.</p>
            </td>
        </tr>
    </table>
</div>
                

<!-------------------------------------------------------------------------------------------------------------->
<div class="section-title bg-glass mt-3">
    <h2>Image</h2>
    <div class="toggle-container">
        <label class="rocker rocker-md">
            <input type="checkbox" name="captcha_modules_image_enable" class="toggle-block" value="on" data-target="captcha-image-section" <?php $this->checked('captcha_modules.image.enable', true); ?> />
            <span class="switch-left"><i class="fas fa-check"></i></span>
            <span class="switch-right"><i class="fas fa-times"></i></span>
        </label>
    </div>
</div>
<div class="section-body my-0" data-parent="captcha-image-section">
    <table class="setting-table">
        <tr>
            <td class="r1">Language Code</td>
            <td class="r2">
                <div class="container">
                    <div ckass="row">
                        <div class="col-sm-12">
                            <div class="radio-style">
                                <input name="captcha_modules_image_config_type" value="album" type="radio" id="captcha-image-album" <?php $this->checked('captcha_modules.image.config.type', 'alnum'); ?> /> 
                                <label for="captcha-image-album" class="radio-label">
                                    Alpha-numeric string with lower and uppercase characters.
                                </label> 
                            </div>
                            <div class="radio-style">
                                <input name="captcha_modules_image_config_type" value="alpha" type="radio" id="captcha-image-alpha" <?php $this->checked('captcha_modules.image.config.type', 'alpha'); ?> /> 
                                <label for="captcha-image-alpha" class="radio-label">
                                    A string with lower and uppercase letters only.
                                </label> 
                            </div>
                            <div class="radio-style">
                                <input name="captcha_modules_image_config_type" value="numeric" type="radio" id="captcha-image-numeric" <?php $this->checked('captcha_modules.image.config.type', 'numeric'); ?> /> 
                                <label for="captcha-image-numeric" class="radio-label">
                                    Numeric string only.
                                </label> 
                            </div>
                        </div>
                    </div>
                </div>
            </td>
        </tr>
    </table>
</div>
<div class="section-body my-0" data-parent="captcha-image-section">
    <table class="setting-table">
        <tr>
            <td class="r1">Length</td>
            <td class="r2">
                <input type="text" name="captcha_modules_image_config_length" class="form-control form-control-sm col-sm-3" value="<?php $this->_('captcha_modules.image.config.length'); ?>"><br />
                <p>How many characters do you like to display on CAPTCHA.</p>
            </td>
        </tr>
    </table>
</div>