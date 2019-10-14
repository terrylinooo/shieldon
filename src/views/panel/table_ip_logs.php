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
use function Shieldon\Helper\mask_string;

$timezone = '';

?>

<div class="so-dashboard">
    <div id="so-table-loading" class="so-datatables">
        <div class="lds-css ng-scope">
            <div class="lds-ripple">
                <div></div>
                <div></div>
            </div>
        </div>
    </div>
    <div id="so-table-container" class="so-datatables" style="display: none;">
        <div class="so-datatable-heading">
            IP Log Table
        </div>
        <div class="so-datatable-description">
            This is where the Shieldon records the users' strange behavior.<br />
            All processes are automatic and instant, you can ignore that.<br />
            IP log table will be all cleared after new cycle begins.
        </div>
        <table id="so-datalog" class="cell-border compact stripe" cellspacing="0" width="100%">
            <thead>
                <tr>
                    <th rowspan="2">IP</th>
                    <th rowspan="2">Resolved hostname</th>
                    <th colspan="4" class="merged-field">Pageviews</th>
                    <th colspan="3" class="merged-field">Flags</th>
                    <th rowspan="2">Last visit</th>
                </tr>
                <tr>
                    <th>S</th>
                    <th>M</th>
                    <th>H</th>
                    <th>D</th>
                    <th>Cookie</th>
                    <th>Session</th>
                    <th>Referrer</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach($ip_log_list as $ipInfo) : ?>
                    <?php $logData = is_array($ipInfo['log_data']) ? $ipInfo['log_data'] : json_decode($ipInfo['log_data'], true ); ?>
                    <?php

                        $text_warning = '';

                        if ($logData['pageviews_m'] > 6 || $logData['pageviews_h'] > 50 || $logData['pageviews_d'] > 100 ) {
                            $text_warning = '<span class="so-text-warning"><i class="fas fa-exclamation-triangle"></i></span>';
                        }

                        if ($logData['flag_js_cookie'] > 2 || $logData['flag_multi_session'] > 2 || $logData['flag_empty_referer'] > 2 ) {
                            $text_warning = '<span class="so-text-warning"><i class="fas fa-exclamation-triangle"></i></span>';
                        }

                        if ($logData['flag_js_cookie'] > 3 || $logData['flag_multi_session'] > 3 || $logData['flag_empty_referer'] > 3 ) {
                            $text_warning = '<span class="so-text-danger"><i class="fas fa-exclamation-triangle"></i></span>';
                        }
                    ?>
                    <tr>
                        <td>
                            <?php if ($this->mode === 'demo') : ?>
                                <?php $ipInfo['log_ip'] = mask_string($ipInfo['log_ip']); ?>
                            <?php endif; ?>
                            <?php echo $ipInfo['log_ip']; ?>
                            
                            <?php echo $text_warning; ?>
                        </td>
                        <td><?php echo $logData['hostname']; ?></td>
                        <td><?php echo $logData['pageviews_s']; ?></td>
                        <td><?php echo $logData['pageviews_m']; ?></td>
                        <td><?php echo $logData['pageviews_h']; ?></td>
                        <td><?php echo $logData['pageviews_d']; ?></td>
                        <td><?php echo $logData['flag_js_cookie']; ?></td>
                        <td><?php echo $logData['flag_multi_session']; ?></td>
                        <td><?php echo $logData['flag_empty_referer']; ?></td>
                        <td><?php echo date('Y-m-d H:i:s', $logData['last_time']); ?></td>
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
    });

</script>