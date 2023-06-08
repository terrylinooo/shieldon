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

$timezone = '';

?>

<div class="so-dashboard">
    <div class="so-datatables">
        <div class="so-datatable-heading">
            <?php _e('panel', 'overview_heading_data_circle', 'Data Circle'); ?> 
                <button type="button" class="btn-shieldon btn-only-icon" onclick="openResetModal(this)" 
                    data-id="reset-data-circle" 
                    data-title="<?php _e('panel', 'overview_reset_data_circle', 'Reset Data Circle'); ?>"
                >
                    <i class="fas fa-sync"></i>
                </button>
            <div class="heading-right">
                <ul>
                    <li>
                        <span>shieldon_rule_list</span>
                        <strong>
                            <?php echo count($rule_list); ?>
                            <?php _e('panel', 'overview_text_rows', 'rows'); ?><br />
                        </strong>
                    </li>
                    <li>
                        <span>shieldon_filter_logs</span>
                        <strong>
                            <?php echo count($ip_log_list); ?>
                            <?php _e('panel', 'overview_text_rows', 'rows'); ?>
                        </strong>
                    </li>
                    <li>
                        <span>shieldon_sessions</span>
                        <strong>
                            <?php echo count($session_list); ?>
                            <?php _e('panel', 'overview_text_rows', 'rows'); ?>
                        </strong>
                    </li>
                </ul>
            </div>
        </div>
        <br />
        <div class="row">
            <div class="col-sm-3">
                <div class="filter-status">
                    <div class="heading">
                        <?php _e('panel', 'overview_label_mysql', 'MySQL'); ?>
                    </div>
                    <div class="nums">
                        <?php echo $driver['mysql'] ?
                            '<i class="far fa-check-circle"></i>' :
                            '<i class="far fa-circle"></i>'; ?>
                    </div>
                    <div class="note">
                        <?php _e('panel', 'overview_note_sql_db', 'SQL database.'); ?>
                    </div>
                </div>
            </div>
            <div class="col-sm-3">
                <div class="filter-status">
                    <div class="heading">
                        <?php _e('panel', 'overview_label_redis', 'Redis'); ?>
                    </div>
                    <div class="nums">
                        <?php echo $driver['redis'] ?
                            '<i class="far fa-check-circle"></i>' :
                            '<i class="far fa-circle"></i>'; ?>
                    </div>
                    <div class="note">
                        <?php _e('panel', 'overview_note_memory_db', 'In-memory dadabase.'); ?>
                    </div>
                </div>
            </div>
            <div class="col-sm-3">
                <div class="filter-status">
                    <div class="heading"><?php _e('panel', 'overview_label_file', 'File'); ?></div>
                    <div class="nums">
                        <?php echo $driver['file'] ?
                            '<i class="far fa-check-circle"></i>' :
                            '<i class="far fa-circle"></i>'; ?>
                    </div>
                    <div class="note">
                        <?php _e('panel', 'overview_note_file_system', 'File system.'); ?>
                    </div>
                </div>
            </div>
            <div class="col-sm-3">
                <div class="filter-status">
                    <div class="heading">
                        <?php _e('panel', 'overview_label_sqlite', 'SQLite'); ?>
                    </div>
                    <div class="nums">
                        <?php echo $driver['sqlite'] ?
                            '<i class="far fa-check-circle"></i>' :
                            '<i class="far fa-circle"></i>'; ?>
                    </div>
                    <div class="note">
                        <?php _e('panel', 'overview_note_sql_db', 'SQL database.'); ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<div class="so-dashboard">
    <div class="so-datatables">
        <div class="so-datatable-heading">
            <?php _e('panel', 'overview_heading_filters', 'Filters'); ?>
        </div>
        <br />
        <div class="row">
            <div class="col-sm-3">
                <div class="filter-status">
                    <div class="heading">
                        <?php _e('panel', 'overview_label_cookie', 'Cookie'); ?>
                    </div>
                    <div class="nums">
                        <?php echo $filters['cookie'] ?
                            '<i class="far fa-play-circle"></i>' :
                            '<i class="far fa-stop-circle"></i>'; ?>
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
                </div>
            </div>
            <div class="col-sm-3">
                <div class="filter-status">
                    <div class="heading">
                        <?php _e('panel', 'overview_label_session', 'Session'); ?>
                    </div>
                    <div class="nums">
                        <?php echo $filters['session'] ?
                            '<i class="far fa-play-circle"></i>' :
                            '<i class="far fa-stop-circle"></i>'; ?>
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
                </div>
            </div>
            <div class="col-sm-3">
                <div class="filter-status">
                    <div class="heading">
                        <?php _e('panel', 'overview_label_frequency', 'Frequency'); ?>
                    </div>
                    <div class="nums">
                        <?php echo $filters['frequency'] ?
                            '<i class="far fa-play-circle"></i>' :
                            '<i class="far fa-stop-circle"></i>'; ?>
                    </div>
                    <div class="note">
                        <?php _e('panel', 'overview_note_frequency', 'Check how often a visitor views pages.'); ?>
                    </div>
                </div>
            </div>
            <div class="col-sm-3">
                <div class="filter-status">
                    <div class="heading">
                        <?php _e('panel', 'overview_label_referer', 'Referrer'); ?>
                    </div>
                    <div class="nums">
                        <?php echo $filters['referer'] ?
                            '<i class="far fa-play-circle"></i>' :
                            '<i class="far fa-stop-circle"></i>'; ?>
                    </div>
                    <div class="note">
                        <?php _e('panel', 'overview_note_referer', 'Check HTTP referrer information.'); ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<div class="so-dashboard">
    <div class="so-datatables">
        <div class="so-datatable-heading">
            <?php _e('panel', 'overview_heading_components', 'Components'); ?>
        </div>
        <br />
        <div class="row">
            <div class="col-sm-3">
                <div class="filter-status">
                    <div class="heading">
                        <?php _e('panel', 'overview_label_ip', 'IP'); ?>
                    </div>
                    <div class="nums">
                        <?php echo $components['Ip'] ?
                            '<i class="far fa-play-circle"></i>' :
                            '<i class="far fa-stop-circle"></i>'; ?>
                    </div>
                    <div class="note">
                        <?php _e('panel', 'overview_note_ip', 'Advanced IP address mangement.'); ?>
                    </div>
                </div>
            </div>
            <div class="col-sm-3">
                <div class="filter-status">
                    <div class="heading">
                        <?php _e('panel', 'overview_label_trustedbot', 'Trusted Bot'); ?>
                    </div>
                    <div class="nums">
                        <?php echo $components['TrustedBot'] ?
                            '<i class="far fa-play-circle"></i>' :
                            '<i class="far fa-stop-circle"></i>'; ?>
                    </div>
                    <div class="note">
                        <?php
                        _e(
                            'panel',
                            'overview_note_trustedbot',
                            'Allow popular search engines to crawl your website.'
                        );
                        ?>
                    </div>
                </div>
            </div>
            <div class="col-sm-3">
                <div class="filter-status">
                    <div class="heading">
                        <?php _e('panel', 'overview_label_header', 'Header'); ?>
                    </div>
                    <div class="nums">
                        <?php echo $components['Header'] ?
                            '<i class="far fa-play-circle"></i>' :
                            '<i class="far fa-stop-circle"></i>'; ?>
                    </div>
                    <div class="note">
                        <?php _e('panel', 'overview_note_header', 'Analyze visitors header information.'); ?>
                    </div>
                </div>
            </div>
            <div class="col-sm-3">
                <div class="filter-status">
                    <div class="heading">
                        <?php _e('panel', 'overview_label_rdns', 'RDNS'); ?>
                    </div>
                    <div class="nums">
                        <?php echo $components['Rdns'] ?
                            '<i class="far fa-play-circle"></i>' :
                            '<i class="far fa-stop-circle"></i>'; ?>
                    </div>
                    <div class="note">
                        <?php _e('panel', 'overview_note_rdns', 'Identify visitor IP resolved hostname (RDNS).'); ?>
                    </div>
                </div>
            </div>
            <div class="col-sm-3">
                <div class="filter-status">
                    <div class="heading">
                        <?php _e('panel', 'overview_label_useragent', 'User Agent'); ?>
                    </div>
                    <div class="nums">
                        <?php echo $components['UserAgent'] ?
                            '<i class="far fa-play-circle"></i>' :
                            '<i class="far fa-stop-circle"></i>'; ?>
                    </div>
                    <div class="note">
                        <?php
                        _e(
                            'panel',
                            'overview_note_useragent',
                            'Analysis user-agent information from visitors.'
                        );
                        ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<div class="so-dashboard">
    <div class="so-datatables">
        <div class="so-datatable-heading">
            <?php _e('panel', 'overview_heading_logger', 'Logger'); ?> 
            <button type="button"
                class="btn-shieldon btn-only-icon"
                onclick="openResetModal(this)" 
                data-id="reset-action-logs" 
                data-title="<?php _e('panel', 'overview_reset_action_logs', 'Reset Action Logs'); ?>"
            >
                <i class="fas fa-sync"></i>
            </button>
            <div class="heading-right">
                <ul>
                    <li>
                        <span><?php _e('panel', 'overview_text_since', 'since'); ?></span>
                        <strong><?php echo $logger_started_working_date; ?></strong>
                    </li>
                    <li>
                        <span><?php _e('panel', 'overview_text_days', 'days'); ?></span>
                        <strong><?php echo $logger_work_days; ?></strong>
                    </li>
                    <li>
                        <span><?php _e('panel', 'overview_text_size', 'size'); ?></span>
                        <strong><?php echo $logger_total_size; ?></strong>
                    </li>
                </ul>
            </div>
        </div>
        <br />
        <div class="row">
            <div class="col-sm-3">
                <div class="filter-status">
                    <div class="heading">
                        <?php _e('panel', 'overview_label_action_logger', 'Action Logger'); ?>
                    </div>
                    <div class="nums">
                        <?php echo $data['action_logger'] ?
                            '<i class="far fa-play-circle"></i>' :
                            '<i class="far fa-stop-circle"></i>'; ?>
                    </div>
                    <div class="note">
                        <?php _e('panel', 'overview_note_action_logger', 'Record every visitor’s behavior.'); ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<div class="so-dashboard">
    <div class="so-datatables">
        <div class="so-datatable-heading">
            <?php _e('panel', 'overview_heading_captcha', 'Captcha Modules'); ?>
        </div>
        <br />
        <div class="row">
            <div class="col-sm-3">
                <div class="filter-status">
                    <div class="heading">
                        <?php _e('panel', 'overview_label_recaptcha', 'reCAPTCHA'); ?>
                    </div>
                    <div class="nums">
                        <?php echo $captcha['recaptcha'] ?
                            '<i class="far fa-play-circle"></i>' :
                            '<i class="far fa-stop-circle"></i>'; ?>
                    </div>
                    <div class="note">
                        <?php _e('panel', 'overview_note_recaptcha', 'Provided by Google.'); ?>
                    </div>
                </div>
            </div>
            <div class="col-sm-3">
                <div class="filter-status">
                    <div class="heading">
                        <?php _e('panel', 'overview_label_image_captcha', 'Image Captcha'); ?>
                    </div>
                    <div class="nums">
                        <?php echo $captcha['imagecaptcha'] ?
                            '<i class="far fa-play-circle"></i>' :
                            '<i class="far fa-stop-circle"></i>'; ?>
                    </div>
                    <div class="note">
                        <?php _e('panel', 'overview_note_image_captcha', 'A simple text-in-image Captcha.'); ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<div class="so-dashboard">
    <div class="so-datatables">
        <div class="so-datatable-heading">
            <?php _e('panel', 'overview_heading_messenger', 'Messenger Modules'); ?>
        </div>
        <br />
        <div class="row">
            <?php foreach ($messengers as $k => $v) : ?>
            <div class="col-sm-3">
                <div class="filter-status">
                    <div class="heading"><?php _e('panel', 'overview_label_' . $k, ''); ?></div>
                    <div class="nums">
                        <?php echo $messengers[$k] ?
                            '<i class="far fa-play-circle"></i>' :
                            '<i class="far fa-stop-circle"></i>'; ?>
                    </div>
                    <div class="note">
                        <?php _e('panel', 'overview_note_' . $k, ''); ?>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>

<div id="info-modal" class="modal fade" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"></h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body"></div>
            <div class="modal-footer justify-content-between">
                <button type="button" class="btn btn-primary" id="btn-document-link" data-url="">
                    <i class="far fa-file-code"></i> <?php _e('panel', 'overview_btn_document', 'Document'); ?>
                </button>
                <button type="button" class="btn btn-secondary" data-dismiss="modal">
                    <?php _e('panel', 'overview_btn_close', 'Close'); ?>
                </button>
            </div>
        </div>
    </div>
</div>

<div id="reset-modal" class="modal fade" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <form method="post" id="form-reset-data">
        <?php echo $this->fieldCsrf(); ?>
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"></h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body"></div>
            <div class="modal-footer">
                <button type="submit" class="btn btn-danger">
                    <?php _e('panel', 'auth_btn_submit', 'Submit'); ?>
                </button>
                <button type="button" class="btn btn-secondary" data-dismiss="modal">
                    <?php _e('panel', 'overview_btn_close', 'Close'); ?>
                </button>
            </div>
        </div>
        </form>
    </div>
</div>

<script type="text/template" id="reset-data-circle">
    <p>
        <?php _e('panel', 'overview_text_reset_data_circle_1', 'Would you like to reset current data circle?'); ?>
    </p>
    <table class="table table-bordered">
        <thead class="thead-dark">
            <th><?php _e('panel', 'overview_thread_table', 'Table'); ?></th>
            <th><?php _e('panel', 'overview_thread_rows', 'Rows'); ?></th>
        </thead>
        <tr>
            <td>shieldon_rule_list</td>
            <td><?php echo count($rule_list); ?></td>
        <tr>
        <tr>
            <td>shieldon_filter_logs</td>
            <td><?php echo count($ip_log_list); ?></td>
        <tr>
        <tr>
            <td>shieldon_sessions</td>
            <td><?php echo count($session_list); ?></td>
        <tr>
    </table>
    <input type="hidden" name="action_type" value="reset_data_circle">
    <p>
        <?php
        _e(
            'panel',
            'overview_text_reset_data_circle_2',
            'Performing this action will remove all data from current data circle and rebuild data tables.'
        );
        ?>
    </p>
</script>

<script type="text/template" id="reset-action-logs">
    <p><?php _e('panel', 'overview_text_reset_action_logs', 'Would you like to remove all action logs?'); ?></p>
    <table class="table table-bordered">
        <tr>
            <td><?php _e('panel', 'overview_text_since', 'since'); ?></td>
            <td><?php echo $logger_started_working_date; ?></td>
        <tr>
        <tr>
            <td><?php _e('panel', 'overview_text_days', 'days'); ?></td>
            <td><?php echo $logger_work_days; ?></td>
        <tr>
        <tr>
            <td><?php _e('panel', 'overview_text_size', 'size'); ?></td>
            <td><?php echo $logger_total_size; ?></td>
        <tr>
    </table>
    <input type="hidden" name="action_type" value="reset_action_logs">
</script>

<script>

    $(function() {

        $('#btn-document-link').click(function() {
            let url = $(this).attr('data-url');

            if (url !== '') {
                window.open(url, '_blank');
            }
        });

        $('#form-reset-data').submit(function() {
            freezeUI();
            setTimeout(function() {
                $('#reset-modal').modal('hide');
            }, 300);
        });
    });

    var openInfoModal = function(obj) {
        let id = $(obj).attr('data-id');
        let title = $(obj).attr('data-title');
        let document = $(obj).attr('data-document');

        $('#info-modal').find('.modal-title').html(title);
        $('#info-modal').find('.modal-body').html($('#' + id).html());
        $('#btn-document-link').attr('data-url', document);
        $('#info-modal').modal();
    };

    var openResetModal = function(obj) {
        let id = $(obj).attr('data-id');
        let title = $(obj).attr('data-title');

        $('#reset-modal').find('.modal-title').html(title);
        $('#reset-modal').find('.modal-body').html($('#' + id).html());
        $('#reset-modal').modal();
    };

</script>