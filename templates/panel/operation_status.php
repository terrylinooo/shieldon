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

$componentList = [
    'ip',
    'trustedbot',
    'header',
    'rdns',
    'useragent',
    'frequency',
    'referer',
    'session',
    'cookie',
];

?>

<div class="so-dashboard opertaion-table">
    <div class="so-datatables">
        <div class="so-datatable-heading">
            <?php _e('panel', 'overview_heading_filters', 'Filters'); ?>
        </div>
        <br />
        <div class="row">
            <div class="col-sm-3">
                <div class="filter-status">
                    <div class="heading"><?php _e('panel', 'overview_label_cookie', 'Cookie'); ?></div>
                    <div class="nums">
                        <?php if (!empty($filter_cookie)) : ?>
                            <a href="#" onclick="displayLogs('frequency');"><?php echo $filter_cookie; ?></a>
                        <?php else : ?>
                            <?php echo $filter_cookie; ?>
                        <?php endif; ?>
                    </div>
                    <div class="note">
                        <?php
                        _e(
                            'panel',
                            'overview_note_cookie',
                            'Check whether visitors can create cookies with JavaScript'
                        );
                        ?>
                    </div>
                    <button class="note-code">
                        <?php

                            echo $filters['cookie'] ? '<i class="fas fa-play-circle"></i>' :
                                '<i class="fas fa-stop-circle"></i>';
                        ?>
                    </button>
                </div>
            </div>
            <div class="col-sm-3">
                <div class="filter-status">
                    <div class="heading"><?php _e('panel', 'overview_label_session', 'Session'); ?></div>
                    <div class="nums">
                        <?php if (!empty($filter_session)) : ?>
                            <a href="#" onclick="displayLogs('session');"><?php echo $filter_session; ?></a>
                        <?php else : ?>
                            <?php echo $filter_session; ?>
                        <?php endif; ?>
                    </div>
                    <div class="note">
                        <?php
                        _e(
                            'panel',
                            'overview_note_session',
                            'Detect whether multiple sessions were created by the same visitor.'
                        );
                        ?>
                    </div>
                    <button class="note-code">
                        <?php echo $filters['session'] ?
                            '<i class="fas fa-play-circle"></i>' :
                            '<i class="fas fa-stop-circle"></i>'; ?>
                    </button>
                </div>
            </div>
            <div class="col-sm-3">
                <div class="filter-status">
                    <div class="heading"><?php _e('panel', 'overview_label_frequency', 'Frequency'); ?></div>
                    <div class="nums">
                        <?php if (!empty($filter_frequency)) : ?>
                            <a href="#" onclick="displayLogs('frequency');"><?php echo $filter_frequency; ?></a>
                        <?php else : ?>
                            <?php echo $filter_frequency; ?>
                        <?php endif; ?>
                    </div>
                    <div class="note">
                        <?php _e('panel', 'overview_note_frequency', 'Check how often a visitor views pages.'); ?>
                    </div>
                    <button class="note-code">
                        <?php echo $filters['frequency'] ?
                            '<i class="fas fa-play-circle"></i>' :
                            '<i class="fas fa-stop-circle"></i>'; ?>
                    </button>
                </div>
            </div>
            <div class="col-sm-3">
                <div class="filter-status">
                    <div class="heading"><?php _e('panel', 'overview_label_referer', 'Referrer'); ?></div>
                    <div class="nums">
                        <?php if (!empty($filter_referer)) : ?>
                            <a href="#" onclick="displayLogs('referer');"><?php echo $filter_referer; ?></a>
                        <?php else : ?>
                            <?php echo $filter_referer; ?>
                        <?php endif; ?>
                    </div>
                    <div class="note">
                        <?php _e('panel', 'overview_note_referer', 'Check HTTP referrer information.'); ?>
                    </div>
                    <button class="note-code">
                        <?php echo $filters['referer'] ?
                            '<i class="fas fa-play-circle"></i>' :
                            '<i class="fas fa-stop-circle"></i>';
                        ?>
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
<div class="so-dashboard opertaion-table">
    <div class="so-datatables">
        <div class="so-datatable-heading">
            <?php _e('panel', 'overview_heading_components', 'Components'); ?>
        </div>
        <br />
        <div class="row">
            <div class="col-sm-3">
                <div class="filter-status">
                    <div class="heading"><?php _e('panel', 'overview_label_ip', 'IP'); ?></div>
                    <div class="nums">
                        <?php if (!empty($component_ip)) : ?>
                            <a href="#" onclick="displayLogs('ip');"><?php echo $component_ip; ?></a>
                        <?php else : ?>
                            <?php echo $component_ip; ?>
                        <?php endif; ?>
                    </div>
                    <div class="note"><?php _e('panel', 'operation_note_ip', 'Advanced IP address mangement.'); ?></div>
                    <button class="note-code">
                        <?php echo $components['Ip'] ?
                            '<i class="fas fa-play-circle"></i>' :
                            '<i class="fas fa-stop-circle"></i>'; ?>
                    </button>
                </div>
            </div>
            <div class="col-sm-3">
                <div class="filter-status">
                    <div class="heading"><?php _e('panel', 'overview_label_trustedbot', 'Trusted Bot'); ?></div>
                    <div class="nums">
                        <?php if (!empty($component_trustedbot)) : ?>
                            <a href="#" onclick="displayLogs('trustedbot');"><?php echo $component_trustedbot; ?></a>
                        <?php else : ?>
                            <?php echo $component_trustedbot; ?>
                        <?php endif; ?>
                    </div>
                    <div class="note">
                        <?php
                        _e(
                            'panel',
                            'operation_note_trustedbot',
                            'Allow popular search engines to crawl your website.'
                        );
                        ?>
                    </div>
                    <button class="note-code">
                        <?php echo $components['TrustedBot'] ?
                            '<i class="fas fa-play-circle"></i>' :
                            '<i class="fas fa-stop-circle"></i>'; ?>
                    </button>
                </div>
            </div>
            <div class="col-sm-3">
                <div class="filter-status">
                    <div class="heading"><?php _e('panel', 'overview_label_header', 'Header'); ?></div>
                    <div class="nums">
                        <?php if (!empty($component_header)) : ?>
                            <a href="#" onclick="displayLogs('header');"><?php echo $component_header; ?></a>
                        <?php else : ?>
                            <?php echo $component_header; ?>
                        <?php endif; ?>
                    </div>
                    <div class="note">
                        <?php
                        _e(
                            'panel',
                            'operation_note_header',
                            'Analyze visitors header information.'
                        );
                        ?>
                    </div>
                    <button class="note-code">
                        <?php echo $components['Header'] ?
                            '<i class="fas fa-play-circle"></i>' :
                            '<i class="fas fa-stop-circle"></i>'; ?>
                    </button>
                </div>
            </div>
            <div class="col-sm-3">
                <div class="filter-status">
                    <div class="heading"><?php _e('panel', 'overview_label_rdns', 'RDNS'); ?></div>
                    <div class="nums">
                        <?php if (!empty($component_rdns)) : ?>
                            <a href="#" onclick="displayLogs('rdns');"><?php echo $component_rdns; ?></a>
                        <?php else : ?>
                            <?php echo $component_rdns; ?>
                        <?php endif; ?>
                    </div>
                    <div class="note">
                        <?php _e('panel', 'operation_note_rdns', 'Identify visitor IP resolved hostname (RDNS).'); ?>
                    </div>
                    <button class="note-code">
                        <?php echo $components['Rdns'] ?
                            '<i class="fas fa-play-circle"></i>' :
                            '<i class="fas fa-stop-circle"></i>'; ?>
                    </button>
                </div>
            </div>
            <div class="col-sm-3">
                <div class="filter-status">
                    <div class="heading"><?php _e('panel', 'overview_label_useragent', 'User Agent'); ?></div>
                    <div class="nums">
                        <?php if (!empty($component_useragent)) : ?>
                            <a href="#" onclick="displayLogs('useragent');"><?php echo $component_useragent; ?></a>
                        <?php else : ?>
                            <?php echo $component_useragent; ?>
                        <?php endif; ?>
                    </div>
                    <div class="note">
                        <?php
                        _e(
                            'panel',
                            'operation_note_useragent',
                            'Analysis user-agent information from visitors.'
                        );
                        ?>
                    </div>
                    <button class="note-code">
                        <?php echo $components['UserAgent'] ?
                            '<i class="fas fa-play-circle"></i>' :
                            '<i class="fas fa-stop-circle"></i>'; ?>
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<?php foreach ($componentList as $i) : ?>
    <div id="table-<?php echo $i; ?>" class="so-dashboard" style="display: none;">
        <div class="so-datatables">
            <div class="so-datatable-heading">
                <?php _e('panel', 'overview_label_' . $i, ''); ?>
                <button type="button"
                    class="btn-shieldon btn-only-icon"
                    onclick="closeDisplayLogs('<?php echo $i; ?>')">
                    <i class="fas fa-undo-alt"></i>
                </button>
            </div>
            <table id="so-datalog-<?php echo $i; ?>"
                class="so-datalog cell-border compact stripe responsive"
                cellspacing="0"
                width="100%"
            >
                <thead>
                    <tr>
                        <th><?php _e('panel', 'overview_label_ip', 'IP'); ?></th>
                        <th><?php _e('panel', 'table_label_resolved_hostname', 'Resolved hostname'); ?></th>
                        <th><?php _e('panel', 'table_label_type', 'Type'); ?></th>
                        <th><?php _e('panel', 'table_label_reason', 'Reason'); ?></th>
                        <th><?php _e('panel', 'table_label_time', 'Time'); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($rule_list[$i] as $ipInfo) : ?>
                    <tr>
                        <td>
                            <?php if ($this->mode === 'demo') : ?>
                                <?php $ipInfo['log_ip'] = mask_string($ipInfo['log_ip']); ?>
                            <?php endif; ?>
                            <?php echo $ipInfo['log_ip']; ?>
                        </td>
                        <td>
                            <?php echo $ipInfo['ip_resolve']; ?>
                        </td>
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
                        <td><?php echo date('Y-m-d H:i:s', $ipInfo['time']); ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>   
            </table>
        </div>
    </div>
<?php endforeach; ?>

<script>

    $(function() {

        $('.so-datalog').DataTable({
            'responsive': true,
            'pageLength': 25,
            'initComplete': function(settings, json) {
                $('#so-table-loading').hide();
                $('#so-table-container').fadeOut(800);
                $('#so-table-container').fadeIn(800);
            }
        });
    });

    function displayLogs(type) {
        $('#table-' + type).removeAttr('style');
        $('.opertaion-table').hide();
    }

    function closeDisplayLogs(type) {
        $('#table-' + type).hide();
        $('.opertaion-table').show();
    }

</script>