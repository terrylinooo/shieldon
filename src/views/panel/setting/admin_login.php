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
    <h2>Admin Login</h2>
</div>
<div class="section-body my-0">
    <table class="setting-table">
        <tr>
            <td class="r1">User</td>
            <td class="r2">
                <input type="text" name="admin__user" class="form-control form-control-sm col-sm-3" value="<?php $this->_('admin.user'); ?>"><br />
            </td>
        </tr>
    </table>
</div>
<div class="section-body my-0">
    <table class="setting-table">
        <tr>
            <td class="r1">Password</td>
            <td class="r2">
                <input type="text" name="admin__pass" class="form-control form-control-sm col-sm-3" value="<?php $this->_('admin.pass'); ?>"><br />
            </td>
        </tr>
    </table>
</div>
<div class="section-body my-0">
    <table class="setting-table">
        <tr>
            <td class="r1">Last Modified</td>
            <td class="r2">
                <?php $this->_('admin.last_modified'); ?>
                <input type="hidden" name="admin__last_modified" value="<?php echo date('Y-m-d H:i:s'); ?>">
            </td>
        </tr>
    </table>
</div>