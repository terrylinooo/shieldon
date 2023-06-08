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
    <?php if (!empty($period_data)) : ?>
    <div class="so-flex">
        <div class="so-board">
            <div class="board-field left">
                <div id="chart-1"></div>
            </div>
            <div class="board-field right">
                <div class="heading">
                    <?php _e('panel', 'log_heading_captchas', 'CAPTCHAs'); ?>
                </div>
                <div class="nums">
                    <?php echo number_format($period_data['captcha_count']); ?>
                </div>
                <div class="note">
                    <?php _e('panel', 'log_note_captcha_last_7_days', 'CAPTCHA statistic last 7 days.'); ?>
                </div>
            </div>
        </div>
        <div class="so-board">
            <div class="board-field left">
                <div id="chart-2"></div>
            </div>
            <div class="board-field right">
                <div class="heading">
                    <?php _e('panel', 'log_heading_pageviews', 'Pageviews'); ?>
                </div>
                <div class="nums">
                    <?php echo number_format($period_data['pageview_count']); ?>
                </div>
                <div class="note">
                    <?php _e('panel', 'log_note_pageview_last_7_days', 'Total pageviews last 7 days.'); ?>
                </div>
            </div>
        </div>
        <div class="so-board area-chart-container">
            <div id="chart-3"></div>
        </div>
    </div>
    <?php endif; ?>
    <div class="so-tabs">
        <ul>
            <li>
                <a href="<?php echo $this->url('report/actionLog'); ?>?tab=today">
                    <?php _e('panel', 'log_label_today', 'Today'); ?>
                </a>
            </li>
            <li>
                <a href="<?php echo $this->url('report/actionLog'); ?>?tab=yesterday">
                    <?php _e('panel', 'log_label_yesterday', 'Yesterday'); ?>
                </a>
            </li>
            <li class="is-active">
                <a href="<?php echo $this->url('report/actionLog'); ?>?tab=past_seven_days">
                    <?php _e('panel', 'log_label_last_7_days', 'Last 7 days'); ?>
                </a>
            </li>
            <li>
                <a href="<?php echo $this->url('report/actionLog'); ?>?tab=this_month">
                    <?php _e('panel', 'log_label_this_month', 'This month'); ?>
                </a>
            </li>
            <li>
                <a href="<?php echo $this->url('report/actionLog'); ?>?tab=last_month">
                    <?php _e('panel', 'log_label_last_month', 'Last month'); ?>
                </a>
            </li>
        </ul>
    </div>

    <?php if ($page_availability) : ?>
        <div id="so-table-loading" class="so-datatables">
            <div class="lds-css ng-scope">
                <div class="lds-ripple">
                    <div></div>
                    <div></div>
                </div>
            </div>
        </div>
    <?php else : ?>
        <div class="alert alert-danger">
            <?php
            _e(
                'panel',
                'log_msg_no_logger',
                'Sorry, you have to implement ActionLogger to use this function.'
            );
            ?>
        </div>
    <?php endif; ?>

    <div id="so-table-container" class="so-datatables" style="display: none;">
        <table id="so-datalog" class="cell-border compact stripe responsive" cellspacing="0" width="100%">
            <thead>
                <tr>
                    <th rowspan="2"><?php _e('panel', 'ipma_label_ip', 'IP'); ?></th>
                    <th rowspan="2"><?php _e('panel', 'log_label_session', 'Sessions'); ?></th>
                    <th rowspan="2"><?php _e('panel', 'log_label_pageviews', 'Pageviews'); ?></th>
                    <th colspan="3" class="merged-field"><?php _e('panel', 'log_label_captcha', 'CAPTCHA'); ?></th>
                    <th rowspan="2"><?php _e('panel', 'log_label_in_blacklist', 'In blacklist'); ?></th>
                    <th rowspan="2"><?php _e('panel', 'log_label_in_queue', 'In queue'); ?></th>
                </tr>
                <tr>
                    <th><?php _e('panel', 'log_label_solved', 'solved'); ?></th>
                    <th><?php _e('panel', 'log_label_failed', 'failed'); ?></th>
                    <th><?php _e('panel', 'log_label_displays', 'displays'); ?></th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($ip_details)) : ?>
                    <?php foreach ($ip_details as $ip => $ipInfo) : ?>
                    <tr>
                        <td>
                            <?php if ($this->mode === 'demo') : ?>
                                <?php $ip = mask_string($ip); ?>
                            <?php endif; ?>
                            <?php echo $ip; ?>
                        </td>
                        <td><?php echo count($ipInfo['session_id']); ?></td>
                        <td><?php echo $ipInfo['pageview_count']; ?></td>
                        <td><?php echo $ipInfo['captcha_success_count']; ?></td>
                        <td><?php echo $ipInfo['captcha_failure_count']; ?></td>
                        <td><?php echo $ipInfo['captcha_count']; ?></td>
                        <td><?php echo $ipInfo['blacklist_count']; ?></td>
                        <td><?php echo $ipInfo['session_limit_count']; ?></td>
                    </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>   
        </table>
    </div>
    <div class="so-timezone">
        <?php if (!empty($last_cached_time)) : ?>
            <?php _e('panel', 'log_label_cache_time', 'Report generated time'); ?>:
            <strong class="text-info">
                <?php echo $last_cached_time; ?>
            </strong>
            &nbsp;&nbsp;&nbsp;&nbsp; 
        <?php endif; ?>
        <?php _e('panel', 'log_label_timezone', 'Timezone'); ?>: <?php echo date_default_timezone_get(); ?>
    </div>
</div>

<?php

if (!empty($period_data)) {
    $data['period_data'] = $period_data;
    $this->loadViewPart('panel/js/chart', $data);
}
