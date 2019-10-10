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
    <h2>Dialog UI</h2>
</div>
<div class="section-body my-0">
    <table class="setting-table">
        <tr>
            <td class="r1">Language</td>
            <td class="r2">
                <div class="container">
                    <div ckass="row">
                        <div class="col-sm-12">
                            <div class="radio-style">
                                <input name="dialog_ui__lang" value="en" type="radio" id="lang-1" <?php $this->checked('dialog_ui.lang', 'en'); ?> /> 
                                <label for="lang-1" class="radio-label">
                                    English (en)
                                </label> 
                            </div>
                            <div class="radio-style">
                                <input name="dialog_ui__lang" value="zh" type="radio" id="lang-2" <?php $this->checked('dialog_ui.lang', 'zh'); ?> /> 
                                <label for="lang-2" class="radio-label">
                                    中文 (zh))
                                </label> 
                            </div>
                            <div class="radio-style">
                                <input name="dialog_ui__lang" value="zh_CN" type="radio" id="lang-3" <?php $this->checked('dialog_ui.lang', 'zh_CN'); ?> /> 
                                <label for="lang-3" class="radio-label">
                                    中文 (简体) (zh_CN)
                                </label> 
                            </div>
                        </div>
                    </div>
                </div>
            </td>
        </tr>
    </table>
</div>
<div class="section-body my-0">
    <table class="setting-table">
        <tr>
            <td class="r1">Background Image</td>
            <td class="r2">
                <input type="text" name="dialog_ui__background_image" class="form-control form-control-sm col-sm-3" value="<?php $this->_('dialog_ui.background_image'); ?>"><br />
                <p>Please add a full URL or relative path of the image.</p>
            </td>
        </tr>
    </table>
</div>
<div class="section-body my-0">
    <table class="setting-table">
        <tr>
            <td class="r1">Background Color</td>
            <td class="r2">
                <input type="text" name="dialog_ui__background_color" class="form-control form-control-sm col-sm-3" value="<?php $this->_('dialog_ui.background_color'); ?>"><br />
                <p>You can spefic a background color if you don't want to spefic the background image.</p>
            </td>
        </tr>
    </table>
</div>
<div class="section-body my-0">
    <table class="setting-table">
        <tr>
            <td class="r1">Background Color<br /><small>Dialog Header</small></td>
            <td class="r2">
                <input type="text" name="dialog_ui__header_background_color" class="form-control form-control-sm col-sm-3" value="<?php $this->_('dialog_ui.header_background_color'); ?>"><br />
                <p>For example: <code>#00aeff</code> or <code>rgb(0,174,255)</code></p>
            </td>
        </tr>
    </table>
</div>
<div class="section-body my-0">
    <table class="setting-table">
        <tr>
            <td class="r1">Font Color<br /><small>Dialog Header</small></td>
            <td class="r2">
                <input type="text" name="dialog_ui__header_color" class="form-control form-control-sm col-sm-3" value="<?php $this->_('dialog_ui.header_color'); ?>"><br />
                <p>For example: <code>#00aeff</code> or <code>rgb(0,174,255)</code></p>
            </td>
        </tr>
    </table>
</div>
<div class="section-body my-0">
    <table class="setting-table">
        <tr>
            <td class="r1">Shadow Opacity</td>
            <td class="r2">
                <input type="text" name="dialog_ui__shadow_opacity" class="form-control form-control-sm col-sm-3" value="<?php $this->_('dialog_ui.shadow_opacity'); ?>"><br />
                <p>The range from 0 to 1, for example, 0.2 stands for 20% opacity.</p>
            </td>
        </tr>
    </table>
</div>
