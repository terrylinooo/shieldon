<?php
/*
 * This file is part of the Shieldon package.
 *
 * (c) Terry L. <contact@terryl.in>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
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
                    <li><span>shieldon_rule_list</span> <strong><?php echo count($rule_list); ?> <?php _e('panel', 'overview_text_rows', 'rows'); ?><br /></strong></li>
                    <li><span>shieldon_filter_logs</span> <strong><?php echo count($ip_log_list); ?> <?php _e('panel', 'overview_text_rows', 'rows'); ?></strong></li>
                    <li><span>shieldon_sessions</span> <strong><?php echo count($session_list); ?> <?php _e('panel', 'overview_text_rows', 'rows'); ?></strong></li>
                </ul>
            </div>
        </div>
        <br />
        <div class="row">
            <div class="col-sm-3">
                <div class="filter-status">
                    <div class="heading"><?php _e('panel', 'overview_label_mysql', 'MySQL'); ?></div>
                    <div class="nums">
                        <?php echo $driver['mysql'] ? '<i class="far fa-check-circle"></i>' : '<i class="far fa-circle"></i>'; ?>
                    </div>
                    <div class="note"><?php _e('panel', 'overview_note_sql_db', 'SQL database.'); ?></div>
                    <button class="note-code" onclick="openInfoModal(this)" 
                        data-document="https://shield-on-php.github.io/en/driver/mysql.html" 
                        data-id="driver-mysql" 
                        data-title="MySQL">
                        <i class="fas fa-code"></i>
                    </button>
                </div>
            </div>
            <div class="col-sm-3">
                <div class="filter-status">
                    <div class="heading"><?php _e('panel', 'overview_label_redis', 'Redis'); ?></div>
                    <div class="nums">
                        <?php echo $driver['redis'] ? '<i class="far fa-check-circle"></i>' : '<i class="far fa-circle"></i>'; ?>
                    </div>
                    <div class="note"><?php _e('panel', 'overview_note_memory_db', 'In-memory dadabase.'); ?></div>
                    <button class="note-code" onclick="openInfoModal(this)" 
                        data-document="https://shield-on-php.github.io/en/driver/redis.html" 
                        data-id="driver-redis" 
                        data-title="Redis">
                        <i class="fas fa-code"></i>
                    </button>
                </div>
            </div>
            <div class="col-sm-3">
                <div class="filter-status">
                    <div class="heading"><?php _e('panel', 'overview_label_file', 'File'); ?></div>
                    <div class="nums">
                        <?php echo $driver['file'] ? '<i class="far fa-check-circle"></i>' : '<i class="far fa-circle"></i>'; ?>
                    </div>
                    <div class="note"><?php _e('panel', 'overview_note_file_system', 'File system.'); ?></div>
                    <button class="note-code" onclick="openInfoModal(this)" 
                        data-document="https://shield-on-php.github.io/en/driver/file.html" 
                        data-id="driver-file" 
                        data-title="File System">
                        <i class="fas fa-code"></i>
                    </button>
                </div>
            </div>
            <div class="col-sm-3">
                <div class="filter-status">
                    <div class="heading"><?php _e('panel', 'overview_label_sqlite', 'SQLite'); ?></div>
                    <div class="nums">
                        <?php echo $driver['sqlite'] ? '<i class="far fa-check-circle"></i>' : '<i class="far fa-circle"></i>'; ?>
                    </div>
                    <div class="note"><?php _e('panel', 'overview_note_sql_db', 'SQL database.'); ?></div>
                    <button class="note-code" onclick="openInfoModal(this)" 
                        data-document="https://shield-on-php.github.io/en/driver/sqlite.html" 
                        data-id="driver-sqlite" 
                        data-title="SQLite">
                        <i class="fas fa-code"></i>
                    </button>
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
                    <div class="heading"><?php _e('panel', 'overview_label_cookie', 'Cookie'); ?></div>
                    <div class="nums">
                        <?php echo $filters['cookie'] ? '<i class="far fa-play-circle"></i>' : '<i class="far fa-stop-circle"></i>'; ?>
                    </div>
                    <div class="note"><?php _e('panel', 'overview_note_cookie', 'Check whether visitors can create cookie by JavaScript.'); ?></div>
                    <button class="note-code" onclick="openInfoModal(this)" 
                        data-document="https://shield-on-php.github.io/en/api.html#setfilters" 
                        data-id="filters-cookie" 
                        data-title="Cookie filiter">
                        <i class="fas fa-code"></i>
                    </button>
                </div>
            </div>
            <div class="col-sm-3">
                <div class="filter-status">
                    <div class="heading"><?php _e('panel', 'overview_label_session', 'Session'); ?></div>
                    <div class="nums">
                        <?php echo $filters['session'] ? '<i class="far fa-play-circle"></i>' : '<i class="far fa-stop-circle"></i>'; ?>
                    </div>
                    <div class="note"><?php _e('panel', 'overview_note_session', 'Detect whether multiple sessions created by the same visitor.'); ?></div>
                    <button class="note-code" onclick="openInfoModal(this)" 
                        data-document="https://shield-on-php.github.io/en/api.html#setfilters" 
                        data-id="filters-session" 
                        data-title="Session filiter">
                        <i class="fas fa-code"></i>
                    </button>
                </div>
            </div>
            <div class="col-sm-3">
                <div class="filter-status">
                    <div class="heading"><?php _e('panel', 'overview_label_frequency', 'Frequency'); ?></div>
                    <div class="nums">
                        <?php echo $filters['frequency'] ? '<i class="far fa-play-circle"></i>' : '<i class="far fa-stop-circle"></i>'; ?>
                    </div>
                    <div class="note"><?php _e('panel', 'overview_note_frequency', 'Check how often does a visitor view the pages.'); ?></div>
                    <button class="note-code" onclick="openInfoModal(this)" 
                        data-document="https://shield-on-php.github.io/en/api.html#setfilters" 
                        data-id="filters-frequency" 
                        data-title="Frequency filiter">
                        <i class="fas fa-code"></i>
                    </button>
                </div>
            </div>
            <div class="col-sm-3">
                <div class="filter-status">
                    <div class="heading"><?php _e('panel', 'overview_label_referer', 'Referrer'); ?></div>
                    <div class="nums">
                        <?php echo $filters['referer'] ? '<i class="far fa-play-circle"></i>' : '<i class="far fa-stop-circle"></i>'; ?>
                    </div>
                    <div class="note"><?php _e('panel', 'overview_note_referer', 'Check HTTP referrer information.'); ?></div>
                    <button class="note-code" onclick="openInfoModal(this)" 
                        data-document="https://shield-on-php.github.io/en/api.html#setfilters" 
                        data-id="filters-referer" 
                        data-title="Referer filiter">
                        <i class="fas fa-code"></i>
                    </button>
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
                    <div class="heading"><?php _e('panel', 'overview_label_ip', 'IP'); ?></div>
                    <div class="nums">
                        <?php echo $components['Ip'] ? '<i class="far fa-play-circle"></i>' : '<i class="far fa-stop-circle"></i>'; ?>
                    </div>
                    <div class="note"><?php _e('panel', 'overview_note_ip', 'Advanced IP address mangement.'); ?></div>
                    <button class="note-code" onclick="openInfoModal(this)" 
                        data-document="https://shield-on-php.github.io/en/component/ip.html" 
                        data-id="components-ip" 
                        data-title="Component IP">
                        <i class="fas fa-code"></i>
                    </button>
                </div>
            </div>
            <div class="col-sm-3">
                <div class="filter-status">
                    <div class="heading"><?php _e('panel', 'overview_label_trustedbot', 'Trusted Bot'); ?></div>
                    <div class="nums">
                        <?php echo $components['TrustedBot'] ? '<i class="far fa-play-circle"></i>' : '<i class="far fa-stop-circle"></i>'; ?>
                    </div>
                    <div class="note"><?php _e('panel', 'overview_note_trustedbot', 'Allow popular search engines crawl your website.'); ?></div>
                    <button class="note-code" onclick="openInfoModal(this)" 
                        data-document="https://shield-on-php.github.io/en/component/trustedbot.html" 
                        data-id="components-trustedbot" 
                        data-title="Component Trusted-bot">
                        <i class="fas fa-code"></i>
                    </button>
                </div>
            </div>
            <div class="col-sm-3">
                <div class="filter-status">
                    <div class="heading"><?php _e('panel', 'overview_label_header', 'Header'); ?></div>
                    <div class="nums">
                        <?php echo $components['Header'] ? '<i class="far fa-play-circle"></i>' : '<i class="far fa-stop-circle"></i>'; ?>
                    </div>
                    <div class="note"><?php _e('panel', 'overview_note_header', 'Analyze header information from visitors.'); ?></div>
                    <button class="note-code" onclick="openInfoModal(this)" 
                        data-document="https://shield-on-php.github.io/en/component/header.html" 
                        data-id="components-header" 
                        data-title="Component Header">
                        <i class="fas fa-code"></i>
                    </button>
                </div>
            </div>
            <div class="col-sm-3">
                <div class="filter-status">
                    <div class="heading"><?php _e('panel', 'overview_label_rdns', 'RDNS'); ?></div>
                    <div class="nums">
                        <?php echo $components['Rdns'] ? '<i class="far fa-play-circle"></i>' : '<i class="far fa-stop-circle"></i>'; ?>
                    </div>
                    <div class="note"><?php _e('panel', 'overview_note_rdns', 'Identify IP resolved hostname (RDNS) from visitors.'); ?></div>
                    <button class="note-code" onclick="openInfoModal(this)" 
                        data-document="https://shield-on-php.github.io/en/component/rdns.html" 
                        data-id="components-rdns" 
                        data-title="Component RDNS">
                        <i class="fas fa-code"></i>
                    </button>
                </div>
            </div>
            <div class="col-sm-3">
                <div class="filter-status">
                    <div class="heading"><?php _e('panel', 'overview_label_useragent', 'User Agent'); ?></div>
                    <div class="nums">
                        <?php echo $components['UserAgent'] ? '<i class="far fa-play-circle"></i>' : '<i class="far fa-stop-circle"></i>'; ?>
                    </div>
                    <div class="note"><?php _e('panel', 'overview_note_useragent', 'Analysis user-agent information from visitors.'); ?></div>
                    <button class="note-code" onclick="openInfoModal(this)" 
                        data-document="https://shield-on-php.github.io/en/component/useragent.html" 
                        data-id="components-useragent" 
                        data-title="Component User-agent">
                        <i class="fas fa-code"></i>
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
<div class="so-dashboard">
    <div class="so-datatables">
        <div class="so-datatable-heading">
            <?php _e('panel', 'overview_heading_logger', 'Logger'); ?> 
            <button type="button" class="btn-shieldon btn-only-icon" onclick="openResetModal(this)" 
                    data-id="reset-action-logs" 
                    data-title="<?php _e('panel', 'overview_reset_action_logs', 'Reset Action Logs'); ?>"
                >
                <i class="fas fa-sync"></i>
            </button>
            <div class="heading-right">
                <ul>
                    <li><span><?php _e('panel', 'overview_text_since', 'since'); ?></span> <strong><?php echo $logger_started_working_date; ?></strong></li>
                    <li><span><?php _e('panel', 'overview_text_days', 'days'); ?></span> <strong><?php echo $logger_work_days; ?></strong></li>
                    <li><span><?php _e('panel', 'overview_text_size', 'size'); ?></span> <strong><?php echo $logger_total_size; ?></strong></li>
                </ul>
            </div>
        </div>
        <br />
        <div class="row">
            <div class="col-sm-3">
                <div class="filter-status">
                    <div class="heading"><?php _e('panel', 'overview_label_action_logger', 'Action Logger'); ?></div>
                    <div class="nums">
                        <?php echo $data['action_logger']  ? '<i class="far fa-play-circle"></i>' : '<i class="far fa-stop-circle"></i>'; ?>
                    </div>
                    <div class="note"><?php _e('panel', 'overview_note_action_logger', 'Record every visitor’s behavior.'); ?></div>
                    <button class="note-code" onclick="openInfoModal(this)" 
                        data-document="https://shield-on-php.github.io/en/logs/actionlogger.html" 
                        data-id="logs-actionlogger" 
                        data-title="Action Logger">
                        <i class="fas fa-code"></i>
                    </button>
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
                    <div class="heading"><?php _e('panel', 'overview_label_recaptcha', 'reCAPTCHA'); ?></div>
                    <div class="nums">
                        <?php echo $captcha['recaptcha']  ? '<i class="far fa-play-circle"></i>' : '<i class="far fa-stop-circle"></i>'; ?>
                    </div>
                    <div class="note"><?php _e('panel', 'overview_note_recaptcha', 'Provided by Google.'); ?></div>
                    <button class="note-code" onclick="openInfoModal(this)" 
                        data-document="https://shield-on-php.github.io/en/captcha/recaptcha.html" 
                        data-id="captcha-recaptcha" 
                        data-title="reCAPTCHA">
                        <i class="fas fa-code"></i>
                    </button>
                </div>
            </div>
            <div class="col-sm-3">
                <div class="filter-status">
                    <div class="heading"><?php _e('panel', 'overview_label_image_captcha', 'Image Captcha'); ?></div>
                    <div class="nums">
                        <?php echo $captcha['imagecaptcha']  ? '<i class="far fa-play-circle"></i>' : '<i class="far fa-stop-circle"></i>'; ?>
                    </div>
                    <div class="note"><?php _e('panel', 'overview_note_image_captcha', 'A simple text-in-image Captcha.'); ?></div>
                    <button class="note-code" onclick="openInfoModal(this)" 
                        data-document="https://shield-on-php.github.io/en/captcha/image.html" 
                        data-id="captcha-image" 
                        data-title="Image Captcha">
                        <i class="fas fa-code"></i>
                    </button>
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
                        <?php echo $messengers[$k]  ? '<i class="far fa-play-circle"></i>' : '<i class="far fa-stop-circle"></i>'; ?>
                    </div>
                    <div class="note"><?php _e('panel', 'overview_note_' . $k, ''); ?></div>
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
                <button type="button" class="btn btn-primary" id="btn-document-link" data-url=""><i class="far fa-file-code"></i> <?php _e('panel', 'overview_btn_document', 'Document'); ?></button>
                <button type="button" class="btn btn-secondary" data-dismiss="modal"><?php _e('panel', 'overview_btn_close', 'Close'); ?></button>
            </div>
        </div>
    </div>
</div>

<div id="reset-modal" class="modal fade" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <form method="post" id="form-reset-data">
        <?php $this->_csrf(); ?>
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"></h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body"></div>
            <div class="modal-footer">
                <button type="submit" class="btn btn-danger"><?php _e('panel', 'auth_btn_submit', 'Submit'); ?></button>
                <button type="button" class="btn btn-secondary" data-dismiss="modal"><?php _e('panel', 'overview_btn_close', 'Close'); ?></button>
            </div>
        </div>
        </form>
    </div>
</div>


<script type="text/template" id="filters-session">
    <pre>
        <code class="php">
$kernel->setFilters([
    'session' => true
]);

// or

$kernel->setFilter('session', true);
        </code>
    </pre>
</script>

<script type="text/template" id="filters-cookie">
    <pre>
        <code class="php">
$kernel->setFilters([
    'cookie' => true
]);

// or

$kernel->setFilter('cookie', true);
        </code>
    </pre>
</script>

<script type="text/template" id="filters-frequency">
    <pre>
        <code class="php">
$kernel->setFilters([
    'frequency' => true
]);

// or
$kernel->setFilter('frequency', true);

$kernel->setProperty('time_unit_quota', [
    's' => 4,
    'm' => 20, 
    'h' => 60, 
    'd' => 240
]);

        </code>
    </pre>
</script>

<script type="text/template" id="filters-referer">
    <pre>
        <code class="php">
$kernel->setFilters([
    'referer' => true
]);

// or

$kernel->setFilter('referer', true);
        </code>
    </pre>
</script>

<script type="text/template" id="components-ip">
    <pre>
        <code class="php">
$ip = new \Shieldon\Firewall\Component\Ip();
$kernel->setComponent($ip);
        </code>
    </pre>
    <h5>API</h5>
    <ul>
        <li>inRange</li>
        <li>setAllowedItems</li>
        <li>setAllowedItem</li>
        <li>getAllowedItems</li>
        <li>setDeniedItems</li>
        <li>setDeniedItem</li>
        <li>getDeniedItems</li>
        <li>removeItem</li>
    </ul>
    <p>
        <?php _e('panel', 'overview_text_more_usages', 'For more usages please check out that document.'); ?>
    </p>
</script>

<script type="text/template" id="components-trustedbot">
    <pre>
        <code class="php">
$robot = new \Shieldon\Firewall\Component\TrustedBot();
$kernel->setComponent($robot);
        </code>
    </pre>
    <h5>API</h5>
    <ul>
        <li>setStrict</li>
        <li>isAllowed</li>
        <li>isDenied</li>
        <li>isGoogle</li>
        <li>isYahoo</li>
        <li>isBing</li>
        <li>addItem</li>
        <li>setDeniedItems</li>
        <li>setDeniedItem</li>
        <li>getDeniedItems</li>
        <li>removeItem</li>
    </ul>
    <p>
        <?php _e('panel', 'overview_text_more_usages', 'For more usages please check out that document.'); ?>
    </p>
</script>

<script type="text/template" id="components-header">
    <pre>
        <code class="php">
$header = new \Shieldon\Firewall\Component\Header();
$kernel->setComponent($header);
        </code>
    </pre>
    <h5>API</h5>
    <ul>
        <li>setStrict</li>
        <li>setDeniedItems</li>
        <li>setDeniedItem</li>
        <li>getDeniedItems</li>
        <li>removeItem</li>
    </ul>
    <p>
        <?php _e('panel', 'overview_text_more_usages', 'For more usages please check out that document.'); ?>
    </p>
</script>

<script type="text/template" id="components-rdns">
    <pre>
        <code class="php">
$rdns = new \Shieldon\Firewall\Component\Rdns();
$kernel->setComponent($rdns);
        </code>
    </pre>
    <h5>API</h5>
    <ul>
        <li>setStrict</li>
        <li>setDeniedItems</li>
        <li>setDeniedItem</li>
        <li>getDeniedItems</li>
        <li>removeItem</li>
    </ul>
    <p>
        <?php _e('panel', 'overview_text_more_usages', 'For more usages please check out that document.'); ?>
    </p>
</script>

<script type="text/template" id="components-useragent">
    <pre>
        <code class="php">
$agent = new \Shieldon\Firewall\Component\UserAgent();
$kernel->setComponent($agent);
        </code>
    </pre>
    <h5>API</h5>
    <ul>
        <li>setStrict</li>
        <li>setDeniedItems</li>
        <li>setDeniedItem</li>
        <li>getDeniedItems</li>
        <li>removeItem</li>
    </ul>
    <p>
        <?php _e('panel', 'overview_text_more_usages', 'For more usages please check out that document.'); ?>
    </p>
</script>

<script type="text/template" id="driver-mysql">
    <pre>
        <code class="php">
$db = [
    'host'    => '127.0.0.1',
    'dbname'  => 'your_database',
    'user'    => 'your_user_name',
    'pass'    => 'your_password',
    'charset' => 'utf8',
];

$pdoInstance = new \PDO(
    'mysql:host=' . 
        $db['host'] . ';dbname=' . 
        $db['dbname'] . ';charset=' . 
        $db['charset']
    , $db['user']
    , $db['pass']
);

$kernel->setDriver(
    new \Shieldon\Firewall\Driver\MysqlDriver($pdoInstance)
);
        </code>
    </pre>
</script>

<script type="text/template" id="driver-redis">
    <pre>
        <code class="php">
$redisInstance = new \Redis();
$redisInstance->connect('127.0.0.1', 6379); 

$kernel->setDriver(
    new \Shieldon\Firewall\Driver\RedisDriver($redisInstance)
);
        </code>
    </pre>
</script>

<script type="text/template" id="driver-file">
    <pre>
        <code class="php">

// $path:
// Absolute path of the directory where you store the data in.

$kernel->setDriver(
    new \Shieldon\Firewall\Driver\FileDriver($path)
);
        </code>
    </pre>
</script>

<script type="text/template" id="driver-sqlite">
    <pre>
        <code class="php">
// $dbLocation:
// Absolute path of the sqlite file.

$dbLocation = APPPATH . 'cache/shieldon.sqlite3';
$pdoInstance = new \PDO('sqlite:' . $dbLocation);
$kernel->setDriver(new \Shieldon\Firewall\Driver\SqliteDriver($pdoInstance));
        </code>
    </pre>
</script>

<script type="text/template" id="logs-actionlogger">
    <pre>
        <code class="php">
// $logDirectory:
// Absolute path of the directory where the logs will be stored in.

$logger = new \Shieldon\Firewall\Log\ActionLogger($logDirectory);
$kernel->setLogger($logger);
        </code>
    </pre>
</script>

<script type="text/template" id="captcha-recaptcha">
    <pre>
        <code class="php">
$captchaConfig = [
    'key' => '6LfkOaUUAAAAAH-AlTz3hRQ25SK8kZKb2hDRSwz9',
    'secret' => '6LfkOaUUAAAAAJddZ6k-1j4hZC1rOqYZ9gLm0WQh',
];

$captchaInstance = new \Shieldon\Firewall\Captcha\Recaptcha($captchaConfig);
$kernel->setCaptcha($captchaInstance);
        </code>
    </pre>
</script>

<script type="text/template" id="captcha-image">
    <pre>
        <code class="php">
$config = [
    'word_length' => 6,
];

$captchaInstance = new \Shieldon\Firewall\Captcha\ImageCaptcha($config);
$kernel->setCaptcha($captchaInstance);
        </code>
    </pre>
</script>

<script type="text/template" id="reset-data-circle">
    <p><?php _e('panel', 'overview_text_reset_data_circle_1', 'Would you like to reset current data circle?'); ?></p>
    <table class="table table-bordered">
        <thead class="thead-dark">
            <th><?php _e('panel', 'overview_thread_rows', 'Table'); ?></th>
            <th><?php _e('panel', 'overview_thread_table', 'Rows'); ?></th>
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
    <p><?php _e('panel', 'overview_text_reset_data_circle_2', 'Performing this action will remove all data from current data circle and rebuild data tables.'); ?></p>
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