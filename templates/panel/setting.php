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

$timezone = '';

?>
<form method="post">

<div class="so-setting-page">
    <div class="so-tab">
        <?php $this->_include('panel/setting/tab'); ?>
            
        <section id="content1" class="tab-section">
            <?php $this->_include('panel/setting/daemon'); ?>
        </section>

        <section id="content2" class="tab-section">
            <?php $this->_include('panel/setting/components'); ?>
        </section>

        <section id="content3" class="tab-section">
            <?php $this->_include('panel/setting/filters'); ?>
        </section>

        <section id="content4" class="tab-section">
            <?php $this->_include('panel/setting/captchas'); ?>
        </section>

        <section id="content5" class="tab-section">
            <?php $this->_include('panel/setting/dialog_ui'); ?>
        </section>

        <section id="content6" class="tab-section">
            <?php $this->_include('panel/setting/admin_login'); ?>
        </section>
    </div>
    <div class="d-flex justify-content-center py-2">
        <button type="submit" class="btn btn-enter"><i class="fas fa-fire-alt"></i> <?php _e('panel', 'overview_btn_save', 'SAVE'); ?></button>
    </div>
</div>
<?php $this->_csrf(); ?>
<input type="hidden" name="tab" value="daemon">
<input type="hidden" name="managed_by" value="firewall">
</form>

<?php $this->_include('panel/setting/import_export'); ?>

<?php $this->_include('panel/js/common'); ?>
