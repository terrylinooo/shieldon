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

?>
<div class="so-dashboard">
    <div id="so-rule-table-form" class="so-datatables">
        <div class="so-datatable-heading">
            <?php _e('panel', 'iptable_heading', 'iptables Manager'); ?> (<?php echo $type; ?>)
        </div>
        <?php if ('IPv4' === $type) : ?>
        <div class="so-datatable-description">
            <?php
            _e(
                'panel',
                'iptable_description_1',
                'This is Web Interface of <strong>iptables</strong>, be careful of using this function.'
            );
            ?><br />
            <?php
            _e(
                'panel',
                'iptable_description_2',
                'You can only manage incoming requests here.'
            );
            ?><br />
            <?php
            _e(
                'panel',
                'iptable_description_3',
                'After you reboot your server, the rules here will be disappeared. 
                Using <strong>iptables-save</strong> youself to keep the rules exist.'
            );
            ?>
        </div>
        <?php endif; ?>
        <?php if ('IPv6' === $type) : ?>
        <div class="so-datatable-description">
            <?php
            _e(
                'panel',
                'ip6table_description_1',
                'This is Web Interface of <strong>ip6tables</strong>, be careful of using this function.'
            );
            ?>
            <br />
            <?php
            _e(
                'panel',
                'ip6table_description_2',
                'You can only manage incoming requests here.'
            );
            ?>
            <br />
            <?php
            _e(
                'panel',
                'ip6table_description_3',
                'After you reboot your server, the rules here will be disappeared. 
                Using <strong>ip6tables-save</strong> youself to keep the rules exist.'
            );
            ?>
        </div>
        <?php endif; ?>
        <div class="so-rule-form iptables-form">
            <form method="post" onsubmit="freezeUI();">
                <?php echo $this->fieldCsrf(); ?>
                <div class="d-inline-block align-top">
                    <label for="ip-address" style="padding-left: 10px;">
                        <?php _e('panel', 'iptables_label_ip', 'IP'); ?>
                    </label><br />
                    <input name="ip" type="text" value="" id="ip-address" class="regular-text ">
                </div>
                <div class="d-inline-block align-top">
                    <label for="subnet" style="padding-left: 20px">
                        <?php _e('panel', 'iptables_label_subnet', 'Subnet'); ?>
                    </label><br />
                    <span class="seperate">/<span>
                    <?php if ('IPv4' === $type) : ?>
                    <select name="subnet" class="regular" id="subnet">
                        <option value="null">---</option>
                        <?php for ($i = 32; $i > 0; $i--) : ?>
                            <?php $label = $i;  ?>
                            <?php ($i === 8) ? $label = $i . ' (A)' : ''; ?>
                            <?php ($i === 16) ? $label = $i . ' (B)' : ''; ?>
                            <?php ($i === 24) ? $label = $i . ' (C)' : ''; ?>
                            <option value="<?php echo $i; ?>"><?php echo $label; ?></option>
                        <?php endfor; ?>
                    </select>
                    <?php endif; ?>
                    <?php if ('IPv6' === $type) : ?>
                    <select name="subnet" class="regular" id="subnet">
                        <option value="null">---</option>
                        <?php for ($i = 128; $i > 0; $i--) : ?>
                            <?php $label = $i;  ?>
                            <option value="<?php echo $i; ?>"><?php echo $label; ?></option>
                        <?php endfor; ?>
                    </select>
                    <?php endif; ?>
                </div>
                <div class="d-inline-block align-top">
                    <label for="port" style="padding-left: 20px">
                        <?php _e('panel', 'iptables_label_port', 'Port'); ?>
                    </label>
                    <br />
                    <span class="seperate">:</span>
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
                    <label for="protocol" style="padding-left: 20px">
                        <?php _e('panel', 'iptables_label_protocol', 'Protocol'); ?>
                    </label>
                    <br />
                    <span class="seperate">(<span>
                    <select name="protocol" class="regular" id="protocol">
                        <option value="all"><?php _e('panel', 'iptables_label_protocol_all', 'All'); ?></option>
                        <option value="tcp"><?php _e('panel', 'iptables_label_protocol_tcp', 'TCP'); ?></option>
                        <option value="udp"><?php _e('panel', 'iptables_label_protocol_udp', 'UDP'); ?></option>
                    </select>
                    
                </div>
                <div class="d-inline-block align-top">
                    <label for="action" style="padding-left: 20px">
                        <?php _e('panel', 'ipma_label_action', 'Action'); ?>
                    </label>
                    <br />
                    <span class="seperate">)<span>
                    <select name="action" class="regular" id="action">
                        <option value="allow"><?php _e('panel', 'iptables_label_action_allow', 'allow'); ?></option>
                        <option value="deny"><?php _e('panel', 'iptables_label_action_deny', 'deny'); ?></option>
                    </select>
                    <input type="hidden" name="remove" value="no">
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
    <?php if (empty($ipCommand)) : ?>
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
                    <th><?php _e('panel', 'iptables_label_ip', 'IP'); ?></th>
                    <th><?php _e('panel', 'iptables_label_port', 'Port'); ?></th>
                    <th><?php _e('panel', 'iptables_label_protocol', 'Protocol'); ?></th>
                    <th><?php _e('panel', 'ipma_label_action', 'Action'); ?></th>
                    <th><?php _e('panel', 'auth_label_remove', 'Remove'); ?></th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($ipCommand)) : ?>
                    <?php foreach ($ipCommand as $i => $ipInfo) : ?>
                        <?php $subnet = (!empty($ipInfo[3]) && $ipInfo[3] !== 'null') ? '/' . $ipInfo[3] : '' ?>
                        <tr>
                            <td>
                                <?php if ($this->mode === 'demo') : ?>
                                    <?php $ipInfo[2] = mask_string($ipInfo[2]); // ip ?>
                                <?php endif; ?>

                                <?php echo $ipInfo[2] . $subnet; ?>
                            </td>
                            <td><?php echo strtoupper($ipInfo[4]); // port ?></td>
                            <td><?php echo strtoupper($ipInfo[5]); // protocol ?></td>
                            <td><?php echo strtoupper($ipInfo[6]); // action ?></td>
                            <td>
                                <button type="button" class="button btn-remove-ip" 
                                    data-ip="<?php echo $ipInfo[2]; ?>" 
                                    data-subnet="<?php echo $ipInfo[3]; ?>" 
                                    data-port="<?php echo $ipInfo[4]; ?>" 
                                    data-protocol="<?php echo $ipInfo[5]; ?>" 
                                    data-action="<?php echo $ipInfo[6]; ?>">
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

<?php if (!empty($ipCommand)) : ?>
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
            var subnet = $(this).attr('data-subnet');
            var protocol = $(this).attr('data-protocol');
            var port = $(this).attr('data-port');
            var action = $(this).attr('data-action');

            $('[name=ip]').val(ip);
            $('[name=subnet]').val(subnet);
            $('[name=port]').val(port);
            $('[name=protocol]').val(protocol);
            $('[name=action]').val(action);
            $('[name=remove').val('yes');
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