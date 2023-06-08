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
    <h2><?php _e('panel', 'setting_heading_adminlogin', 'Admin Login'); ?></h2>
</div>
<div class="section-body my-0">
    <table class="setting-table">
        <tr>
            <td class="r1"><?php _e('panel', 'setting_label_user', 'User'); ?></td>
            <td class="r2">
                <input type="text"
                    name="admin__user"
                    class="form-control form-control-sm col-sm-3"
                    value="<?php $this->_('admin.user'); ?>">
                <br />
            </td>
        </tr>
    </table>
</div>
<div class="section-body my-0">
    <table class="setting-table">
        <tr>
            <td class="r1"><?php _e('panel', 'setting_label_password', 'Password'); ?></td>
            <td class="r2">
                <input type="text"
                    name="admin__pass"
                    class="form-control form-control-sm col-sm-3"
                    value="<?php $this->_('admin.pass'); ?>">
                <br />
            </td>
        </tr>
    </table>
</div>
<div class="section-body my-0">
    <table class="setting-table">
        <tr>
            <td class="r1"><?php _e('panel', 'setting_label_last_modified', 'Last Modified'); ?></td>
            <td class="r2">
                <?php $this->_('admin.last_modified'); ?>
                <input type="hidden" name="admin__last_modified" value="<?php echo date('Y-m-d H:i:s'); ?>">
            </td>
        </tr>
    </table>
</div>