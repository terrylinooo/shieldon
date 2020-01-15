<?php declare(strict_types=1);
/*
 * This file is part of the Shieldon package.
 *
 * (c) Terry L. <contact@terryl.in>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Shieldon;

use Shieldon\Shieldon;

use Shieldon\Captcha\ImageCaptcha;
use Shieldon\Captcha\Recaptcha;
use Shieldon\Component\Header;
use Shieldon\Component\Ip;
use Shieldon\Component\Rdns;
use Shieldon\Component\TrustedBot;
use Shieldon\Component\UserAgent;
use Shieldon\Container;
use Shieldon\Driver\FileDriver;
use Shieldon\Driver\MysqlDriver;
use Shieldon\Driver\RedisDriver;
use Shieldon\Driver\SqliteDriver;
use Shieldon\Log\ActionLogger;
use Shieldon\Security\Xss;
use Shieldon\Security\httpAuthentication;
use Shieldon\FirewallTrait;
use Messenger\Telegram;
use Messenger\LineNotify;
use Messenger\Sendgrid;

use PDO;
use PDOException;
use Redis;
use RedisException;

use function array_column;
use function defined;
use function file_exists;
use function file_get_contents;
use function file_put_contents;
use function is_array;
use function is_dir;
use function is_string;
use function json_decode;
use function json_encode;
use function mkdir;
use function rtrim;
use function strpos;
use function umask;

/**
 * Managed Firewall.
 * 
 * @since 3.0.0
 */
class Firewall
{
    use FirewallTrait;

    /**
     * A RESTful routing system defines four methods: POST, GET, PUT, DELETE 
     * If current framework is a RESTful routing system, 
     * the page will be refeshed after submitting Capatcha form.
     * No refresh? An Error will be occurred while current URL does't support POST method request.
     *
     * @var boolean
     */
    protected $restful = false;

    /**
     * Constructor.
     */
    public function __construct($source)
    {
        // Set to container.
        Container::set('firewall', $this);

        $this->shieldon = new Shieldon();

        if (is_string($source)) {
            $this->directory = rtrim($source, '\\/');
            $configFilePath = $this->directory . '/' . $this->filename;

            if (! file_exists($configFilePath)) {

                $jsonString = file_get_contents(__DIR__ . '/../../config.json');

                if (defined('PHPUNIT_TEST')) {
                    $jsonString = file_get_contents(__DIR__ . '/../../tests/config.json');
                }
                
            } else {
                $jsonString = file_get_contents($configFilePath);
            }

            // Identify the configration is from firewall-generated JSON config file.
            $this->configuration = json_decode($jsonString, true);
            $this->shieldon->managedBy('managed');

        } elseif (is_array($source)) {

            // Identify the configration is from PHP config file.
            $this->configuration = $source;
            $this->shieldon->managedBy('config');
        }

        $this->setup();
    }

    /**
     * Setup everything we need.
     *
     * @return void
     */
    public function setup(): void
    {
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

        $this->status = $this->getOption('daemon');
    }

    /**
     * Just, run!
     *
     * @return void
     */
    public function run(): void
    {
        if ($this->status) {

            $result = $this->shieldon->run();

            if ($result !== $this->shieldon::RESPONSE_ALLOW) {

                // @codeCoverageIgnoreStart
                if ($this->shieldon->captchaResponse()) {
                    $this->shieldon->unban();

                    // The reason here please check out the explanation of $restful property.
                    if ($this->restful) {

                        // Modify the request method from POST to GET.
                        $_SERVER['REQUEST_METHOD'] = 'GET';
                        header('Location: ' . $this->shieldon->getCurrentUrl(), true, 303);
                        die($_SERVER['REQUEST_METHOD']);
                        exit;
                    }
                }
                $this->shieldon->output(200);
                // @codeCoverageIgnoreEnd
            }
        }
    }

    /**
     * Check out the explanation of $restful property.
     *
     * @return void
     */
    public function restful(): void
    {
        $this->restful = true;
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
            $this->shieldon->setChannel($channelId);
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

                    if (! empty($redisSetting['host'])) {
                        $host = $redisSetting['host'];
                    }

                    if (! empty($redisSetting['port'])) {
                        $port = $redisSetting['port'];
                    }

                    // Create a Redis instance.
                    $redis = new Redis();
                    $redis->connect($host, $port);

                    if (! empty($redisSetting['auth'])) {

                        // @codeCoverageIgnoreStart
                        $redis->auth($redisSetting['auth']);
                        // @codeCoverageIgnoreEnd
                    }

                    // Use Redis data driver.
                    $this->shieldon->setDriver(new RedisDriver($redis));

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
                    $this->status = false;
                }

                // Use File data driver.
                $this->shieldon->setDriver(new FileDriver($fileSetting['directory_path']));

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
                    $this->shieldon->setDriver(new SqliteDriver($pdoInstance));
    
                // @codeCoverageIgnoreStart
                } catch(PDOException $e) {
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
                    $this->shieldon->setDriver(new MysqlDriver($pdoInstance));

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
            if (! empty($loggerSetting['config']['directory_path'])) {
                $this->shieldon->setLogger(new ActionLogger($loggerSetting['config']['directory_path']));
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

        if ($ipSourceType['REMOTE_ADDR']) {
            $this->shieldon->setIp($_SERVER['REMOTE_ADDR']);

        // Cloudflare
        } elseif ($ipSourceType['HTTP_CF_CONNECTING_IP']) {
            $this->shieldon->setIp($_SERVER['HTTP_CF_CONNECTING_IP']);

        // Google Cloud CDN, Google Load-balancer, AWS.
        } elseif ($ipSourceType['HTTP_X_FORWARDED_FOR']) {
            $this->shieldon->setIp($_SERVER['HTTP_X_FORWARDED_FOR']);

        // KeyCDN, or other CDN providers not listed here.
        } elseif ($ipSourceType['HTTP_X_FORWARDED_HOST']) {
            $this->shieldon->setIp($_SERVER['HTTP_X_FORWARDED_HOST']);

        // Fallback.
        } else {

            // @codeCoverageIgnoreStart
            $this->shieldon->setIp($_SERVER['REMOTE_ADDR']);
            // @codeCoverageIgnoreEnd
        }
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

        $this->shieldon->setFilters($filterConfig);

        $this->shieldon->setProperty('limit_unusual_behavior', [
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

            $this->shieldon->setProperty('time_unit_quota', $frequencyQuota);
        }

        if ($cookieSetting['enable']) {

            $cookieName = $cookieSetting['config']['cookie_name'] ?? 'ssjd';
            $cookieDomain = $cookieSetting['config']['cookie_domain'] ?? '';
            $cookieValue = $cookieSetting['config']['cookie_value'] ?? '1';
    
            $this->shieldon->setProperty('cookie_name', $cookieName);
            $this->shieldon->setProperty('cookie_domain', $cookieDomain);
            $this->shieldon->setProperty('cookie_value', $cookieValue);
        }

        if ($refererSetting['enable']) {
            $this->shieldon->setProperty('interval_check_referer', $refererSetting['config']['time_buffer']);
        }

        if ($sessionSetting['enable']) {
            $this->shieldon->setProperty('interval_check_session', $sessionSetting['config']['time_buffer']);
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
            $componentIp = new Ip();
            $this->shieldon->setComponent($componentIp);
            $this->ipManager();
        }

        if ($trustedBotSetting['enable']) {
            $componentTrustedBot = new TrustedBot();

            // This component will only allow popular search engline.
            // Other bots will go into the checking process.
            $this->shieldon->setComponent($componentTrustedBot);
        }

        if ($headerSetting['enable']) {
            $componentHeader = new Header();

            // Deny all vistors without common header information.
            if ($headerSetting['strict_mode']) {
                $componentHeader->setStrict(true);
            }

            $this->shieldon->setComponent($componentHeader);
        }

        if ($userAgentSetting['enable']) {
            $componentUserAgent = new UserAgent();

            // Deny all vistors without user-agent information.
            if ($userAgentSetting['strict_mode']) {
                $componentUserAgent->setStrict(true);
            }

            $this->shieldon->setComponent($componentUserAgent);
        }

        if ($rdnsSetting['enable']) {
            $componentRdns = new Rdns();

            // Visitors with empty RDNS record will be blocked.
            // IP resolved hostname (RDNS) and IP address must conform with each other.
            if ($rdnsSetting['strict_mode']) {
                $componentRdns->setStrict(true);
            }

            $this->shieldon->setComponent($componentRdns);
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

            $this->shieldon->setCaptcha(new Recaptcha($googleRecaptcha));
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

            $this->shieldon->setCaptcha(new ImageCaptcha($imageCaptchaConfig));
        }
    }

    /**
     * Set the messenger modules.
     *
     * @return void
     */
    protected function setMessengers(): void
    {
        $telegramSetting = $this->getOption('telegram', 'messengers');
        $linenotodySetting = $this->getOption('line_notify', 'messengers');
        $sendgridSetting = $this->getOption('sendgrid', 'messengers');

        if ($telegramSetting['enable']) {
            $apiKey = $telegramSetting['config']['api_key'] ?? '';
            $channel = $telegramSetting['config']['channel'] ?? '';
            $this->shieldon->setMessenger(new Telegram($apiKey, $channel));
        }

        if ($linenotodySetting['enable']) {
            $accessToken = $linenotodySetting['config']['access_token'] ?? '';
            $this->shieldon->setMessenger(new LineNotify($accessToken));
        }

        if ($sendgridSetting['enable']) {
            $apiKey = $sendgridSetting['config']['api_key'] ?? '';
            $sender = $sendgridSetting['config']['sender'] ?? '';
            $recipients = $sendgridSetting['config']['recipients'] ?? [];

            $sendgrid = new Sendgrid($apiKey);
            $sendgrid->setSubject('Firewall Notification');
            $sendgrid->addSender($sender);

            foreach ($recipients as $recipient) {
                $sendgrid->addRecipient($recipient);
            }

            $this->shieldon->setMessenger($sendgrid);
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

        $this->shieldon->setProperty('deny_attempt_notify', [
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

        $this->shieldon->setProperty('deny_attempt_enable', [
            'data_circle' => $enableDataCircle,
            'system_firewall' => $enableSystemFirewall,
        ]);

        $this->shieldon->setProperty('deny_attempt_buffer', [
            'data_circle' => $eventSetting['data_circle']['buffer'] ?? 10,
            'system_firewall' => $eventSetting['data_circle']['buffer'] ?? 10,
        ]);

        // Check the time of the last failed attempt. @since 0.2.0
        $recordAttempt = $this->getOption('record_attempt');

        $detectionPeriod = $recordAttempt['detection_period'] ?? 5;
        $timeToReset = $recordAttempt['time_to_reset'] ?? 1800;

        $this->shieldon->setProperty('record_attempt_detection_period', $detectionPeriod);
        $this->shieldon->setProperty('reset_attempt_counter', $timeToReset);
    }

    /**
     * Set iptables working folder.
     *
     * @return void
     */
    protected function setIptablesWatchingFolder(): void
    {
        $iptablesSetting = $this->getOption('config', 'iptables');
        $this->shieldon->setProperty('iptables_watching_folder',  $iptablesSetting['watching_folder']);
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

            $this->shieldon->limitSession($onlineUsers, $alivePeriod);
        }
    }

    /**
     * Set the cron job.
     * This is triggered by the pageviews, not system cron job.
     *
     * @return void
     */
    private function setCronJob(): void 
    {
        $cronjobSetting = $this->getOption('reset_circle', 'cronjob');

        if ($cronjobSetting['enable']) {

            $nowTime = time();

            $lastResetTime = $cronjobSetting['config']['last_update'];

            if (! empty($lastResetTime) ) {
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
                $this->shieldon->driver->rebuild();
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

        if (! empty($excludedUrls)) {
            $list = array_column($excludedUrls, 'url');

            $this->shieldon->setExcludedUrls($list);
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

        $xssFilter = new Xss();

        if ($xssProtectionOptions['post']) {
            $this->shieldon->setClosure('xss_post', function() use ($xssFilter) {
                if (! empty($_POST)) {
                    foreach (array_keys($_POST) as $k) {
                        $_POST[$k] = $xssFilter->clean($_POST[$k]);
                    }
                }
            });
        }

        if ($xssProtectionOptions['get']) {
            $this->shieldon->setClosure('xss_get', function() use ($xssFilter) {
                if (! empty($_GET)) {
                    foreach (array_keys($_GET) as $k) {
                        $_GET[$k] = $xssFilter->clean($_GET[$k]);
                    }
                }
            });
        }

        if ($xssProtectionOptions['cookie']) {
            $this->shieldon->setClosure('xss_cookie', function() use ($xssFilter) {
                if (! empty($_COOKIE)) {
                    foreach (array_keys($_COOKIE) as $k) {
                        $_COOKIE[$k] = $xssFilter->clean($_COOKIE[$k]);
                    }
                }
            });
        }

        $xssProtectedList = $this->getOption('xss_protected_list');

        if (! empty($xssProtectedList)) {
        
            $this->shieldon->setClosure('xss_protection', function() use ($xssFilter, $xssProtectedList) {

                foreach ($xssProtectedList as $v) {
                    $k = $v['variable'] ?? 'undefined';
    
                    switch ($v['type']) {

                        case 'get':

                            if (! empty($_GET[$k])) {
                                $_GET[$k] = $xssFilter->clean($_GET[$k]);
                            }
                            break;
    
                        case 'post':
    
                            if (! empty($_POST[$k])) {
                                $_POST[$k] = $xssFilter->clean($_POST[$k]);
                            }
                            break;
    
                        case 'cookie':

                            if (! empty($_COOKIE[$k])) {
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

        if (! empty($authenticateList)) {

            $authHandler = new httpAuthentication();

            $this->shieldon->setClosure('www_authenticate', function() use ($authHandler, $authenticateList) {
                $authHandler->set($authenticateList);
                $authHandler->check();
            });
        }
    }

    /**
     * IP manager.
     */
    protected function ipManager()
    {
        $ipList = $this->getOption('ip_manager');

        $allowedList = [];
        $deniedList = [];

        if (! empty($ipList)) {
            foreach ($ipList as $ip) {

                if (0 === strpos($this->shieldon->getCurrentUrl(), $ip['url']) ) {
    
                    if ('allow' === $ip['rule']) {
                        $allowedList[] = $ip['ip'];
                    }
    
                    if ('deny' === $ip['rule']) {
                        $deniedList[] = $ip['ip'];
                    }
                }
            }
        }

        if (! empty($allowedList)) {
            $this->shieldon->component['Ip']->setAllowedList($allowedList);
        }

        if (! empty($deniedList)) {
            $this->shieldon->component['Ip']->setDeniedList($deniedList);
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

        if (! empty($ui)) {
            $_SESSION['shieldon_ui_lang'] = $ui['lang'];
            $this->shieldon->setDialogUI($this->getOption('dialog_ui'));
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
    private function getOption(string $option, string $section = '')
    {
        if (! empty($this->configuration[$section][$option])) {
            return $this->configuration[$section][$option];
        }

        if (! empty($this->configuration[$option]) && $section === '') {
            return $this->configuration[$option];
        }

        return false;
    }

    /**
     * Update configuration file.
     *
     * @return void
     */
    private function updateConfig()
    {
        $configFilePath = $this->directory . '/' . $this->filename;

        if (! file_exists($configFilePath)) {
            if (! is_dir($this->directory)) {
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
