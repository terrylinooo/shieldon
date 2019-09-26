<?php defined('SHIELDON_VIEW') || exit('Life is short, why are you wasting time?');
/*
 * This file is part of the Shieldon package.
 *
 * (c) Terry L. <contact@terryl.in>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

$timezone = '';

?>

<div class="so-dashboard">
    <div id="so-rule-table-form" class="so-datatables">
        <div class="so-datatable-heading">
            Rule Table<br />
        </div>
        <div class="so-datatable-description">
            This is where the Shieldon temporarily allows or denys users in current cycle. 
            All processes are automatic and instant, you can ignore that.<br />
            Rule table will be reset after new cycle begins.
        </div>
        <div class="so-rule-form">
            <form method="post">
                <input name="ip" type="text" value="" class="regular-text" placeholder="Please fill in an IP address..">
                <select name="action" class="regular">
                    <option value="none">--- please select ---</option>
                    <option value="temporarily_ban">Deny this IP temporarily</option>
                    <option value="permanently_ban">Deny this IP permanently</option>
                    <option value="allow">Allow this IP</option>
                    <option value="remove">Remove this IP</option>
                </select>
                <input type="submit" name="submit" id="btn-add-rule" class="button button-primary" value="Submit">
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
        <table id="so-datalog" class="cell-border compact stripe" cellspacing="0" width="100%">
            <thead>
                <tr>
                    <th>IP</th>
                    <th>Resolved hostname</th>
                    <th>Type</th>
                    <th>Reason</th>
                    <th>Time</th>
                    <th>Remove</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach($rule_list as $ipInfo) : ?>
                <tr>
                    <td><?php echo $ipInfo['log_ip']; ?></td>
                    <td><?php echo $ipInfo['ip_resolve']; ?></td>
                    <td>
                        <?php 
                            if (! empty($type_mapping[$ipInfo['type'] ]) ) {
                                echo $type_mapping[$ipInfo['type'] ];
                            }
                        ?>
                    </td>
                    <td>
                        <?php
                            if (! empty($reason_mapping[$ipInfo['reason'] ]) ) {
                                echo $reason_mapping[$ipInfo['reason'] ];
                            }
                        ?>
                    </td>
                    <td><?php echo date('Y-m-d H:i:s', $ipInfo['time']); ?></td>
                    <td><button type="button" class="button btn-remove-ip" data-ip="<?php $ipInfo['log_ip']; ?>"><i class="far fa-trash-alt"></i></button></td>
                </tr>
                <?php endforeach; ?>
            </tbody>   
        </table>
    </div>
</div>

<script>

    $(function() {
        $('#so-datalog').DataTable({
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