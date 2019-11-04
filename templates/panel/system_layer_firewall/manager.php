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

?>
<div class="so-dashboard">
    <div id="so-rule-table-form" class="so-datatables">
        <div class="so-datatable-heading">
            <?php _e('panel', 'iptable_heading', 'Iptables Manager'); ?>
        </div>
        <div class="so-datatable-description">
            <?php _e('panel', 'iptable_description_1', 'This is Web Interface of iptables / ip6tables, be careful of using this function, preventing to block yourself.'); ?><br />
            <?php _e('panel', 'iptable_description_2', 'You can only manage incoming requests here.'); ?><br />
            <?php _e('panel', 'iptable_description_3', 'After you reboot your server, the rules here will be disappeared. Using <strong>iptables-save</strong> youself to keep the rules exist.'); ?>
        </div>
        <div class="so-rule-form">
            <form method="post">
                <div class="d-inline-block align-top">
                    <label for="ip-address"><?php _e('panel', 'iptables_label_ip', 'IP'); ?></label><br />
                    <input name="ip" type="text" value="" id="ip-address" class="regular-text ">
                    <span class="form-text text-muted">e.g. <code>1.1.1.1</code> , <code>127.0.0.1/24</code></span>
                </div>
                <div class="d-inline-block align-top">
                    <label for="port"><?php _e('panel', 'iptables_label_port', 'Port'); ?></label><br />
                    <select name="port" class="regular" id="port">
                        <option value="all">All</option>
                        <option value="21">FTP - 21</option>
                        <option value="22">SSH - 22</option>
                        <option value="23">Telnet - 23</option>
                        <option value="25">SMTP - 25</option>
                        <option value="80">HTTP - 80</option>
                        <option value="110">POP3 - 110</option>
                        <option value="143">IMAP - 143</option>
                        <option value="443">HTTPS - 443</option>
                        <option value="3306">MySQL - 3306</option>
                        <option value="6379">Redis - 6379</option>
                        <option value="27017">MongoDB- 27017</option>
                        <option value="custom"><?php _e('panel', 'iptables_label_port_custom', 'Custom'); ?></option>
                    </select>
                    <input name="port_custom" type="text" value="" class="d-none" id="ip-address" style="width: 60px">
                </div>
                <div class="d-inline-block align-top">
                    <label for="action"><?php _e('panel', 'iptables_label_protocol', 'Protocol'); ?></label><br />
                    <select name="protocol" class="regular" id="action">
                        <option value="all"><?php _e('panel', 'iptables_label_protocol_all', 'All'); ?></option>
                        <option value="tcp"><?php _e('panel', 'iptables_label_protocol_tcp', 'TCP'); ?></option>
                        <option value="udp"><?php _e('panel', 'iptables_label_protocol_udp', 'UDP'); ?></option>
                    </select>
                </div>
                <div class="d-inline-block align-top">
                    <label for="action"><?php _e('panel', 'ipma_label_action', 'Action'); ?></label><br />
                    <select name="action" class="regular" id="action">
                        <option value="allow"><?php _e('panel', 'iptables_label_action_drop', 'DROP'); ?></option>
                        <option value="deny"><?php _e('panel', 'iptables_label_action_allow', 'ALLOW'); ?></option>
                    </select>
                </div>
                <div class="d-inline-block align-top">
                    <label class="visible">&nbsp;</label><br />
                    <input type="submit" name="submit" id="btn-add-rule" class="button button-primary" value="<?php _e('panel', 'auth_btn_submit', 'Submit'); ?>">
                </div>
            </form>
        </div>
    </div>
    <br />
    <div id="so-table-loading" class="so-datatables">
        <div class="lds-css ng-scope">
            <div class="lds-ripple">
                <div></div>
                <div></div>
            </div>
        </div>
    </div>
    <div id="so-table-container" class="so-datatables" style="display: none;">
        <table id="so-datalog" class="cell-border compact stripe responsive" cellspacing="0" width="100%">
            <thead>
                <tr>
                    <th><?php _e('panel', 'iptables_label_ip', 'IP'); ?></th>
                    <th><?php _e('panel', 'iptables_label_port', 'Port'); ?></th>
                    <th><?php _e('panel', 'iptables_label_protocol', 'Protocol'); ?></th>
                    <th><?php _e('panel', 'ipma_label_action', 'Action'); ?></label></th>
                    <th><th><?php _e('panel', 'auth_label_remove', 'Remove'); ?></th>
                </tr>
            </thead>
            <tbody>
                <?php if (! empty($ip_list)) : ?>
                <?php foreach($ip_list as $i => $ipInfo) : ?>
                <tr>
                    <td>
                        <?php if ($this->mode === 'demo') : ?>
                        <?php $ipInfo['ip'] = mask_string($ipInfo['ip']); ?>
                        <?php endif; ?>

                        <?php echo $ipInfo['ip']; ?>
                    </td>
                    <td><?php echo $ipInfo['port']; ?></td>
                    <td><?php echo $ipInfo['protocol']; ?></td>
                    <td><?php echo $ipInfo['action']; ?></td>
                    <td>
                        <button type="button" class="button btn-remove-ip" 
                            data-ip="<?php echo $ipInfo['ip']; ?>" 
                            data-port="<?php echo $ipInfo['port']; ?>" 
                            data-protocol="<?php echo $ipInfo['protocol']; ?>" 
                            data-action="<?php echo $ipInfo['action']; ?>">
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

<?php if (! empty($ip_list)) : ?>

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

<?php endif; ?>

<script>

    $(function() {
        $('select[name="port"]').change(function() {
            if ($(this).val() === 'custom') {
                $('input[name="port_custom"]').removeClass('d-none');
            } else {
                $('input[name="port_custom"]').addClass('d-none');
            }
        });
    });

</script>