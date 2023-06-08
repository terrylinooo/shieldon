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
            <?php _e('panel', 'table_heading_rule', 'Rule Table'); ?>
        </div>
        <div class="so-datatable-description">
            <?php
            _e(
                'panel',
                'table_description_rule_1',
                'Shieldon temporarily allows or denies access to users in this table.'
            );
            ?>
            <br />
            <?php
            _e(
                'panel',
                'table_description_rule_3',
                'Rule table will be reset when a new cycle begins.'
            );
            ?>
        </div>
        <div class="so-rule-form iptables-form">
            <form method="post">
                <?php echo $this->fieldCsrf(); ?>
                <input name="ip"
                    type="text" value=""
                    class="regular-text"
                    placeholder="<?php _e('panel', 'table_ip_placeholder', 'Please fill in an IP address..'); ?>"
                >
                <select name="action" class="regular">
                    <option value="none">
                        <?php _e('panel', 'ipma_label_plz_select', 'Please select'); ?>
                    </option>
                    <option value="temporarily_ban">
                        <?php _e('panel', 'table_label_deny_ip_temporarily', 'Deny this IP temporarily'); ?>
                    </option>
                    <option value="permanently_ban">
                        <?php _e('panel', 'table_label_deny_ip_permanently', 'Deny this IP permanently'); ?>
                    </option>
                    <option value="allow">
                        <?php _e('panel', 'ipma_label_allow_ip', 'Allow this IP'); ?>
                    </option>
                    <option value="remove">
                        <?php _e('panel', 'ipma_label_remove_ip', 'Remove this IP'); ?>
                    </option>
                </select>
                <input type="submit"
                    name="submit"
                    id="btn-add-rule"
                    class="button button-primary"
                    value="<?php _e('panel', 'auth_btn_submit', 'Submit'); ?>"
                >
            </form>
        </div>
    </div>
    <br />
    <?php if (empty($rule_list)) : ?>
    <div id="so-table-container" class="so-datatables">
        <table class="cell-border compact stripe responsive" cellspacing="0" width="100%">
            <thead>
                <tr>
                    <th>
                        <?php _e('panel', 'ipma_text_nodata', 'No data is available now.'); ?>
                    </th>
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
    <?php if (!empty($rule_list)) : ?>
    <div id="so-table-container" class="so-datatables" style="display: none;">
        <table id="so-datalog" class="cell-border compact stripe responsive" cellspacing="0" width="100%">
            <thead>
                <tr>
                    <th><?php _e('panel', 'overview_label_ip', 'IP'); ?></th>
                    <th><?php _e('panel', 'table_label_resolved_hostname', 'Resolved hostname'); ?></th>
                    <th><?php _e('panel', 'table_label_type', 'Type'); ?></th>
                    <th><?php _e('panel', 'table_label_reason', 'Reason'); ?></th>
                    <th><?php _e('panel', 'table_label_time', 'Time'); ?></th>
                    <th><?php _e('panel', 'table_label_remove', 'Remove'); ?></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($rule_list as $ipInfo) : ?>
                <tr>
                    <td>
                        <?php if ($this->mode === 'demo') : ?>
                            <?php $ipInfo['log_ip'] = mask_string($ipInfo['log_ip']); ?>
                        <?php endif; ?>
                        <?php echo $ipInfo['log_ip']; ?>
                    </td>
                    <td><?php echo $ipInfo['ip_resolve']; ?></td>
                    <td>
                        <?php
                        if (!empty($type_mapping[$ipInfo['type']])) {
                            echo $type_mapping[$ipInfo['type']];
                        }
                        ?>
                    </td>
                    <td>
                        <?php
                        if (!empty($reason_mapping[$ipInfo['reason']])) {
                            echo $reason_mapping[$ipInfo['reason']];
                        }
                        ?>
                    </td>
                    <td>
                        <?php echo date('Y-m-d H:i:s', $ipInfo['time']); ?>
                    </td>
                    <td>
                        <button type="button" class="button btn-remove-ip" data-ip="<?php echo $ipInfo['log_ip']; ?>">
                            <i class="far fa-trash-alt"></i>
                        </button>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>   
        </table>
    <?php endif; ?>
    </div>
</div>

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
            var ip = $(this).attr('data-ip');

            $('[name=ip]').val(ip);
            $('[name=action]').val('remove');
            $('#btn-add-rule').trigger('click');
        });
    });

</script>