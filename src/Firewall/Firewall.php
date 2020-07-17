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

namespace Shieldon\Firewall;

use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Server\MiddlewareInterface;
use Shieldon\Firewall\Kernel;
use Shieldon\Firewall\Captcha as Captcha;
use Shieldon\Firewall\Component as Component;
use Shieldon\Firewall\Driver as Driver;
use Shieldon\Firewall\Middleware as Middleware;
use Shieldon\Firewall\Security as Security;
use Shieldon\Messenger as Messenger;
use Shieldon\Firewall\Utils\Container;
use Shieldon\Firewall\Log\ActionLogger;
use Shieldon\Firewall\FirewallTrait;
use Shieldon\Psr15\RequestHandler;
use function Shieldon\Firewall\get_request;
use function Shieldon\Firewall\get_response;

use PDO;
use PDOException;
use Redis;
use RedisException;

use function array_column;
use function defined;
use function file_exists;
use function file_get_contents;
use function file_put_contents;
use function is_dir;
use function json_decode;
use function json_encode;
use function mkdir;
use function rtrim;
use function strpos;
use function umask;
use function time;
use function strtotime;
use function date;

/**
 * Managed Firewall.
 */
class Firewall
{
    use FirewallTrait;

    /**
     * Collection of PSR-7 or PSR-15 middlewares.
     *
     * @var array
     */
    protected $middleware = [];

    /**
     * Constructor.
     */
    public function __construct(?ServerRequestInterface $request = null, ?ResponseInterface $response = null)
    {
        Container::set('firewall', $this);

        $this->kernel = new Kernel($request, $response);
    }

    /**
     * Set up the path of the configuration file.
     *
     * @param string $source The path.
     * @param string $type   The type.
     * 
     * @return void
     */
    public function configure(string $source, string $type = 'json')
    {
        if ($type === 'json') {
            $this->directory = rtrim($source, '\\/');
            $configFilePath = $this->directory . '/' . $this->filename;

            if (file_exists($configFilePath)) {
                $jsonString = file_get_contents($configFilePath);

            } else {
                $jsonString = file_get_contents(__DIR__ . '/../../config.json');

                if (defined('PHP_UNIT_TEST')) {
                    $jsonString = file_get_contents(__DIR__ . '/../../tests/config.json');
                }
            }

            $this->configuration = json_decode($jsonString, true);
            $this->kernel->managedBy('managed');

        } elseif ($type === 'php') {
            $this->configuration = require $source;
            $this->kernel->managedBy('config');
        }

        $this->setup();
    }

    /**
     * Add middlewares and use them before executing Shieldon kernal.
     *
     * @param array $middleware Array of PSR-7 or PSR-15 middlewares.
     *
     * @return void
     */
    public function add($middleware)
    {
        if ($middleware instanceof MiddlewareInterface) {
            $this->middleware[] = $middleware;
        }
    }

    /**
     * Setup everything we need.
     *
     * @return void
     */
    public function setup(): void
    {
        $this->status = $this->getOption('daemon');

        $this->setDriver();

        $this->setChannel();

        $this->setIpSource();

        $this->setLogger();

        $this->setFilters();

        $this->setComponents();

        $this->setCaptchas();

        $this->setSessionLimit();

        $this->setCronJob();

        $this->setExcludedUrls();

        $this->setXssProtection();

        $this->setAuthentication();

        $this->setDialogUI();

        $this->setMessengers();

        $this->setMessageEvents();

        $this->setDenyAttempts();

        $this->setIptablesWatchingFolder();
    }

    /**
     * Just, run!
     *
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function run(): ResponseInterface
    {
        // If settings are ready, let's start monitoring requests.
        if ($this->status) {

            $response = get_request();

            // PSR-15 request handler.
            $requestHandler = new RequestHandler();

            foreach ($this->middleware as $middleware) {
                $requestHandler->add($middleware);
            }

            $response = $requestHandler->handle($response);

            // Something is detected by Middlewares, return.
            if ($response->getStatusCode() !== 200) {
                return $response;
            }

            $result = $this->kernel->run();

            if ($result !== $this->kernel::RESPONSE_ALLOW) {

                if ($this->kernel->captchaResponse()) {
                    $this->kernel->unban();

                    $response = $response->withHeader('Location', $this->kernel->getCurrentUrl());
                    $response = $response->withStatus(303);

                    return $response;
                }
            }
        }

        return $this->kernel->respond();
    }

    /**
     * Set the channel ID.
     *
     * @return void
     */
    protected function setChannel(): void
    {
        $channelId = $this->getOption('channel_id');

        if ($channelId) {
            $this->kernel->setChannel($channelId);
        }
    }

    /**
     * Set a data driver for Shieldon use.
     *
     * @return void
     */
    protected function setDriver(): void
    {
        $driverType = $this->getOption('driver_type');

        switch ($driverType) {

            case 'redis':
            
                $redisSetting = $this->getOption('redis', 'drivers');

                try {

                    $host = '127.0.0.1';
                    $port = 6379;

                    if (!empty($redisSetting['host'])) {
                        $host = $redisSetting['host'];
                    }

                    if (!empty($redisSetting['port'])) {
                        $port = $redisSetting['port'];
                    }

                    // Create a Redis instance.
                    $redis = new Redis();
                    $redis->connect($host, $port);

                    if (!empty($redisSetting['auth'])) {

                        // @codeCoverageIgnoreStart
                        $redis->auth($redisSetting['auth']);
                        // @codeCoverageIgnoreEnd
                    }

                    // Use Redis data driver.
                    $this->kernel->add(new Driver\RedisDriver($redis));

                // @codeCoverageIgnoreStart
                } catch(RedisException $e) {
                    $this->status = false;

                    echo $e->getMessage();
                }
                // @codeCoverageIgnoreEnd

                break;

            case 'file':
            
                $fileSetting = $this->getOption('file', 'drivers');

                if (empty($fileSetting['directory_path'])) {
                    $fileSetting['directory_path'] = $this->directory;
                }

                // Use File data driver.
                $this->kernel->add(new Driver\FileDriver($fileSetting['directory_path']));

                break;

            case 'sqlite':
            
                $sqliteSetting = $this->getOption('sqlite', 'drivers');

                if (empty($sqliteSetting['directory_path'])) {
                    $sqliteSetting['directory_path'] = '';
                    $this->status = false;
                }

                try {
                    
                    // Specific the sqlite file location.
                    $sqliteLocation = $sqliteSetting['directory_path'] . '/shieldon.sqlite3';

                    // Create a PDO instance.
                    $pdoInstance = new PDO('sqlite:' . $sqliteLocation);

                    // Use Sqlite data driver.
                    $this->kernel->add(new Driver\SqliteDriver($pdoInstance));
    
                // @codeCoverageIgnoreStart
                } catch(PDOException $e) {
                    $this->status = false;

                    echo $e->getMessage();
                }
                // @codeCoverageIgnoreEnd

                break;

            case 'mysql':
            default:

                $mysqlSetting = $this->getOption('mysql', 'drivers');

                try {

                    // Create a PDO instance.
                    $pdoInstance = new PDO(
                        'mysql:host=' 
                            . $mysqlSetting['host']   . ';dbname=' 
                            . $mysqlSetting['dbname'] . ';charset=' 
                            . $mysqlSetting['charset']
                        , (string) $mysqlSetting['user']
                        , (string) $mysqlSetting['pass']
                    );

                    // Use MySQL data driver.
                    $this->kernel->add(new Driver\MysqlDriver($pdoInstance));

                // @codeCoverageIgnoreStart
                } catch(PDOException $e) {
                    echo $e->getMessage();
                }
                // @codeCoverageIgnoreEnd
            // end switch.
        }
    }

    /**
     * Set up the action logger.
     *
     * @return void
     */
    protected function setLogger(): void
    {
        $loggerSetting = $this->getOption('action', 'loggers');

        if ($loggerSetting['enable']) {
            if (!empty($loggerSetting['config']['directory_path'])) {
                $this->kernel->add(new ActionLogger($loggerSetting['config']['directory_path']));
            }
        }
    }

    /**
     * If you use CDN, please choose the real IP source.
     *
     * @return void
     */
    protected function setIpSource(): void
    {
        $ipSourceType = $this->getOption('ip_variable_source');
        $serverParams = get_request()->getServerParams();

        if ($ipSourceType['REMOTE_ADDR']) {
            $ip = $serverParams['REMOTE_ADDR'];

        // Cloudflare
        } elseif ($ipSourceType['HTTP_CF_CONNECTING_IP']) {
            $ip = $serverParams['HTTP_CF_CONNECTING_IP'];

        // Google Cloud CDN, Google Load-balancer, AWS.
        } elseif ($ipSourceType['HTTP_X_FORWARDED_FOR']) {
            $ip = $serverParams['HTTP_X_FORWARDED_FOR'];

        // KeyCDN, or other CDN providers not listed here.
        } elseif ($ipSourceType['HTTP_X_FORWARDED_HOST']) {
            $ip = $serverParams['HTTP_X_FORWARDED_HOST'];

        // Fallback.
        } else {

            // @codeCoverageIgnoreStart
            $ip = $serverParams['REMOTE_ADDR'];
            // @codeCoverageIgnoreEnd
        }

        if (empty($ip)) {
            // @codeCoverageIgnoreStart
            throw new RuntimeException('IP source is not set correctly.');
            // @codeCoverageIgnoreEnd
        }

        $this->kernel->setIp($ip);
    }

    /**
     * Set the filiters.
     *
     * @return void
     */
    protected function setFilters(): void
    {
        $sessionSetting = $this->getOption('session', 'filters');
        $cookieSetting = $this->getOption('cookie', 'filters');
        $refererSetting = $this->getOption('referer', 'filters');
        $frequencySetting = $this->getOption('frequency', 'filters');

        $filterConfig = [
            'session'   => $sessionSetting['enable'],
            'cookie'    => $cookieSetting['enable'],
            'referer'   => $refererSetting['enable'],
            'frequency' => $frequencySetting['enable'],
        ];

        $this->kernel->setFilters($filterConfig);

        $this->kernel->setProperty('limit_unusual_behavior', [
            'session' => $sessionSetting['config']['quota'] ?? 5,
            'cookie'  => $cookieSetting['config']['quota'] ?? 5,
            'referer' => $refererSetting['config']['quota'] ?? 5,
        ]);

        if ($frequencySetting['enable']) {

            $frequencyQuota = [
                's' => $frequencySetting['config']['quota_s'] ?? 2,
                'm' => $frequencySetting['config']['quota_m'] ?? 10,
                'h' => $frequencySetting['config']['quota_h'] ?? 30,
                'd' => $frequencySetting['config']['quota_d'] ?? 60,
            ];

            $this->kernel->setProperty('time_unit_quota', $frequencyQuota);
        }

        if ($cookieSetting['enable']) {

            $cookieName = $cookieSetting['config']['cookie_name'] ?? 'ssjd';
            $cookieDomain = $cookieSetting['config']['cookie_domain'] ?? '';
            $cookieValue = $cookieSetting['config']['cookie_value'] ?? '1';
    
            $this->kernel->setProperty('cookie_name', $cookieName);
            $this->kernel->setProperty('cookie_domain', $cookieDomain);
            $this->kernel->setProperty('cookie_value', $cookieValue);
        }

        if ($refererSetting['enable']) {
            $this->kernel->setProperty('interval_check_referer', $refererSetting['config']['time_buffer']);
        }

        if ($sessionSetting['enable']) {
            $this->kernel->setProperty('interval_check_session', $sessionSetting['config']['time_buffer']);
        }
    }

    /**
     * Set the components.
     *
     * @return void
     */
    protected function setComponents(): void
    {
        $ipSetting = $this->getOption('ip', 'components');
        $rdnsSetting = $this->getOption('rdns', 'components');
        $headerSetting = $this->getOption('header', 'components');
        $userAgentSetting = $this->getOption('user_agent', 'components');
        $trustedBotSetting = $this->getOption('trusted_bot', 'components');

        if ($ipSetting['enable']) {
            $componentIp = new Component\Ip();
            $this->kernel->add($componentIp);
            $this->ipManager();
        }

        if ($trustedBotSetting['enable']) {
            $componentTrustedBot = new Component\TrustedBot();

            if ($trustedBotSetting['strict_mode']) {
                $componentTrustedBot->setStrict(true);
            }

            // This component will only allow popular search engline.
            // Other bots will go into the checking process.
            $this->kernel->add($componentTrustedBot);
        }

        if ($headerSetting['enable']) {
            $componentHeader = new Component\Header();

            // Deny all vistors without common header information.
            if ($headerSetting['strict_mode']) {
                $componentHeader->setStrict(true);
            }

            $this->kernel->add($componentHeader);
        }

        if ($userAgentSetting['enable']) {
            $componentUserAgent = new Component\UserAgent();

            // Deny all vistors without user-agent information.
            if ($userAgentSetting['strict_mode']) {
                $componentUserAgent->setStrict(true);
            }

            $this->kernel->add($componentUserAgent);
        }

        if ($rdnsSetting['enable']) {
            $componentRdns = new Component\Rdns();

            // Visitors with empty RDNS record will be blocked.
            // IP resolved hostname (RDNS) and IP address must conform with each other.
            if ($rdnsSetting['strict_mode']) {
                $componentRdns->setStrict(true);
            }

            $this->kernel->add($componentRdns);
        }
    }

    /**
     * Set the Captcha modules.
     *
     * @return void
     */
    protected function setCaptchas(): void
    {
        $recaptchaSetting = $this->getOption('recaptcha', 'captcha_modules');
        $imageSetting = $this->getOption('image', 'captcha_modules');

        if ($recaptchaSetting['enable']) {

            $googleRecaptcha = [
                'key'     => $recaptchaSetting['config']['site_key'],
                'secret'  => $recaptchaSetting['config']['secret_key'],
                'version' => $recaptchaSetting['config']['version'],
                'lang'    => $recaptchaSetting['config']['lang'],
            ];

            $this->kernel->add(new Captcha\Recaptcha($googleRecaptcha));
        }

        if ($imageSetting['enable']) {

            $type = $imageSetting['config']['type'] ?? 'alnum';
            $length = $imageSetting['config']['length'] ?? 8;

            switch ($type) {
                case 'numeric':
                    $imageCaptchaConfig['pool'] = '0123456789';
                    break;

                case 'alpha':
                    $imageCaptchaConfig['pool'] = '0123456789abcdefghijklmnopqrstuvwxyz';
                    break;

                case 'alnum':
                default:
                    $imageCaptchaConfig['pool'] = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
            }

            $imageCaptchaConfig['word_length'] = $length;

            $this->kernel->add(new Captcha\ImageCaptcha($imageCaptchaConfig));
        }
    }

    /**
     * Covert string with dashes into camel-case string.
     *
     * @param string $string A string with dashes.
     *
     * @return string
     */
    private function getCamelCase(string $string = '')
    {
        $str = explode('-', $string);
        $str = implode('', array_map(function($word) {
            return ucwords($word); 
        }, $str));

        return $str;
    }

    /**
     * Set the messenger modules.
     *
     * @return void
     */
    protected function setMessengers(): void
    {
        $messengerList = [
            'telegram',
            'line_notify',
            'sendgrid',
            'native_php_mail',
            'smtp',
            'mailgun',
            'rocket_chat',
            'slack',
            'slack_webhook',
        ];

        foreach ($messengerList as $messenger) {
            $setting = $this->getOption($messenger, 'messengers');

            if (!empty($setting['enable']) && !empty($setting['confirm_test'])) {
                $factory = '\Shieldon\Firewall\Messenger\\' . $this->getCamelCase($messenger);

                // Initialize messenger instances from the factories.
                if ($factory::check($setting)) {
                    $this->kernel->add($factory::getInstance($setting));
                }
            }
            unset($setting);
        }

        $telegramSetting     = $this->getOption('telegram', 'messengers');
        $linenotodySetting   = $this->getOption('line_notify', 'messengers');
        $sendgridSetting     = $this->getOption('sendgrid', 'messengers');
        $phpMailSetting      = $this->getOption('native_php_mail', 'messengers');
        $smtpSetting         = $this->getOption('smtp', 'messengers');
        $mailgunSetting      = $this->getOption('mailgun', 'messengers');
        $rocketchatSetting   = $this->getOption('rocket_chat', 'messengers');
        $slackSetting        = $this->getOption('slack', 'messengers');
        $slackWebhookSetting = $this->getOption('slack_webhook', 'messengers');

        $messageTitle = 'Firewall Notification';

        if (!empty($telegramSetting['enable'])) {
            if (!empty($telegramSetting['confirm_test'])) {
                $apiKey = $telegramSetting['config']['api_key'] ?? '';
                $channel = $telegramSetting['config']['channel'] ?? '';
                $this->kernel->add(
                    new Messenger\Telegram($apiKey, $channel)
                );
            }
        }

        if (!empty($linenotodySetting['enable'])) {
            if (!empty($linenotodySetting['confirm_test'])) {
                $accessToken = $linenotodySetting['config']['access_token'] ?? '';
                $this->kernel->add(
                    new Messenger\LineNotify($accessToken)
                );
            }
        }

        if (!empty($sendgridSetting['enable'])) {
            if (!empty($sendgridSetting['confirm_test'])) {
                $apiKey = $sendgridSetting['config']['api_key'] ?? '';
                $sender = $sendgridSetting['config']['sender'] ?? '';
                $recipients = $sendgridSetting['config']['recipients'] ?? [];

                $sendgrid = new Messenger\Sendgrid($apiKey);
                $sendgrid->setSubject($messageTitle);
                $sendgrid->addSender($sender);

                foreach ($recipients as $recipient) {
                    $sendgrid->addRecipient($recipient);
                }

                $this->kernel->add($sendgrid);
            }
        }

        if (!empty($phpMailSetting['enable'])) {
            if (!empty($phpMailSetting['confirm_test'])) {
                $sender = $phpMailSetting['config']['sender'] ?? '';
                $recipients = $phpMailSetting['config']['recipients'] ?? [];

                $phpNativeMail = new Messenger\Mail();
                $phpNativeMail->setSubject($messageTitle);
                $phpNativeMail->addSender($sender);

                foreach ($recipients as $recipient) {
                    $phpNativeMail->addRecipient($recipient);
                }

                $this->kernel->add($phpNativeMail);
            }
        }

        if (!empty($smtpSetting['enable'])) {
            if (!empty($smtpSetting['confirm_test'])) {
                $sender = $smtpSetting['config']['sender'] ?? '';
                $recipients = $smtpSetting['config']['recipients'] ?? [];
                $host = $smtpSetting['config']['host'] ?? '';
                $user = $smtpSetting['config']['user'] ?? '';
                $pass = $smtpSetting['config']['pass'] ?? '';
                $port = (int) $smtpSetting['config']['port'] ?? '';

                $smtpMail = new Messenger\Smtp($user, $pass, $host, $port);
                $smtpMail->setSubject($messageTitle);
                $smtpMail->addSender($sender);

                foreach ($recipients as $recipient) {
                    $smtpMail->addRecipient($recipient);
                }

                $this->kernel->add($smtpMail);
            }
        }

        if (!empty($mailgunSetting['enable'])) {
            if (!empty($mailgunSetting['confirm_test'])) {
                $apiKey = $mailgunSetting['config']['api_key'] ?? '';
                $domain = $mailgunSetting['config']['domain_name'] ?? '';
                $sender = $mailgunSetting['config']['sender'] ?? '';
                $recipients = $mailgunSetting['config']['recipients'] ?? [];

                $mailgun = new Messenger\Mailgun($apiKey, $domain);
                $mailgun->setSubject($messageTitle);
                $mailgun->addSender($sender);

                foreach ($recipients as $recipient) {
                    $mailgun->addRecipient($recipient);
                }

                $this->kernel->add($mailgun);
            }
        }

        if (!empty($rocketchatSetting['enable'])) {
            if (!empty($rocketchatSetting['confirm_test'])) {
                $serverUrl = $rocketchatSetting['config']['server_url'] ?? '';
                $userId = $rocketchatSetting['config']['user_id'] ?? '';
                $accessToken = $rocketchatSetting['config']['access_token'] ?? '';
                $channel = $rocketchatSetting['config']['channel'] ?? [];

                $this->kernel->add(
                    new Messenger\RocketChat(
                        $accessToken, $userId, $serverUrl, $channel
                    )
                );
            }
        }

        if (!empty($slackSetting['enable'])) {
            if (!empty($slackSetting['confirm_test'])) {
                $botToken = $slackSetting['config']['bot_token'] ?? '';
                $channel = $slackSetting['config']['channel'] ?? '';

                $this->kernel->add(
                    new Messenger\Slack($botToken, $channel)
                );
            }
        }

        if (!empty($slackWebhookSetting['enable'])) {
            if (!empty($slackWebhookSetting['confirm_test'])) {
                $webhookUrl = $slackWebhookSetting['config']['webhook_url'] ?? '';

                $this->kernel->add(
                    new Messenger\SlackWebhook($webhookUrl)
                );
            }
        }
    }

    /**
     * Set message events.
     *
     * @return void
     */
    protected function setMessageEvents(): void
    {
        $eventSetting = $this->getOption('failed_attempts_in_a_row', 'events');

        $notifyDataCircle = false;
        $notifySystemFirewall = false;

        if ($eventSetting['data_circle']['messenger']) {
            $notifyDataCircle = true;
        }

        if ($eventSetting['system_firewall']['messenger']) {
            $notifyDataCircle = true;
        }

        $this->kernel->setProperty('deny_attempt_notify', [
            'data_circle' => $notifyDataCircle,
            'system_firewall' => $notifySystemFirewall,
        ]);
    }

    /**
     * Set deny attempts.
     *
     * @return void
     */
    protected function setDenyAttempts(): void
    {
        $eventSetting = $this->getOption('failed_attempts_in_a_row', 'events');

        $enableDataCircle = false;
        $enableSystemFirewall = false;

        if ($eventSetting['data_circle']['enable']) {
            $enableDataCircle = true;
        }

        if ($eventSetting['system_firewall']['enable']) {
            $enableSystemFirewall = true;
        }

        $this->kernel->setProperty('deny_attempt_enable', [
            'data_circle' => $enableDataCircle,
            'system_firewall' => $enableSystemFirewall,
        ]);

        $this->kernel->setProperty('deny_attempt_buffer', [
            'data_circle' => $eventSetting['data_circle']['buffer'] ?? 10,
            'system_firewall' => $eventSetting['data_circle']['buffer'] ?? 10,
        ]);

        // Check the time of the last failed attempt. @since 0.2.0
        $recordAttempt = $this->getOption('record_attempt');

        $detectionPeriod = $recordAttempt['detection_period'] ?? 5;
        $timeToReset = $recordAttempt['time_to_reset'] ?? 1800;

        $this->kernel->setProperty('record_attempt_detection_period', $detectionPeriod);
        $this->kernel->setProperty('reset_attempt_counter', $timeToReset);
    }

    /**
     * Set iptables working folder.
     *
     * @return void
     */
    protected function setIptablesWatchingFolder(): void
    {
        $iptablesSetting = $this->getOption('config', 'iptables');
        $this->kernel->setProperty('iptables_watching_folder',  $iptablesSetting['watching_folder']);
    }

    /**
     * Set the online session limit.
     *
     * @return void
     */
    protected function setSessionLimit(): void
    {
        $sessionLimitSetting = $this->getOption('online_session_limit');

        if ($sessionLimitSetting['enable']) {

            $onlineUsers = $sessionLimitSetting['config']['count'] ?? 100;
            $alivePeriod = $sessionLimitSetting['config']['period'] ?? 300;

            $this->kernel->limitSession($onlineUsers, $alivePeriod);
        }
    }

    /**
     * Set the cron job.
     * This is triggered by the pageviews, not system cron job.
     *
     * @return void
     */
    protected function setCronJob(): void 
    {
        $cronjobSetting = $this->getOption('reset_circle', 'cronjob');

        if ($cronjobSetting['enable']) {

            $nowTime = time();

            $lastResetTime = $cronjobSetting['config']['last_update'];

            if (!empty($lastResetTime) ) {
                $lastResetTime = strtotime($lastResetTime);
            } else {
                // @codeCoverageIgnoreStart
                $lastResetTime = strtotime(date('Y-m-d 00:00:00'));
                // @codeCoverageIgnoreEnd
            }

            if (($nowTime - $lastResetTime) > $cronjobSetting['config']['period']) {

                $updateResetTime = date('Y-m-d 00:00:00');

                // Update new reset time.
                $this->setConfig('cronjob.reset_circle.config.last_update', $updateResetTime);
                $this->updateConfig();

                // Remove all logs.
                $this->kernel->driver->rebuild();
            }
        }
    }

    /**
     * Set the URLs that want to be excluded from Shieldon protection.
     *
     * @return void
     */
    protected function setExcludedUrls(): void
    {
        $excludedUrls = $this->getOption('excluded_urls');

        if (!empty($excludedUrls)) {
            $list = array_column($excludedUrls, 'url');

            $this->kernel->setExcludedUrls($list);
        }
    }

    /**
     * Set XSS protection.
     *
     * @return void
     */
    protected function setXssProtection(): void
    {
        $xssProtectionOptions = $this->getOption('xss_protection');

        $xssFilter = new Security\Xss();

        if ($xssProtectionOptions['post']) {
            $this->kernel->setClosure('xss_post', function() use ($xssFilter) {
                if (!empty($_POST)) {
                    foreach (array_keys($_POST) as $k) {
                        $_POST[$k] = $xssFilter->clean($_POST[$k]);
                    }
                }
            });
        }

        if ($xssProtectionOptions['get']) {
            $this->kernel->setClosure('xss_get', function() use ($xssFilter) {
                if (!empty($_GET)) {
                    foreach (array_keys($_GET) as $k) {
                        $_GET[$k] = $xssFilter->clean($_GET[$k]);
                    }
                }
            });
        }

        if ($xssProtectionOptions['cookie']) {
            $this->kernel->setClosure('xss_cookie', function() use ($xssFilter) {
                if (!empty($_COOKIE)) {
                    foreach (array_keys($_COOKIE) as $k) {
                        $_COOKIE[$k] = $xssFilter->clean($_COOKIE[$k]);
                    }
                }
            });
        }

        $xssProtectedList = $this->getOption('xss_protected_list');

        if (!empty($xssProtectedList)) {
        
            $this->kernel->setClosure('xss_protection', function() use ($xssFilter, $xssProtectedList) {

                foreach ($xssProtectedList as $v) {
                    $k = $v['variable'] ?? 'undefined';
    
                    switch ($v['type']) {

                        case 'get':

                            if (!empty($_GET[$k])) {
                                $_GET[$k] = $xssFilter->clean($_GET[$k]);
                            }
                            break;
    
                        case 'post':
    
                            if (!empty($_POST[$k])) {
                                $_POST[$k] = $xssFilter->clean($_POST[$k]);
                            }
                            break;
    
                        case 'cookie':

                            if (!empty($_COOKIE[$k])) {
                                $_COOKIE[$k] = $xssFilter->clean($_COOKIE[$k]);
                            }
                            break;
    
                        default:
                    }
                }
            });
        }
    }

    /**
     * WWW-Athentication.
     *
     * @return void
     */
    protected function setAuthentication(): void
    {
        $authenticateList = $this->getOption('www_authenticate');

        $this->add(new Middleware\httpAuthentication($authenticateList));
    }

    /**
     * IP manager.
     */
    protected function ipManager()
    {
        $ipList = $this->getOption('ip_manager');

        $allowedList = [];
        $deniedList = [];

        if (!empty($ipList)) {
            foreach ($ipList as $ip) {

                if (0 === strpos($this->kernel->getCurrentUrl(), $ip['url']) ) {
    
                    if ('allow' === $ip['rule']) {
                        $allowedList[] = $ip['ip'];
                    }
    
                    if ('deny' === $ip['rule']) {
                        $deniedList[] = $ip['ip'];
                    }
                }
            }
        }

        if (!empty($allowedList)) {
            $this->kernel->component['Ip']->setAllowedItems($allowedList);
        }

        if (!empty($deniedList)) {
            $this->kernel->component['Ip']->setDeniedItems($deniedList);
        }
    }

    /**
     * Set dialog UI.
     *
     * @return void
     */
    protected function setDialogUI()
    {
        $ui = $this->getOption('dialog_ui');

        if (!empty($ui)) {
            get_session()->set('shieldon_ui_lang', $ui['lang']);
            $this->kernel->setDialogUI($this->getOption('dialog_ui'));
        }
    }

    /**
     * Get options from the configuration file.
     * 
     * This method is same as `$this->getConfig()` but returning value from array directly, 
     * saving a `explode()` process.
     *
     * @param string $option
     * @param string $section
     *
     * @return mixed
     */
    protected function getOption(string $option, string $section = '')
    {
        if (!empty($this->configuration[$section][$option])) {
            return $this->configuration[$section][$option];
        }

        if (!empty($this->configuration[$option]) && $section === '') {
            return $this->configuration[$option];
        }

        return false;
    }

    /**
     * Update configuration file.
     *
     * @return void
     */
    protected function updateConfig()
    {
        $configFilePath = $this->directory . '/' . $this->filename;

        if (!file_exists($configFilePath)) {
            if (!is_dir($this->directory)) {
                // @codeCoverageIgnoreStart
                $originalUmask = umask(0);
                @mkdir($this->directory, 0777, true);
                umask($originalUmask);
                // @codeCoverageIgnoreEnd
            }
        }

        file_put_contents($configFilePath, json_encode($this->configuration));
    }
}
