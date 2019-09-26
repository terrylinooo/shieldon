<?php
/*
 * This file is part of the Shieldon package.
 *
 * (c) Terry L. <contact@terryl.in>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 * 
 * Notice: This file is a simple file for configuration, copy this file
 * to your framework's config directory.
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
    | This is a basic protection. Please change the user and password instead 
    | of a complex and strong one.
    |
    */

    'admin' => [
        'user' => 'shieldon_user',
        'pass' => '$2y$10$x/celAC.L8xBn1UPPq619uG6ZGKoA6yfbjxtAJqAAtB.yLjU3S3Fu',
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
            'enable' => true,
            'config' => [
                'directory_path' => '',
            ],
        ],

        // Data driver: File system.
        'mysql' => [
            'enable' => true,
            'config' => [
                'host'    => '127.0.0.1',
                'dbname'  => 'shieldon_db',
                'user'    => 'shieldon_user',
                'pass'    => '1234',
                'charset' => 'utf8',
            ],
        ],

        // Data driver: SQLite.
        'sqlite' => [
            'enable' => true,
            'config' => [
                'directory_path' => '',
            ],
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
                'cookie_name'   => 'ssjd',
                'cookie_domain' => '',
                'cookie_value'  => '1',
                'quota'         => 5,
            ],
        ],

        // Session filter.
        'session' => [
            'enable' => false,
            'config' => [
                'quota'       => 5,
                'time_buffer' => 5,
            ],
        ],

        // Referer filter.
        'referer' => [
            'enable' => false,
            'config' => [
                'quota'       => 5,
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
            'enable'      => true,
            'strict_mode' => false,
        ],

        // Header filter.
        'header' => [
            'enable'      => true,
            'strict_mode' => false,
        ],

        // User-agent filter.
        'user_agent' => [
            'enable'      => true,
            'strict_mode' => false,
        ],

        // RDNS filter.
        'rdns' => [
            'enable'      => true,
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
                'site_key'   => null,
                'secret_key' => null,
                'version'    => 'v2',
                'lang'       => 'en-US'
            ],
        ],

        // A very simple image CAPTCHA.
        'image' => [
            'enable' => false,
            'config' => [
                'type'   =>  'alnum', // // alnum, alpha, numeric
                'length' => 4
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
        'REMOTE_ADDR'           => true,
        'HTTP_CF_CONNECTING_IP' =>  false,
        'HTTP_X_FORWARDED_FOR'  =>  false,
        'HTTP_X_FORWARDED_HOST' =>  false
    ],

    /*
    |--------------------------------------------------------------------------
    | Online Session Limit
    |--------------------------------------------------------------------------
    |
    | When the online user amount has reached the limit, other users not in the 
    | queue have to line up!
    |
    */

    'online_session_limit' => [
        'enable' => false,
        'config' => [
            'count'  =>  100,
            'period' => 300,
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
        'post'        => false,
        'get'         => false,
        'cookie'      => false,
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
                'type' => 'shieldon' // shieldon, iptable, utw (Ubuntu firewall).
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
            'expire'        => 7200,
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
                'period'      => 86400,
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
            'url'  => '/',
            'rule' => 'allow',
            'ip'   => '127.0.0.1',
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

];