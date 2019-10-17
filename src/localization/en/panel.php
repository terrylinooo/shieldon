<?php

return [
    'reason_manual_ban' => 'Added manually by administrator',
    'reason_is_search_engine' => 'Search engine bot',
    'reason_is_google' => 'Google bot',
    'reason_is_bing' => 'Bing bot',
    'reason_is_yahoo' => 'Yahoo bot',
    'reason_too_many_sessions' => 'Too many sessions',
    'reason_too_many_accesses' => 'Too many accesses',
    'reason_empty_js_cookie' => 'Cannot create JS cookies',
    'reason_empty_referer' => 'Empty referrer',
    'reason_reached_limit_day' => 'Daily limit reached',
    'reason_reached_limit_hour' => 'Hourly limit reached',
    'reason_reached_limit_minute' => 'Minutely limit reached',
    'reason_reached_limit_second' => 'Secondly limit reached',

    // Message
    'error_mysql_connection' => 'Cannot access to your MySQL database, please check your settings.',
    'error_mysql_driver_not_supported' => 'Your system doesn’t support MySQL driver.',
    'error_sqlite_driver_not_supported' => 'Your system doesn’t support SQLite driver.',
    'error_sqlite_directory_not_writable' => 'SQLite data driver requires the storage directory writable.',
    'error_redis_driver_not_supported' => 'Your system doesn’t support Redis driver.',
    'error_file_directory_not_writable' => 'File data driver requires the storage directory writable.',
    'error_logger_directory_not_writable' => 'Action Logger requires the storage directory writable.',
    'success_settings_saved' => 'Settings saved',

    // Others.
    'field_not_visible' => 'Cannot view this field in demo mode.',
    'permission_required' => 'Permission required.',

    // Header status bar.
    'channel' => 'Channel',
    'mode' => 'Mode',
    'logout' => 'Logout',

    // Setting - authentication page.
    'auth_heading' => 'Authentication',
    'auth_description' => 'The HTTP WWW-Authenticate response header defines the authentication method that should be used to gain access to a resource.',
    'auth_label_url_path' => 'URL Path',
    'auth_label_username' => 'Username',
    'auth_label_password' => 'Password',
    'auth_btn_submit' => 'Submit',
    'auth_label_encrypted' => 'encrypted',
    'auth_label_remove' => 'Remove',

    // Setting - exclusion page.
    'excl_heading' => 'Exclusion',
    'excl_description' => 'Please enter the begin with URLs you want them excluded from Shieldon protection.',

    // IP Manager
    'ipma_heading' => 'IP Manager',
    'ipma_description' => 'IP Manager is not like Rule Table (effective period depends on the data cycle), everything you have done here is permanent.',
    'ipma_label_ip' => 'IP',
    'ipma_label_order' => 'Order',
    'ipma_label_rule' => 'Rule',
    'ipma_label_action' => 'Action',
    'ipma_label_plz_select' => 'Please select',
    'ipma_label_remove_ip' => 'Remove this IP',
    'ipma_label_allow_ip' => 'Allow this IP',
    'ipma_label_deny_ip' => 'Deny this IP',

    // Log
    'log_heading_captchas' => 'CAPTCHAs',
    'log_note_captcha_last_month' => 'CAPTCHA statistic last month',
    'log_heading_pageviews' => 'Pageviews',
    'log_note_pageview_last_month' => 'Total pageviews last month',
    'log_label_last_month' => 'Last month',
    'log_label_this_month' => 'This month',
    'log_label_last_7_days' => 'Last 7 days',
    'log_label_yesterday' => 'Yesterday',
    'log_label_today' => 'Today',
    'log_msg_no_logger' => 'Sorry, you have to implement ActionLogger to use this function.',
    'log_label_in_queue' => 'In queue',
    'log_label_in_blacklist' => 'In blacklist',
    'log_label_captcha' => 'CAPTCHA',
    'log_label_pageviews' => 'Pageviews',
    'log_label_session' => 'Sessions',
    'log_label_solved' => 'solved',
    'log_label_failed' => 'failed',
    'log_label_displays' => 'displays',
    'log_label_timezone' => 'Timezone',
    'log_note_captcha_last_7_days'
];
