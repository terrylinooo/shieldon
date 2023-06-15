<?php
/*
 * This file is part of the Shieldon package.
 *
 * (c) Terry L. <contact@terryl.in>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * Notice: This file is a sample file for configuration.
 */

return [

    /*
    |--------------------------------------------------------------------------
    | Deamon
    |--------------------------------------------------------------------------
    |
    | Setting true to this value to start Shieldon running on the background.
    | Every HTTP request will be analyzed.
    |
    */

    'daemon' => false,

    /*
    |--------------------------------------------------------------------------
    | Firewall Administor
    |--------------------------------------------------------------------------
    |
    | The users who can login Shieldon Firewall's control panel.
    | Deault: shieldon_user / shieldon_pass
    | This is a basic protection. Please change the user and password.
    |
    */

    'admin' => [
        'user' => 'shieldon_user',
        'pass' => 'shieldon_pass',
        'last_modified' => '2020-02-05',
    ],

    /*
    |--------------------------------------------------------------------------
    | Channel Id
    |--------------------------------------------------------------------------
    |
    | If you would like use mutliple Shieldon, specifying a Channel Id here.
    |
    */

    'channel_id' => '',

    /*
    |--------------------------------------------------------------------------
    | Driver Type
    |--------------------------------------------------------------------------
    |
    | The type of the Data driver
    |
    */

    'driver_type' => 'file',

    /*
    |--------------------------------------------------------------------------
    | Drivers
    |--------------------------------------------------------------------------
    |
    | The setting details of the data drivers.
    |
    */

    'drivers' => [

        // Data driver: File system.
        'file' => [
            'directory_path' => '',
        ],

        // Data driver: File system.
        'mysql' => [
            'host'    => '127.0.0.1',
            'dbname'  => 'shieldon_db',
            'user'    => 'shieldon_user',
            'pass'    => '1234',
            'charset' => 'utf8',
        ],

        // Data driver: SQLite.
        'sqlite' => [
            'directory_path' => '',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Loggers
    |--------------------------------------------------------------------------
    |
    | Logging data to the logs files then we can parse them to visual charts.
    | Currently, we provide only Action Logger now.
    |
    */

    'loggers' => [
        'action' => [
            'enable' => false,
            'config' => [
                'directory_path' => '',
            ],
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Filters
    |--------------------------------------------------------------------------
    |
    | Filters are the soft rule-sets to detect bad-behavior requests then we
    | can "temporarily" ban them. (Unbannend by solving CAPTCHA by themselves.)
    |
    */

    'filters' => [

        // Fequency filter.
        'frequency' => [
            'enable' => false,
            'config' => [
                'quota_s' => 2,
                'quota_m' => 10,
                'quota_h' => 30,
                'quota_d' => 60,
            ],
        ],

        // JavaScript cookie filter.
        'cookie' => [
            'enable' => false,
            'config' => [
                'cookie_name' => 'ssjd',
                'cookie_domain' => '',
                'cookie_value' => '1',
                'quota' => 5,
            ],
        ],

        // Session filter.
        'session' => [
            'enable' => false,
            'config' => [
                'quota' => 5,
                'time_buffer' => 5,
            ],
        ],

        // Referer filter.
        'referer' => [
            'enable' => false,
            'config' => [
                'quota' => 5,
                'time_buffer' => 5,
            ],
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Components
    |--------------------------------------------------------------------------
    |
    | Components are the hard rule-sets to detect bad-behavior requests then we
    | can "permanently" ban them. (No CAPTCHA shows.)
    |
    | Each component provides its public APIs for further control.
    |
    */

    'components' => [

        // IP component.
        'ip' => [
            'enable' => true,
        ],

        // Trusted-bot component.
        'trusted_bot' => [
            'enable' => true,
            'strict_mode'  => false,
            'allowed_list' => [],
        ],

        // Header filter.
        'header' => [
            'enable' => true,
            'strict_mode' => false,
        ],

        // User-agent filter.
        'user_agent' => [
            'enable' => true,
            'strict_mode' => false,
        ],

        // RDNS filter.
        'rdns' => [
            'enable' => true,
            'strict_mode' => false,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | CAPTCHA Modules
    |--------------------------------------------------------------------------
    |
    | CAPTCHA modules provide its way to unban users.
    |
    */

    'captcha_modules' => [

        // Google reCAPTCHA.
        'recaptcha' => [
            'enable' => false,
            'config' => [
                'site_key' => null,
                'secret_key' => null,
                'version' => 'v2',
                'lang' => 'en-US'
            ],
        ],

        // A very simple image CAPTCHA.
        'image' => [
            'enable' => false,
            'config' => [
                'type' => 'alnum', // // alnum, alpha, numeric
                'length' => 4,
            ],
        ],
    ],
    
    /*
    |--------------------------------------------------------------------------
    | IP Variable Source
    |--------------------------------------------------------------------------
    |
    | Picking up the real IP source is a must if you use CDN service on frontend.
    |
    */

    'ip_variable_source' => [
        'REMOTE_ADDR' => true,
        'HTTP_CF_CONNECTING_IP' => false,
        'HTTP_X_FORWARDED_FOR' => false,
        'HTTP_X_FORWARDED_HOST' => false,
    ],

    /*
    |--------------------------------------------------------------------------
    | Online Session Limit
    |--------------------------------------------------------------------------
    |
    | When the Online user count has reached the limit, other users not in the
    | queue have to line up!
    |
    */

    'online_session_limit' => [
        'enable' => false,
        'config' => [
            'count' => 100,
            'period' => 300,
            'unique_only' => false,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | XSS protection.
    |--------------------------------------------------------------------------
    |
    | Googling "XSS" to understand who it is and how can we do.
    | This feature is still under development. Give us suggestions.
    |
    */

    'xss_protection' => [
        'post' => false,
        'get' => false,
        'cookie' => false,
    ],

    'xss_protected_list' => [
        [
            'type' => 'get',
            'variable' => '_test',
        ],

        [
            'type' => 'post',
            'variable' => '_test',
        ],

        [
            'type' => 'cookie',
            'variable' => '_test',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | DDOS protection.
    |--------------------------------------------------------------------------
    |
    | Block users permanently while they never solve any CAPTCHA many times.
    | You can block they by Shieldon or by IPTABLE, UTW, etc...
    |
    |   iptables -A INPUT -s %s -j DROP
    |   ufw deny from %s to any
    |
    */

    'ddos_protection' => [
        'enable' => false,
        'config' => [
            'attacks' => 10,
            'handler' => [
                'type' => 'shieldon', // shieldon, iptable, utw (Ubuntu firewall).
            ],
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | CSRF protection. (Not ready.)
    |--------------------------------------------------------------------------
    |
    | Googling "CSRF" to understand who it is and how can we do.
    | This feature is still under development. Give us suggestions.
    |
    */

    'csrf_protection' => [
        'enable' => true,
        'config' => [
            'expire' => 7200,
            'excluded_urls' => [
                [
                    'url' => '/ajax/',
                ],
            ],
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Cronjob
    |--------------------------------------------------------------------------
    |
    | Shieldon's CRON is triggered by HTTP request, not real system CRON.
    |
    */

    'cronjob' => [

        'reset_circle' => [
            'enable' => true,
            'config' => [
                'period'  => 86400,
                'last_update' => '2019-01-01 00:00:00',
            ],
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Excluded URLs
    |--------------------------------------------------------------------------
    |
    | Shieldon will ignore URLs listed blew.
    |
    */

    'excluded_urls' => [
        [
            'url' => '/tests/',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | IP Manager
    |--------------------------------------------------------------------------
    |
    | IP manager is provided by IP component.
    | Please notice that it will match all URLs begin with the url fields.
    |
    */

    'ip_manager' => [
        [
            'url' => '/',
            'rule' => 'allow',
            'ip' => '127.0.0.1',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | WWW-Authenticate
    |--------------------------------------------------------------------------
    |
    | Basic authenticate. For more detail:
    | https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/WWW-Authenticate
    |
    */

    'www_authenticate' => [
        [
            'url'  => '/wp-admin',
            'user' => 'wp_shieldon_admin',
            'pass' => '$2y$10$eA/S6rH3JDkYV9nrrUvuMOTh8Q/ts33DdCerbNAUpdwtSl3Xq9cQq',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Dialog UI
    |--------------------------------------------------------------------------
    */

    'dialog_ui' => [
        'lang' => 'en',
        'background_image' => '',
        'bg_color' => '#ffffff',
        'header_bg_color' => '#212531',
        'header_color' => '#ffffff',
        'shadow_opacity' => '0.2',
    ],

    'dialog_info_disclosure' => [
        'user_inforamtion' => false,
        'http_status_code' => false,
        'reason_code' => false,
        'reason_text' => false,
        'online_user_amount' => false,
    ],

    /*
    |--------------------------------------------------------------------------
    | Messenger
    |--------------------------------------------------------------------------
    |
    | Docs: https://github.com/terrylinooo/messenger
    |
    | The `confirm_test` value is must be True to execute a messener module.
    | Test the configuration before sending a message.
    |--------------------------------------------------------------------------
    */

    'messengers' => [

        'line_notify' => [
            'enable' => false,
            'config' => [
                'access_token' => 'your_access_token',
            ],
            'confirm_test' => false,
        ],

        'telegram' => [
            'enable' => false,
            'config' => [
                'api_key' => 'your_api_key',
            ],
            'confirm_test' => false,
        ],

        'slack' => [
            'enable' => false,
            'config' => [
                'bot_token' => 'your_bot_token',
                'channel'   => 'your_channel',
            ],
            'confirm_test' => false,
        ],

        'slack_webhook' => [
            'enable' => false,
            'config' => [
                'webhook_url' => 'your_webhook_url',
            ],
            'confirm_test' => false,
        ],

        'rocket_chat' => [
            'enable' => false,
            'config' => [
                'server_url' => 'your_server_url',
                'user_id' => 'your_rocketchat_user_id',
                'access_token' => 'your_accress_token',
                'channel' => 'your_channel',
            ],
            'confirm_test' => false,
        ],

        'sendgrid' => [
            'enable' => false,
            'config' => [
                'api_key' => 'your_api_key',
                'sender' => 'your@email.com',
                'recipients' => [
                    'user1@email.com',
                    'user2@email.com',
                ],
            ],
            'confirm_test' => false,
        ],

        'mailgun' => [
            'enable' => false,
            'config' => [
                'api_key' => 'your_api_key',
                'domain_name' => 'your_domain.com',
                'sender' => 'your@email.com',
                'recipients' => [
                    'user1@email.com',
                    'user2@email.com',
                ]
            ],
            'confirm_test' => false,
        ],

        'native_php_mail' => [
            'enable' => false,
            'config' => [
                'sender' => 'your@email.com',
                'recipients' => [
                    'user1@email.com',
                    'user2@email.com',
                ],
            ],
            'confirm_test' => false,
        ],

        'native_php_mail' => [
            'enable' => false,
            'config' => [
                'sender' => 'your@email.com',
                'recipients' => [
                    'user1@email.com',
                    'user2@email.com',
                ],
            ],
            'confirm_test' => false,
        ],

        'smtp' => [
            'enable' => false,
            'config' => [
                'host' => "127.0.0.1",
                'port' => 25,
                'type' => '', // null, ssl, tls
                'user' => '',
                'pass' => '',
                'sender' => 'your@email.com',
                'recipients' => [
                    'user1@email.com',
                    'user2@email.com',
                ],
            ],
            'confirm_test' => false,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Events
    |--------------------------------------------------------------------------
    */

    'events' => [
        'failed_attempts_in_a_row' => [
            'data_circle' => [
                'enable' => true,
                'messenger' => true,
                'buffer' => 10,
            ],
            'system_firewall' => [
                'enable' => true,
                'messenger' => true,
                'buffer' => 10,
            ],
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Checking the last failure time
    |--------------------------------------------------------------------------
    */

    'record_attempt' => [
        'detection_period' => 5,
        'time_to_reset' => 1800,
    ],

    'check_last_failed_attempt_time' => 5,

    /*
    |--------------------------------------------------------------------------
    | Firewall - iptables
    |--------------------------------------------------------------------------
    */

    'iptables' => [
        'enable' => false,
        'config' => [
            'watching_folder' => '',
        ],
    ],

    'ip6tables' => [
        'enable' => false,
        'config' => [
            'watching_folder' => '',
        ],
    ],
];
