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
            <?php _e('panel', 'table_heading_ip_log', 'IP Log Table'); ?>
        </div>
        <div class="so-datatable-description">
            <?php
            _e(
                'panel',
                'table_description_ip_log_1',
                'This is where the Shieldon records the users’ strange behavior.'
            );
            ?>
            <br />
            <?php
            _e(
                'panel',
                'table_description_ip_log_3',
                'IP log table will be all cleared after new cycle begins.'
            );
            ?>
            <br />
        </div>
        <table id="so-datalog" class="cell-border compact stripe responsive" cellspacing="0" width="100%">
            <thead>
                <tr>
                    <th rowspan="2">
                        <?php _e('panel', 'overview_label_ip', 'IP'); ?>
                    </th>
                    <th rowspan="2">
                        <?php _e('panel', 'table_label_resolved_hostname', 'Resolved hostname'); ?>
                    </th>
                    <th colspan="4" class="merged-field">
                        <?php _e('panel', 'table_label_pageviews', 'Pageviews'); ?>
                    </th>
                    <th colspan="3" class="merged-field">
                        <?php _e('panel', 'table_label_flags', 'Flags'); ?>
                    </th>
                    <th rowspan="2">
                        <?php _e('panel', 'table_label_last_visit', 'Last visit'); ?>
                    </th>
                </tr>
                <tr>
                    <th>S</th>
                    <th>M</th>
                    <th>H</th>
                    <th>D</th>
                    <th><?php _e('panel', 'overview_label_cookie', 'Cookie'); ?></th>
                    <th><?php _e('panel', 'overview_label_session', 'Session'); ?></th>
                    <th><?php _e('panel', 'overview_label_referer', 'Referrer'); ?></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($ip_log_list as $ipInfo) : ?>
                    <?php
                    $logData = is_array($ipInfo['log_data'])
                        ? $ipInfo['log_data']
                        : json_decode($ipInfo['log_data'], true);

                    $text_warning = '';

                    if ($logData['pageviews_m'] > 6 ||
                        $logData['pageviews_h'] > 50 ||
                        $logData['pageviews_d'] > 100
                    ) {
                        $text_warning = '<span class="so-text-warning">
                            <i class="fas fa-exclamation-triangle"></i>
                            </span>';
                    }

                    if ($logData['flag_js_cookie'] > 2 ||
                        $logData['flag_multi_session'] > 2 ||
                        $logData['flag_empty_referer'] > 2
                    ) {
                        $text_warning = '<span class="so-text-warning">
                            <i class="fas fa-exclamation-triangle"></i>
                            </span>';
                    }

                    if ($logData['flag_js_cookie'] > 3 ||
                        $logData['flag_multi_session'] > 3 ||
                        $logData['flag_empty_referer'] > 3
                    ) {
                        $text_warning = '<span class="so-text-danger">
                            <i class="fas fa-exclamation-triangle"></i>
                            </span>';
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
            'responsive': true,
            'pageLength': 25,
            'initComplete': function(settings, json) {
                $('#so-table-loading').hide();
                $('#so-table-container').fadeOut(800);
                $('#so-table-container').fadeIn(800);
            }
        });
    });

</script>
