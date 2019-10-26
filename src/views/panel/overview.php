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

$timezone = '';

?>
<script src="https://cdnjs.cloudflare.com/ajax/libs/highlight.js/9.15.9/highlight.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/highlight.js/9.15.9/languages/php.min.js"></script>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/highlight.js/9.15.9/styles/atom-one-light.min.css" />
<script>hljs.initHighlightingOnLoad();</script>

<div class="so-dashboard">
    <div class="so-datatables">
        <div class="so-datatable-heading">
            <?php _e('panel', 'overview_heading_data_circle', 'Data Circle'); ?>
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
                        <?php echo $driver['mysql']  ? '<i class="far fa-check-circle"></i>' : '<i class="far fa-circle"></i>'; ?>
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
                        <?php echo $driver['redis']  ? '<i class="far fa-check-circle"></i>' : '<i class="far fa-circle"></i>'; ?>
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
                        <?php echo $driver['file']  ? '<i class="far fa-check-circle"></i>' : '<i class="far fa-circle"></i>'; ?>
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

<div id="info-modal" class="modal fade" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
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

<script type="text/template" id="filters-session">
    <pre>
        <code class="php">
$shieldon->setFilters([
    'session' => true
]);

// or

$shieldon->setFilter('session', true);
        </code>
    </pre>
</script>

<script type="text/template" id="filters-cookie">
    <pre>
        <code class="php">
$shieldon->setFilters([
    'cookie' => true
]);

// or

$shieldon->setFilter('cookie', true);
        </code>
    </pre>
</script>

<script type="text/template" id="filters-frequency">
    <pre>
        <code class="php">
$shieldon->setFilters([
    'frequency' => true
]);

// or
$shieldon->setFilter('frequency', true);

$shieldon->setProperty('time_unit_quota', [
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
$shieldon->setFilters([
    'referer' => true
]);

// or

$shieldon->setFilter('referer', true);
        </code>
    </pre>
</script>

<script type="text/template" id="components-ip">
    <pre>
        <code class="php">
$ip = new \Shieldon\Component\Ip();
$shieldon->setComponent($ip);
        </code>
    </pre>
    <h5>API</h5>
    <ul>
        <li>inRange</li>
        <li>setAllowedList</li>
        <li>setAllowedItem</li>
        <li>getAllowedList</li>
        <li>setDeniedList</li>
        <li>setDeniedItem</li>
        <li>getDeniedList</li>
        <li>removeItem</li>
    </ul>
    <p>
        <?php _e('panel', 'overview_text_more_usages', 'For more usages please check out that document.'); ?>
    </p>
</script>

<script type="text/template" id="components-trustedbot">
    <pre>
        <code class="php">
$robot = new \Shieldon\Component\TrustedBot();
$shieldon->setComponent($robot);
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
        <li>setDeniedList</li>
        <li>setDeniedItem</li>
        <li>getDeniedList</li>
        <li>removeItem</li>
    </ul>
    <p>
        <?php _e('panel', 'overview_text_more_usages', 'For more usages please check out that document.'); ?>
    </p>
</script>

<script type="text/template" id="components-header">
    <pre>
        <code class="php">
$header = new \Shieldon\Component\Header();
$shieldon->setComponent($header);
        </code>
    </pre>
    <h5>API</h5>
    <ul>
        <li>setStrict</li>
        <li>setDeniedList</li>
        <li>setDeniedItem</li>
        <li>getDeniedList</li>
        <li>removeItem</li>
    </ul>
    <p>
        <?php _e('panel', 'overview_text_more_usages', 'For more usages please check out that document.'); ?>
    </p>
</script>

<script type="text/template" id="components-rdns">
    <pre>
        <code class="php">
$rdns = new \Shieldon\Component\Rdns();
$shieldon->setComponent($rdns);
        </code>
    </pre>
    <h5>API</h5>
    <ul>
        <li>setStrict</li>
        <li>setDeniedList</li>
        <li>setDeniedItem</li>
        <li>getDeniedList</li>
        <li>removeItem</li>
    </ul>
    <p>
        <?php _e('panel', 'overview_text_more_usages', 'For more usages please check out that document.'); ?>
    </p>
</script>

<script type="text/template" id="components-useragent">
    <pre>
        <code class="php">
$agent = new \Shieldon\Component\UserAgent();
$shieldon->setComponent($agent);
        </code>
    </pre>
    <h5>API</h5>
    <ul>
        <li>setStrict</li>
        <li>setDeniedList</li>
        <li>setDeniedItem</li>
        <li>getDeniedList</li>
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

$shieldon->setDriver(
    new \Shieldon\Driver\MysqlDriver($pdoInstance)
);
        </code>
    </pre>
</script>

<script type="text/template" id="driver-redis">
    <pre>
        <code class="php">
$redisInstance = new \Redis();
$redisInstance->connect('127.0.0.1', 6379); 

$shieldon->setDriver(
    new \Shieldon\Driver\RedisDriver($redisInstance)
);
        </code>
    </pre>
</script>

<script type="text/template" id="driver-file">
    <pre>
        <code class="php">

// $path:
// Absolute path of the directory where you store the data in.

$shieldon->setDriver(
    new \Shieldon\Driver\FileDriver($path)
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
$shieldon->setDriver(new \Shieldon\Driver\SqliteDriver($pdoInstance));
        </code>
    </pre>
</script>

<script type="text/template" id="logs-actionlogger">
    <pre>
        <code class="php">
// $logDirectory:
// Absolute path of the directory where the logs will be stored in.

$logger = new \Shieldon\Log\ActionLogger($logDirectory);
$shieldon->setLogger($logger);
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

$captchaInstance = new \Shieldon\Captcha\Recaptcha($captchaConfig);
$shieldon->setCaptcha($captchaInstance);
        </code>
    </pre>
</script>

<script type="text/template" id="captcha-image">
    <pre>
        <code class="php">
$config = [
    'word_length' => 6,
];

$captchaInstance = new \Shieldon\Captcha\ImageCaptcha($config);
$shieldon->setCaptcha($captchaInstance);
        </code>
    </pre>
</script>

<script>

    $(function() {

        $('#btn-document-link').click(function() {
            let url = $(this).attr('data-url');

            if (url !== '') {
                window.open(url, '_blank');
            }
        });
    });

    var openInfoModal = function(obj) {
        let id = $(obj).attr('data-id');
        let title = $(obj).attr('data-title');
        let document = $(obj).attr('data-document');

        $('#info-modal').find('.modal-title').html(title);
        $('#info-modal').find('.modal-body').html($('#' + id).html());

        highlight();

        $('#btn-document-link').attr('data-url', document);

 

        $('#info-modal').modal();
    };

    function highlight() {
        document.querySelectorAll('pre code').forEach((block) => {
            hljs.highlightBlock(block);
        });
    }

</script>