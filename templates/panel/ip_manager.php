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
use function Shieldon\Firewall\mask_string;

$timezone = '';

?>

<div class="so-dashboard">
    <div id="so-rule-table-form" class="so-datatables">
        <div class="so-datatable-heading">
            <?php _e('panel', 'ipma_heading', 'IP Manager'); ?>
        </div>
        <div class="so-datatable-description">
            <?php
            _e(
                'panel',
                'ipma_description',
                'IP Manager is not like Rule Table (effective period depends on the data cycle), 
                everything you have done here is permanent.'
            );
            ?>
            <br />
        </div>
        <div class="so-rule-form iptables-form">
            <form method="post" onsubmit="freezeUI();">
                <?php echo $this->fieldCsrf(); ?>
                <div class="d-inline-block align-top">
                    <label for="url-path"><?php _e('panel', 'auth_label_url_path', 'URL Path'); ?></label><br />
                    <input name="url" type="text" value="" id="url-path" class="regular-text">
                    <span class="form-text text-muted">e.g. <code>/url-path/</code></span>
                </div>
                <div class="d-inline-block align-top">
                    <label for="ip-address"><?php _e('panel', 'ipma_label_ip', 'IP'); ?></label><br />
                    <input name="ip" type="text" value="" id="ip-address">
                    <span class="form-text text-muted">e.g. <code>1.1.1.1</code> , <code>127.0.0.1/24</code></span>
                </div>
                <div class="d-inline-block align-top">
                    <label for="action"><?php _e('panel', 'ipma_label_action', 'Action'); ?></label><br />
                    <select name="action" class="regular" id="action">
                        <option value="none"><?php _e('panel', 'ipma_label_plz_select', 'Please select'); ?></option>
                        <option value="allow"><?php _e('panel', 'ipma_label_allow_ip', 'Allow this IP'); ?></option>
                        <option value="deny"><?php _e('panel', 'ipma_label_deny_ip', 'Deny this IP'); ?></option>
                        <option value="remove"><?php _e('panel', 'ipma_label_remove_ip', 'Remove this IP'); ?></option>
                    </select>
                </div>
                <div class="d-inline-block align-top">
                    <label for="order"><?php _e('panel', 'ipma_label_order', 'Order'); ?></label><br />
                    <input name="order" type="text" value="1" id="order" placeholder="1" style="width: 50px">
                </div>
                <div class="d-inline-block align-top">
                    <label class="visible">&nbsp;</label><br />
                    <input type="submit"
                        name="submit"
                        id="btn-add-rule"
                        class="button button-primary"
                        value="<?php _e('panel', 'auth_btn_submit', 'Submit'); ?>"
                    >
                </div>
            </form>
        </div>
    </div>
    <br />
    <?php if (empty($ip_list)) : ?>
    <div id="so-table-container" class="so-datatables">
        <table id="so-datalog" class="cell-border compact stripe responsive" cellspacing="0" width="100%">
            <tbody>
                <tr>
                    <td>
                        <?php _e('panel', 'ipma_text_nodata', 'No data is available now.'); ?>
                    </td>
                </tr>
            </tbdoy>
        </table>
    </div>
    <?php else : ?>
    <div id="so-table-loading" class="so-datatables">
        <div class="lds-css ng-scope">
            <div class="lds-ripple">
                <div></div>
                <div></div>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <div id="so-table-container" class="so-datatables" style="display: none;">
        <table id="so-datalog" class="cell-border compact stripe responsive" cellspacing="0" width="100%">
            <thead>
                <tr>
                    <th><?php _e('panel', 'ipma_label_order', 'Order'); ?></th>
                    <th><?php _e('panel', 'auth_label_url_path', 'URL Path'); ?></th>
                    <th><?php _e('panel', 'ipma_label_ip', 'IP'); ?></th>
                    <th><?php _e('panel', 'ipma_label_rule', 'Rule'); ?></th>
                    <th><?php _e('panel', 'auth_label_remove', 'Remove'); ?></th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($ip_list)) : ?>
                    <?php foreach ($ip_list as $i => $ipInfo) : ?>
                    <tr>
                        <td>
                            <?php echo $i + 1; ?>
                        </td>
                        <td>
                            <?php echo $ipInfo['url']; ?>
                        </td>
                        <td>

                            <?php if ($this->mode === 'demo') : ?>
                                <?php $ipInfo['ip'] = mask_string($ipInfo['ip']); ?>
                            <?php endif; ?>

                            <?php echo $ipInfo['ip']; ?>
                        </td>
                        <td>
                            <?php echo $ipInfo['rule']; ?>
                        </td>
                        <td>
                            <button type="button"
                                class="button btn-remove-ip"
                                data-ip="<?php echo $ipInfo['ip']; ?>"
                                data-order="<?php echo ($i + 1); ?>">
                                    <i class="far fa-trash-alt"></i>
                            </button>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>   
        </table>
    </div>
</div>

<?php if (!empty($ip_list)) : ?>
<script>

    $(function() {
        $('#so-datalog').DataTable({
            'responsive': true,
            'pageLength': 25,
            'initComplete': function(settings, json) {
                $('#so-table-loading').hide();
                $('#so-table-container').fadeOut(800);
                $('#so-table-container').fadeIn(800);
            }
        });

        $('.so-dashboard').on('click', '.btn-remove-ip', function() {
            var order = $(this).attr('data-order');
            var ip = $(this).attr('data-ip');

            $('[name=ip]').val(ip);
            $('[name=order]').val(order);
            $('[name=action]').val('remove');
            $('#btn-add-rule').trigger('click');
        });
    });

</script>
<?php endif;
