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
use function Shieldon\Firewall\__;
use function Shieldon\Firewall\mask_string;

$timezone = '';

?>

<div class="so-dashboard">
    <div class="so-flex">
        <div class="so-board">
            <div class="board-field left icon icon-1">
                <i class="fas fa-clipboard-check"></i>
            </div>
            <div class="board-field right">
                <div class="heading">
                    <?php _e('panel', 'table_heading_limit', 'Limit'); ?>
                </div>
                <div class="nums">
                    <?php echo $session_limit_count; ?>
                </div>
                <div class="note">
                    <?php _e('panel', 'table_note_limit', 'Online session limit.'); ?>
                </div>
            </div>
        </div>

        <div class="so-board">
            <div class="board-field left icon icon-2">
                <i class="far fa-clock"></i>
            </div>
            <div class="board-field right">
                <div class="heading">
                    <?php _e('panel', 'table_heading_period', 'Period'); ?>
                </div>
                <div class="nums">
                    <?php echo number_format($session_limit_period); ?>
                </div>
                <div class="note">
                    <?php _e('panel', 'table_note_period', 'Keep-alive period (in minutes)'); ?>
                </div>
            </div>
        </div>
        <div class="so-board">
            <div class="board-field left icon icon-3">
                <i class="fas fa-street-view"></i>
            </div>
            <div class="board-field right">
                <div class="heading"><?php _e('panel', 'table_heading_online', 'Online'); ?></div>
                <div class="nums"><?php echo number_format($online_count); ?></div>
                <div class="note"><?php _e('panel', 'table_note_online', 'Online user count.'); ?></div>
            </div>
        </div>
    </div>
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
            <?php _e('panel', 'table_heading_session', 'Session Table'); ?>
        </div>
        <div class="so-datatable-description">
            <?php
            _e(
                'panel',
                'table_description_session_1',
                'Real-time logs for <strong>Online Session Controll</strong>.'
            );
            ?>
            <br />
            <?php
            _e(
                'panel',
                'table_description_session_2',
                'Notice: this only works when enabled.'
            );
            ?>
        </div>
        <table id="so-datalog" class="cell-border compact stripe responsive" cellspacing="0" width="100%">
            <thead>
                <tr>
                    <th><?php _e('panel', 'table_label_priority', 'Priority'); ?></th>
                    <th><?php _e('panel', 'table_label_status', 'Status'); ?></th>
                    <th><?php _e('panel', 'table_label_session_id', 'Session ID'); ?></th>
                    <th><?php _e('panel', 'overview_label_ip', 'IP'); ?></th>
                    <th><?php _e('panel', 'table_label_time', 'Time'); ?></th>
                    <th><?php _e('panel', 'table_label_remain_seconds', 'Remain seconds'); ?></th>
                </tr>
            </thead>
            <tbody>

                <?php $i = 1; ?>
                <?php foreach ($session_list as $key => $sessionInfo) : ?>
                    <?php

                    $remainsTime = $expires - (time() - $sessionInfo['time']);

                    if ($remainsTime < 1) {
                        $remainsTime = 0;
                    }

                    if ($i < $session_limit_count) {
                        $satusName = __('panel', 'table_text_allowable', 'Allowable');
                        if ($remainsTime < 1) {
                            $satusName = __('panel', 'table_text_expired', 'Expired');
                        }
                    } else {
                        $satusName = __('panel', 'table_text_waiting', 'Waiting');
                    }

                    ?>
                    <tr>
                        <td title="Key: <?php echo $key ?>"><?php echo $i; ?></td>
                        <td><?php echo $satusName; ?></td>
                        <td><?php echo $sessionInfo['id']; ?></td>
                        <td>
                            <?php if ($this->mode === 'demo') : ?>
                                <?php $sessionInfo['ip'] = mask_string($sessionInfo['ip']); ?>
                            <?php endif; ?>
                            <?php echo $sessionInfo['ip']; ?>
                        </td>
                        <td><?php echo date('Y-m-d H:i:s', $sessionInfo['time']); ?></td>
                        <th><?php echo $remainsTime; ?></th>
                    </tr>
                    <?php $i++; ?>
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