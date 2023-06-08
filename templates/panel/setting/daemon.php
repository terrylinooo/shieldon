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

?>
<div class="section-title bg-glass">
    <h2><?php _e('panel', 'setting_heading_enable', 'Enable'); ?></h2>
    <div class="toggle-container">
        <label class="rocker rocker-sm rocker-test">
            <input type="hidden" name="daemon" value="off" />
            <input type="checkbox" 
                name="daemon" 
                class="toggle-block" 
                value="on" 
                data-target="daemon-section" 
                <?php $this->checked('daemon', true); ?> 
            />
            <span class="switch-left"><i>ON</i></span>
            <span class="switch-right"><i>OFF</i></span>
        </label>
    </div>
</div>
<div class="section-body my-0" data-parent="daemon-section">
    <table class="setting-table">
        <tr>
            <td class="r1"><?php _e('panel', 'setting_label_data_driver', 'Data Driver'); ?></td>
            <td class="r2">
                <p>
                    <?php
                    _e(
                        'panel',
                        'setting_note_data_driver',
                        'Start protecting your website by implementing Shieldon. 
                        Shieldon Firewall only works when this option is enabled.'
                    );
                    ?>
                </p>
                <div>
                    <div class="button-wrap">
                        <input class="radio-label"
                            type="radio"
                            name="driver_type"
                            id="driver-file"
                            value="file"
                            <?php $this->checked('driver_type', 'file'); ?> 
                        />
                        <label class="button-label" for="driver-file">
                            <?php _e('panel', 'setting_label_driver_file', 'File System'); ?>
                        </label>
                        <input class="radio-label"
                            type="radio"
                            name="driver_type"
                            id="driver-mysql"
                            value="mysql"
                            <?php $this->checked('driver_type', 'mysql'); ?> 
                        />
                        <label class="button-label" for="driver-mysql">
                            <?php _e('panel', 'setting_label_driver_mysql', 'MySQL'); ?>
                        </label>
                        <input class="radio-label"
                            type="radio"
                            name="driver_type"
                            id="driver-redis"
                            value="redis"
                            <?php $this->checked('driver_type', 'redis'); ?> 
                        />
                        <label class="button-label" for="driver-redis">
                            <?php _e('panel', 'setting_label_driver_redis', 'Redis'); ?>
                        </label>
                        <input class="radio-label"
                            type="radio"
                            name="driver_type"
                            id="driver-sqlite"
                            value="sqlite"
                            <?php $this->checked('driver_type', 'sqlite'); ?> 
                        />
                        <label class="button-label" for="driver-sqlite">
                            <?php _e('panel', 'setting_label_driver_sqlite', 'SQLite'); ?>
                        </label>
                    </div>
                </div>
            </td>
        </tr>
        <tr>
            <td class="r1"></td>
            <td class="r2">
                <div class="data-driver-options data-driver-options-file">
                    <div class="section-title sm bg-dot" data-parent="daemon-section">
                        <h3><?php _e('panel', 'setting_label_driver_file', 'File System'); ?></h3>
                    </div>
                    <div class="section-body sm" data-parent="daemon-section">
                        <div class="container">
                            <div class="row">
                                <div class="col-sm-7 py-2">
                                    <label>
                                        <?php _e('panel', 'setting_label_directory', 'Directory'); ?>
                                    </label>
                                    <input disabled type="text"
                                        name="drivers__file__directory_path"
                                        class="form-control form-control-sm"
                                        value="<?php $this->_('drivers.file.directory_path'); ?>"
                                    />
                                    <br />
                                    <p>
                                        <?php
                                        _e(
                                            'panel',
                                            'setting_note_directory',
                                            'The absolute path of the directory where you store the data.'
                                        );
                                        ?>
                                    </p>
                                </div>
                            </div>
                            <p class="text-info">
                                <?php
                                _e(
                                    'panel',
                                    'setting_note_driver_not_recommended',
                                    'Not recommended for high-traffic wwebsite.'
                                );
                                ?>
                            </p>
                        </div>
                    </div>
                </div>
                <div class="data-driver-options data-driver-options-mysql">
                    <div class="section-title sm bg-dot" data-parent="daemon-section">
                        <h3><?php _e('panel', 'setting_label_driver_mysql', 'MySQL'); ?></h3>
                    </div>
                    <div class="section-body sm" data-parent="daemon-section">
                        <div class="container">
                            <div class="row">
                                <div class="col-sm-3 py-2">
                                    <label>
                                        <?php _e('panel', 'setting_label_mysql_host', 'Host'); ?>
                                    </label>
                                    <input type="text"
                                        name="drivers__mysql__host"
                                        class="form-control form-control-sm"
                                        value="<?php $this->_('drivers.mysql.host'); ?>"
                                    />
                                </div>
                                <div class="col-sm-3 py-2">
                                    <label>
                                        <?php _e('panel', 'setting_label_mysql_dbname', 'DB Name'); ?>
                                    </label>
                                    <input type="text" 
                                        name="drivers__mysql__dbname" 
                                        class="form-control form-control-sm" 
                                        value="<?php $this->_('drivers.mysql.dbname'); ?>"
                                    />
                                </div>
                                <div class="col-sm-3 py-2">
                                    <label>
                                        <?php _e('panel', 'setting_label_mysql_user', 'User'); ?>
                                    </label>
                                    <input type="text" 
                                        name="drivers__mysql__user"
                                        class="form-control form-control-sm"
                                        value="<?php $this->_('drivers.mysql.user'); ?>"
                                    />
                                </div>
                                <div class="col-sm-3 py-2">
                                    <label>
                                        <?php _e('panel', 'setting_label_mysql_password', 'Password'); ?>
                                    </label>
                                    <input type="text"
                                        name="drivers__mysql__pass"
                                        class="form-control form-control-sm"
                                        value="<?php $this->_('drivers.mysql.pass'); ?>"
                                    />
                                </div>
                                <div class="col-sm-3 py-2">
                                    <label>
                                        <?php _e('panel', 'setting_label_mysql_charset', 'Charset'); ?>
                                    </label>
                                    <input type="text"
                                        name="drivers__mysql__charset"
                                        class="form-control form-control-sm"
                                        value="<?php $this->_('drivers.mysql.charset'); ?>"
                                    />
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="data-driver-options data-driver-options-redis">
                    <div class="section-title sm bg-dot" data-parent="daemon-section">
                        <h3><?php _e('panel', 'setting_label_driver_redis', 'Redis'); ?></h3>
                    </div>
                    <div class="section-body sm" data-parent="daemon-section">
                        <div class="container">
                            <div class="row">
                                <div class="col-sm-3 py-2">
                                    <label>
                                        <?php _e('panel', 'setting_label_redis_host', 'Host'); ?>
                                    </label>
                                    <input type="text"
                                        name="drivers__redis__host"
                                        class="form-control form-control-sm"
                                        value="<?php $this->_('drivers.redis.host'); ?>"
                                    />
                                </div>
                                <div class="col-sm-3 py-2">
                                    <label>
                                        <?php _e('panel', 'setting_label_redis_port', 'Port'); ?>
                                    </label>
                                    <input type="text"
                                        name="drivers__redis__port"
                                        class="form-control form-control-sm"
                                        value="<?php $this->_('drivers.redis.port'); ?>"
                                    />
                                </div>
                                <div class="col-sm-3 py-2">
                                    <label>
                                        <?php _e('panel', 'setting_label_redis_auth', 'Auth'); ?>
                                    </label>
                                    <input type="text"
                                        name="drivers__redis__auth"
                                        class="form-control form-control-sm"
                                        value="<?php $this->_('drivers.redis.auth'); ?>"
                                    /><br />
                                    <p>
                                        <?php
                                        _e(
                                            'panel',
                                            'setting_note_redis_auth',
                                            'Required only if a password is needed.'
                                        );
                                        ?>
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="data-driver-options data-driver-options-sqlite">
                    <div class="section-title sm bg-dot" data-parent="daemon-section">
                        <h3><?php _e('panel', 'setting_label_driver_sqlite', 'SQLite'); ?></h3>
                    </div>
                    <div class="section-body sm" data-parent="daemon-section">
                        <div class="container">
                            <div class="row">
                                <div class="col-sm-7 py-2">
                                    <label>
                                        <?php _e('panel', 'setting_label_directory', 'Directory'); ?>
                                    </label>
                                    <input disabled type="text"
                                        name="drivers__sqlite__directory_path"
                                        class="form-control form-control-sm"
                                        value="<?php $this->_('drivers.sqlite.directory_path'); ?>"
                                    />
                                    <br />
                                    <p>
                                        <?php
                                        _e(
                                            'panel',
                                            'setting_note_directory',
                                            'The absolute path of the directory where you store the data.'
                                        );
                                        ?>
                                    </p>
                                </div>
                            </div>
                            <p class="text-info">
                                <?php
                                _e(
                                    'panel',
                                    'setting_note_driver_not_recommended',
                                    'Not recommended for high-traffic wwebsite.'
                                );
                                ?>
                            </p>
                        </div>
                    </div>
                </div>
            </td>
        </tr>
    </table>
</div>
<div class="section-body my-0" data-parent="daemon-section">
    <table class="setting-table">
        <tr>
            <td class="r1">
                <?php _e('panel', 'setting_label_reset_data_cycle', 'Reset Data Cycle'); ?>
            </td>
            <td class="r2">
                <p>
                    <?php
                    _e(
                        'panel',
                        'setting_label_reset_data_cycle',
                        'Clear all logs every day at 0:00 a.m. automatically. 
                        Turning this option on will improve performance.'
                    );
                    ?>
                </p>
                <label class="rocker rocker-sm">
                    <input type="hidden" name="cronjob__reset_circle__enable" value="off" />
                    <input type="checkbox"
                        name="cronjob__reset_circle__enable"
                        class="toggle-block" value="on"
                        <?php $this->checked('cronjob.reset_circle.enable', true); ?>
                    />
                    <span class="switch-left"><i>ON</i></span>
                    <span class="switch-right"><i>OFF</i></span>
                </label>
            </td>
        </tr>
    </table>
</div>
<div class="section-body my-0" data-parent="daemon-section">
    <table class="setting-table">
        <tr>
            <td class="r1"><?php _e('panel', 'setting_label_ip_source', 'IP Source'); ?></td>
            <td class="r2">
                <p>
                    <?php
                    _e(
                        'panel',
                        'setting_note_ip_source',
                        'Is your website behind a CDN service? If you use CDN, you have to set this setting correctly; 
                        otherwise, all IP addresses from CDN servers will probably get banned.'
                    );
                    ?>
                </p>
                <div class="col-sm-12">
                    <div class="radio-style">
                        <input name="ip_variable_source"
                            value="REMOTE_ADDR"
                            type="radio"
                            id="ip-source-1"
                            <?php $this->checked('ip_variable_source.REMOTE_ADDR', true); ?>
                        /> 
                        <label for="ip-source-1" class="radio-label">
                            REMOTE_ADDR
                            <small>
                                <?php echo ($_SERVER['REMOTE_ADDR']) ??
                                    '<i class="fas fa-times-circle text-danger"></i>'; ?>
                            </small>
                        </label> 
                    </div>
                    <div class="radio-style">
                        <input name="ip_variable_source"
                            value="HTTP_CF_CONNECTING_IP"
                            type="radio"
                            id="ip-source-2"
                            <?php $this->checked('ip_variable_source.HTTP_CF_CONNECTING_IP', true); ?>
                        /> 
                        <label for="ip-source-2" class="radio-label">
                            HTTP_CF_CONNECTING_IP
                            <small>
                                <?php echo ($_SERVER['HTTP_CF_CONNECTING_IP']) ??
                                    '<i class="fas fa-times-circle text-danger"></i>'; ?>
                            </small>
                        </label> 
                    </div>
                    <div class="radio-style">
                        <input name="ip_variable_source"
                            value="HTTP_X_FORWARDED_FOR"
                            type="radio"
                            id="ip-source-3"
                            <?php $this->checked('ip_variable_source.HTTP_X_FORWARDED_FOR', true); ?>
                        /> 
                        <label for="ip-source-3" class="radio-label">
                            HTTP_X_FORWARDED_FOR
                            <small>
                                <?php echo ($_SERVER['HTTP_X_FORWARDED_FOR']) ??
                                    '<i class="fas fa-times-circle text-danger"></i>'; ?>
                            </small>
                        </label> 
                    </div>
                    <div class="radio-style">
                        <input name="ip_variable_source"
                            value="HTTP_X_FORWARDED_HOST"
                            type="radio"
                            id="ip-source-4"
                            <?php $this->checked('ip_variable_source.HTTP_X_FORWARDED_HOST', true); ?>
                        /> 
                        <label for="ip-source-4" class="radio-label">
                            HTTP_X_FORWARDED_HOST
                            <small>
                                <?php echo ($_SERVER['HTTP_X_FORWARDED_HOST']) ??
                                    '<i class="fas fa-times-circle text-danger"></i>'; ?>
                            </small>
                        </label> 
                    </div>
                </div>
            </td>
        </tr>
    </table>
</div>
<!-------------------------------------------------------------------------------------------------------------->
<div class="section-title bg-glass mt-3">
    <h2><?php _e('panel', 'setting_label_session_limit', 'Session Limit'); ?></h2>
    <div class="toggle-container">
        <label class="rocker rocker-sm rocker-test">
            <input type="hidden" name="online_session_limit__enable" value="off" />
            <input type="checkbox"
                name="online_session_limit__enable"
                class="toggle-block"
                value="on"
                data-target="online-session-limit-section"
                <?php $this->checked('online_session_limit.enable', true); ?>
            />
            <span class="switch-left"><i>ON</i></span>
            <span class="switch-right"><i>OFF</i></span>
        </label>
    </div>
</div>
<div class="section-body my-0" data-parent="online-session-limit-section">
    <table class="setting-table">
        <tr>
            <td class="r1"></td>
            <td class="r2">
                <p>
                    <?php
                    _e(
                        'panel',
                        'setting_note_session_limit_1',
                        'When the number of users online has reached the limit, 
                        users that are not in the queue have to line up!'
                    );
                    ?>
                    <br />
                    <?php
                    _e(
                        'panel',
                        'setting_note_session_limit_2',
                        'The following image is an example.'
                    );
                    ?>
                </p>
            </td>
        </tr>
        <tr>
            <td class="r1">
                <?php _e('panel', 'setting_label_online_limit', 'Online Limit'); ?>
            </td>
            <td class="r2">
                <input type="text"
                    name="online_session_limit__config__count"
                    class="form-control form-control-sm col-sm-3"
                    value="<?php $this->_('online_session_limit.config.count'); ?>"
                />
                <br />
                <p>
                    <?php
                    _e(
                        'panel',
                        'setting_note_online_limit',
                        'The maximum online user limit.'
                    );
                    ?>
                </p>
            </td>
        </tr>
    </table>
</div>
<div class="section-body my-0" data-parent="online-session-limit-section">
    <table class="setting-table">
        <tr>
            <td class="r1">
                <?php _e('panel', 'setting_label_alive_period', 'Keep Alive Period'); ?>
            </td>
            <td class="r2">
                <input type="text" 
                    name="online_session_limit__config__period"
                    class="form-control form-control-sm col-sm-3"
                    value="<?php $this->_('online_session_limit.config.period'); ?>"
                />
                <br />
                <p>
                    <?php
                    _e(
                        'panel',
                        'setting_note_alive_period',
                        'Unit: minute.'
                    );
                    ?>
                </p>
            </td>
        </tr>
    </table>
</div>
<div class="section-body my-0" data-parent="online-session-limit-section">
    <table class="setting-table">
        <tr>
            <td class="r1">
                <?php _e('panel', 'setting_label_unique_ip_only', 'Only Unique IP'); ?>
            </td>
            <td class="r2">
                <p>
                    <?php
                    _e(
                        'panel',
                        'setting_note_unique_ip_only',
                        'Only allow one session per IP address.'
                    );
                    ?>
                    <br />
                    <?php
                    _e(
                        'panel',
                        'setting_note_unique_ip_only_2',
                        'Users with multiple sessions will be kicked.'
                    );
                    ?>
                </p>
                <label class="rocker rocker-sm">
                    <input type="hidden" name="online_session_limit__config__unique_only" value="off" />
                    <input type="checkbox"
                        name="online_session_limit__config__unique_only"
                        class="toggle-block"
                        value="on"
                        <?php $this->checked('online_session_limit.config.unique_only', true); ?>
                    />
                    <span class="switch-left"><i>ON</i></span>
                    <span class="switch-right"><i>OFF</i></span>
                </label>
            </td>
        </tr>
    </table>
</div>
<!-------------------------------------------------------------------------------------------------------------->
<div class="section-title bg-glass mt-3">
    <h2><?php _e('panel', 'setting_label_action_logs', 'Action Logs'); ?></h2>
    <div class="toggle-container">
        <label class="rocker rocker-sm rocker-test">
            <input type="hidden" name="loggers__action__enable" value="off" />
            <input type="checkbox"
                name="loggers__action__enable"
                class="toggle-block"
                value="on"
                data-target="logger-action-section"
                <?php $this->checked('loggers.action.enable', true); ?>
            />
            <span class="switch-left"><i>ON</i></span>
            <span class="switch-right"><i>OFF</i></span>
        </label>
    </div>
</div>
<div class="section-body my-0" data-parent="logger-action-section">
    <table class="setting-table">
        <tr>
            <td class="r1"><?php _e('panel', 'setting_label_action_logger', 'Action Logger'); ?></td>
            <td class="r2">
                <label>
                    <?php _e('panel', 'setting_label_directory', 'Directory'); ?>
                </label>
                <input disabled type="text"
                    name="loggers__action__config__directory_path"
                    class="form-control form-control-sm col-sm-7"
                    value="<?php $this->_('loggers.action.config.directory_path'); ?>"
                />
                <br />
                <p>
                    <?php
                    _e(
                        'panel',
                        'setting_note_directory',
                        'The absolute path of the directory where you store the data.'
                    );
                    ?>
                </p>
                <p class="text-info">
                    <?php
                    _e(
                        'panel',
                        'setting_note_driver_not_recommended',
                        'Not recommended for high-traffic wwebsite.'
                    );
                    ?>
                </p>
            </td>
        </tr>
    </table>
</div>
<!-------------------------------------------------------------------------------------------------------------->
<div class="section-title bg-glass mt-3">
    <h2><?php _e('panel', 'setting_label_system_firewall', 'System Firewall'); ?></h2>
    <div class="toggle-container">
        <label class="rocker rocker-sm rocker-test">
            <input type="hidden" name="iptables__enable" value="off" />
            <input type="checkbox" 
                name="iptables__enable"
                class="toggle-block"
                value="on"
                data-target="iptables-section"
                <?php $this->checked('iptables.enable', true); ?>
            />
            <span class="switch-left"><i>ON</i></span>
            <span class="switch-right"><i>OFF</i></span>
        </label>
    </div>
</div>
<div class="section-body my-0" data-parent="iptables-section">
    <p>
        <?php
        _e(
            'panel',
            'setting_note_install_iptables',
            'Make sure you have installed <strong>iptables</strong> 
            and <strong>ip6tables</strong> already in your server, 
            and employ <strong>iptables_bridge.sh</strong> in <strong>crontab</strong> correctly.'
        );
        ?>
    </p>
    <table class="setting-table">
        <tr>
            <td class="r1">
                <?php _e('panel', 'setting_label_watching_folder', 'Watch Folder'); ?>
            </td>
            <td class="r2">
                <input disabled type="text" 
                    name="iptables__config__watching_folder"
                    class="form-control form-control-sm col-sm-7"
                    value="<?php $this->_('iptables.config.watching_folder'); ?>"
                />
                <br />
                <p>
                    <?php
                    _e(
                        'panel',
                        'setting_note_directory',
                        'The absolute path of the directory where you store the data.'
                    );
                    ?>
                </p>
            </td>
        </tr>
    </table>
</div>
<div class="section-body my-0" data-parent="iptables-section">
    <table class="setting-table">
        <tr>
            <td class="r1">
                <?php _e('panel', 'setting_label_cronjob', 'Cron Job'); ?>
            </td>
            <td class="r2">
                <div>
                    <?php if ($this->mode === 'demo') : ?>
                        <code id="code1" style="display: none">* * * * * root bash your_project_folder/vendor/shieldon/bin/iptables_bridge.sh --watch=your_watching_folder >> /var/log/shieldon.log 2>&1</code>
                    <?php else : ?>
                        <code id="code1" style="display: none">* * * * * root bash <?php echo realpath(__DIR__ . '/../../../bin/iptables_bridge.sh') ?> --watch=<span id="iptables-watch-folder"></span> >> /var/log/shieldon.log 2>&1</code>
                    <?php endif; ?>

                    <textarea id="code2" class="form-control" rows="4" style="font-size: 12px; font-family: monospace;"></textarea>
                </div><br />
                <p>
                    <?php
                    _e(
                        'panel',
                        'setting_note_cronjob',
                        'Please insert this code into the crontab file on your server.'
                    );
                    ?>
                    <br />
                    <?php
                    _e(
                        'panel',
                        'setting_note_iptables',
                        '<strong>iptables_bridge.sh</strong> will watch the 
                        changes in this folder to employ commands to iptables.'
                    );
                    ?>
                    <br />
                    <span class="text-danger">
                        <?php
                        _e(
                            'panel',
                            'setting_note_iptables_bridge',
                            'Please move <strong>iptables_bridge.sh</strong> to a safe place 
                            only accessible to you, changing the path for security reasons.'
                        );
                        ?>
                    </span>
                </p>
            </td>
        </tr>
    </table>
</div>
<!-------------------------------------------------------------------------------------------------------------->
<div class="section-title bg-glass mt-3">
    <h2><?php _e('panel', 'setting_heading_deny_attempts', 'Deny Attempts'); ?></h2>
</div>
<div class="section-body my-0">
    <p><?php _e('panel', 'setting_desc_deny_attempts', 'Say goodbye to badly behaved visitors.'); ?></p>
    <table class="setting-table">
        <tr>
            <td class="r1">
                <?php _e('panel', 'setting_label_record_attempt_detection_period', 'Detection Period'); ?>
            </td>
            <td class="r2">
                <input type="text"
                    name="record_attempt__detection_period"
                    class="form-control form-control-sm col-sm-3"
                    value="<?php $this->_('record_attempt.detection_period', 5); ?>"
                />
                <br />
                <p>
                    <?php
                    _e(
                        'panel',
                        'setting_desc_record_attempt_detection_period',
                        'Check the time difference between now and last failed attempt. Failure attempts 
                        within the time difference are recorded. A bigger value 
                        of this filed means more strict. (unit: second)'
                    );
                    ?>
                </p>
            </td>
        </tr>
        <tr>
            <td class="r1"><?php _e('panel', 'setting_label_record_attempt_reset', 'Reset'); ?></td>
            <td class="r2">
                <input type="text" 
                    name="record_attempt__time_to_reset" 
                    class="form-control form-control-sm col-sm-3" 
                    value="<?php $this->_('record_attempt.time_to_reset', 1800); ?>" 
                />
                <br />
                <p>
                    <?php _e('panel', 'setting_desc_record_attempt_reset', 'Reset counter after n seconds.'); ?>
                </p>
            </td>
        </tr>
    </table>
</div>
<div class="section-body my-0">
    <table class="setting-table">
        <tr>
            <td class="r1">
                <?php _e('panel', 'messenger_label_event_1', 'Ban user in current data cycle.'); ?>
            </td>
            <td class="r2">
                <p>
                    <?php
                    _e(
                        'panel',
                        'messenger_desc_event_1',
                        'This event is typically triggered when a user fails too many CAPTCHAs in a row.'
                    );
                    ?>
                </p>
                <label class="rocker rocker-sm">
                    <input type="hidden"name="events__failed_attempts_in_a_row__data_circle__enable" value="off" />
                    <input type="checkbox" 
                        name="events__failed_attempts_in_a_row__data_circle__enable" 
                        class="toggle-block" 
                        value="on" 
                        <?php $this->checked('events.failed_attempts_in_a_row.data_circle.enable', true); ?>
                    />
                    <span class="switch-left"><i>ON</i></span>
                    <span class="switch-right"><i>OFF</i></span>
                </label>
            </td>
        </tr>
        <tr>
            <td class="r1">
                <?php _e('panel', 'setting_label_deny_attempt_buffer', 'Buffer'); ?>
            </td>
            <td class="r2">
                 <input type="text"
                    name="events__failed_attempts_in_a_row__data_circle__buffer"
                    class="form-control form-control-sm col-sm-3"
                    value="<?php $this->_('events.failed_attempts_in_a_row.data_circle.buffer'); ?>"
                />
                <br />
                <p>
                    <?php
                    _e(
                        'panel',
                        'setting_desc_deny_attempt_buffer',
                        'How many fails in a row will trigger this event.'
                    );
                    ?>
                </p>
            </td>
        </tr>
    </table>
</div>
<div class="section-body my-0">
    <table class="setting-table">
        <tr>
            <td class="r1">
                <?php _e('panel', 'messenger_label_event_2', 'Permanently ban user in system firewall'); ?>
            </td>
            <td class="r2">
                <p>
                    <?php
                    _e(
                        'panel',
                        'messenger_desc_event_2',
                        'This event is typically triggered when a user is already banned in the current data cycle, 
                        but they still access the warning pages too many times in a row - 
                        we can conclude that they are malicious bots.'
                    );
                    ?>
                    <br />
                    <?php
                    _e(
                        'panel',
                        'messenger_desc_event_3',
                        'This option will take effect only when you have set the system-layer firewall correctly.'
                    );
                    ?>
                </p>
                <label class="rocker rocker-sm">
                    <input type="hidden" name="events__failed_attempts_in_a_row__system_firewall__enable" value="off" />
                    <input type="checkbox"
                        name="events__failed_attempts_in_a_row__system_firewall__enable"
                        class="toggle-block" value="on"
                        <?php $this->checked('events.failed_attempts_in_a_row.system_firewall.enable', true); ?>
                    />
                    <span class="switch-left"><i>ON</i></span>
                    <span class="switch-right"><i>OFF</i></span>
                </label>
            </td>
        </tr>
        <tr>
            <td class="r1">
                <?php _e('panel', 'setting_label_deny_attempt_buffer', 'Buffer'); ?>
            </td>
            <td class="r2">
                <input type="text"
                    name="events__failed_attempts_in_a_row__system_firewall__buffer"
                    class="form-control form-control-sm col-sm-3"
                    value="<?php $this->_('events.failed_attempts_in_a_row.system_firewall.buffer'); ?>"
                />
                <br />
                <p>
                    <?php
                    _e(
                        'panel',
                        'setting_desc_deny_attempt_buffer',
                        'How many fails in a row will trigger this event.'
                    );
                    ?>
                </p>
            </td>
        </tr>
    </table>
</div>