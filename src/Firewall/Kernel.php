<?php
/*
 * @name        Shieldon Firewall
 * @author      Terry Lin
 * @link        https://github.com/terrylinooo/shieldon
 * @package     Shieldon
 * @since       1.0.0
 * @version     2.0.0
 * @license     MIT
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 */

declare(strict_types=1);

namespace Shieldon\Firewall;

use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;
use Shieldon\Firewall\Captcha\CaptchaInterface;
use Shieldon\Firewall\Captcha\Foundation;
use Shieldon\Firewall\Component\ComponentInterface;
use Shieldon\Firewall\Component\ComponentProvider;
use Shieldon\Firewall\Utils\Container;
use Shieldon\Firewall\HttpFactory;
use Shieldon\Firewall\Driver\DriverProvider;
use Shieldon\Firewall\Log\ActionLogger;
use Shieldon\Messenger\MessengerInterface;
use Shieldon\Firewall\Helpers;
use function Shieldon\Firewall\get_cpu_usage;
use function Shieldon\Firewall\get_memory_usage;
use function Shieldon\Firewall\__;
use function Shieldon\Firewall\get_default_properties;
use function Shieldon\Firewall\get_request;
use function Shieldon\Firewall\get_response;
use function Shieldon\Firewall\get_session;
use function Shieldon\Firewall\unset_superglobal;

use LogicException;
use RuntimeException;
use Closure;
use InvalidArgumentException;

use function file_exists;
use function file_put_contents;
use function filter_var;
use function get_class;
use function gethostbyaddr;
use function is_writable;
use function microtime;
use function ob_end_clean;
use function ob_get_contents;
use function ob_start;
use function str_replace;
use function strpos;
use function strrpos;
use function substr;
use function time;

/**
 * The primary Shiendon class.
 */
class Kernel
{
    use IpTrait;

    // Reason codes (allow)
    const REASON_IS_SEARCH_ENGINE = 100;
    const REASON_IS_GOOGLE = 101;
    const REASON_IS_BING = 102;
    const REASON_IS_YAHOO = 103;

    // Reason codes (deny)
    const REASON_TOO_MANY_SESSIONS = 1;
    const REASON_TOO_MANY_ACCESSES = 2; // (not used)
    const REASON_EMPTY_JS_COOKIE = 3;
    const REASON_EMPTY_REFERER = 4;
    
    const REASON_REACHED_LIMIT_DAY = 11;
    const REASON_REACHED_LIMIT_HOUR = 12;
    const REASON_REACHED_LIMIT_MINUTE = 13;
    const REASON_REACHED_LIMIT_SECOND = 14;

    const REASON_INVALID_IP = 40;
    const REASON_DENY_IP = 41;
    const REASON_ALLOW_IP = 42;

    const REASON_COMPONENT_IP = 81;
    const REASON_COMPONENT_RDNS = 82;
    const REASON_COMPONENT_HEADER = 83;
    const REASON_COMPONENT_USERAGENT = 84;
    const REASON_COMPONENT_TRUSTED_ROBOT = 85;

    const REASON_MANUAL_BAN = 99;

    // Action codes
    const ACTION_DENY = 0;
    const ACTION_ALLOW = 1;
    const ACTION_TEMPORARILY_DENY = 2;
    const ACTION_UNBAN = 9;

    // Result codes
    const RESPONSE_DENY = 0;
    const RESPONSE_ALLOW = 1;
    const RESPONSE_TEMPORARILY_DENY = 2;
    const RESPONSE_LIMIT_SESSION = 3;

    const LOG_LIMIT = 3;
    const LOG_PAGEVIEW = 11;
    const LOG_BLACKLIST = 98;
    const LOG_CAPTCHA = 99;

    const KERNEL_DIR = __DIR__;

    /**
     * Driver for storing data.
     *
     * @var \Shieldon\Firewall\Driver\DriverProvider
     */
    public $driver;

    /**
     * Container for Shieldon components.
     *
     * @var array
     */
    public $component = [];

    /**
     * Logger instance.
     *
     * @var ActionLogger
     */
    public $logger;

    /**
     * The closure functions that will be executed in this->run()
     *
     * @var array
     */
    protected $closures = [];

    /**
     * Enable or disable the filters.
     *
     * @var array
     */
    protected $filterStatus = [
        /**
         * Check how many pageviews an user made in a short period time.
         * For example, limit an user can only view 30 pages in 60 minutes.
         */
        'frequency' => true,

        /**
         * If an user checks any internal link on your website, the user's
         * browser will generate HTTP_REFERER information.
         * When a user view many pages without HTTP_REFERER information meaning
         * that the user MUST be a web crawler.
         */
        'referer' => false,

        /**
         * Most of web crawlers do not render JavaScript, they only get the 
         * content they want, so we can check whether the cookie can be created
         * by JavaScript or not.
         */
        'cookie' => false,

        /**
         * Every unique user should only has a unique session, but if a user
         * creates different sessions every connection... meaning that the 
         * user's browser doesn't support cookie.
         * It is almost impossible that modern browsers not support cookie,
         * therefore the user MUST be a web crawler.
         */
        'session' => false,
    ];

    /**
     * default settings
     *
     * @var array
     */
    protected $properties = [];

    /**
     * This is for creating data tables automatically
     * Turn it off, if you don't want to check data tables every connection.
     *
     * @var bool
     */
    protected $autoCreateDatabase = true;

    /**
     * Container for captcha addons.
     *
     * @var Interface
     */
    protected $captcha = [];

    /**
     * The ways Shieldon send a message to when someone has been blocked.
     *
     * @var MessengerInterface[]
     */
    protected $messengers = [];
    
    /**
     * Html output.
     *
     * @var array
     */
    protected $html = [];

    /**
     * If the IP is in the rule table, the rule status will change.
     *
     * @var array
     */
    protected $ruleStatus = [

        // IP is marked as allow in the rule table.
        'allow' => false,

        // IP is marked as deny in the rule table.
        'deny' => false,
    ];

    /**
     * Is to limit traffic?
     *
     * @var array
     */
    protected $sessionLimit = [

        // How many sessions will be available?
        // 0 = no limit.
        'count' => 0,

        // How many seconds will a session be availe to visit?
        // 0 = no limit.
        'period' => 0, 
    ];

    /**
     * Record the online session status.
     * This will be enabled when $sessionLimit[count] > 0
     *
     * @var array
     */
    protected $sessionStatus = [

        // Online session count.
        'count' => 0,

        // Current session order.
        'order' => 0,

        // Current waiting queue.
        'queue' => 0,
    ];

    /**
     * Result.
     *
     * @var int
     */
    protected $result = 1;

    /**
     * URLs that are excluded from Shieldon's protection.
     *
     * @var array
     */
    protected $excludedUrls = [];

    /**
     * Which type of configuration source that Shieldon firewall managed?
     *
     * @var string
     */
    protected $firewallType = 'self'; // managed | config | self | demo

    /**
     * Custom dialog UI settings.
     *
     * @var array
     */
    protected $dialogUI = [];

    /**
     * Store the class information used in Shieldon.
     *
     * @var array
     */
    protected $registrar = [];

    /**
     * Strict mode.
     * 
     * Set by `strictMode()` only. The default value of this propertry is undefined.
     *
     * @var bool
     */
    protected $strictMode;

    /**
     * The directory in where the frontend template files are placed.
     *
     * @var string
     */
    protected $templateDirectory = '';

    /**
     * Shieldon constructor.
     * 
     * @param ServerRequestInterface|null $request  A PSR-7 server request.
     * 
     * @return void
     */
    public function __construct(?ServerRequestInterface $request  = null, ?ResponseInterface $response = null)
    {
        // Load helper functions. This is the must.
        new Helpers();

        if (is_null($request)) {
            $request = HttpFactory::createRequest();
        }

        if (is_null($response)) {
            $response = HttpFactory::createResponse();
        }

        $session = HttpFactory::createSession();

        $this->properties = get_default_properties();
        $this->add(new Foundation());

        Container::set('request', $request);
        Container::set('response', $response);
        Container::set('session', $session);
        Container::set('shieldon', $this);
    }

    /**
     * Log actions.
     *
     * @param int $actionCode The code number of the action.
     *
     * @return void
     */
    protected function log(int $actionCode): void
    {
        if (null !== $this->logger) {
            $logData['ip']          = $this->getIp();
            $logData['session_id']  = get_session()->get('id');
            $logData['action_code'] = $actionCode;
            $logData['timesamp']    = time();

            $this->logger->add($logData);
        }
    }

    /**
     * Run, run, run!
     *
     * Check the rule tables first, if an IP address has been listed.
     * Call function filter() if an IP address is not listed in rule tables.
     *
     * @return int The response code.
     */
    protected function process(): int
    {
        $this->driver->init($this->autoCreateDatabase);

        foreach (array_keys($this->component) as $name) {
            $this->component[$name]->setIp($this->ip);
            $this->component[$name]->setRdns($this->rdns);

            // Apply global strict mode to all components by `strictMode()` if nesscessary.
            if (isset($this->strictMode)) {
                $this->component[$name]->setStrict($this->strictMode);
            }
        }

        /*
        |--------------------------------------------------------------------------
        | Stage - Looking for rule table.
        |--------------------------------------------------------------------------
        */

        $ipRule = $this->driver->get($this->ip, 'rule');

        if (!empty($ipRule)) {

            $ruleType = (int) $ipRule['type'];

            if ($ruleType === self::ACTION_ALLOW) {
                $this->ruleStatus['allow'] = true;
                
            } else {
                
                // Current visitor has been blocked. If he still attempts accessing the site, 
                // then we can drop him into the permanent block list.
                $attempts = $ipRule['attempts'];
                $now      = time();

                $logData['log_ip']     = $ipRule['log_ip'];
                $logData['ip_resolve'] = $ipRule['ip_resolve'];
                $logData['time']       = $now;
                $logData['type']       = $ipRule['type'];
                $logData['reason']     = $ipRule['reason'];
                $logData['attempts']   = $attempts;

                // @since 0.2.0
                $attemptPeriod = $this->properties['record_attempt_detection_period'];
                $attemptReset  = $this->properties['reset_attempt_counter'];

                $lastTimeDiff = $now - $ipRule['time'];

                if ($lastTimeDiff <= $attemptPeriod) {
                    $logData['attempts'] = ++$attempts;
                }

                if ($lastTimeDiff > $attemptReset) {
                    $logData['attempts'] = 0;
                }

                $isTriggerMessenger = false;
                $isUpdatRuleTable = false;

                $handleType = 0;

                if ($this->properties['deny_attempt_enable']['data_circle']) {

                    if ($ruleType === self::ACTION_TEMPORARILY_DENY) {

                        $isUpdatRuleTable = true;

                        $buffer = $this->properties['deny_attempt_buffer']['data_circle'];

                        if ($attempts >= $buffer) {

                            if ($this->properties['deny_attempt_notify']['data_circle']) {
                                $isTriggerMessenger = true;
                            }

                            $logData['type'] = self::ACTION_DENY;

                            // Reset this value for next checking process - iptables.
                            $logData['attempts'] = 0;
                            $handleType = 1;
                        }
                    }
                }

                if ($this->properties['deny_attempt_enable']['system_firewall']) {
                    
                    if ($ruleType === self::ACTION_DENY) {

                        $isUpdatRuleTable = true;

                        // For the requests that are already banned, but they are still attempting access, that means 
                        // that they are programmably accessing your website. Consider put them in the system-layer fireall
                        // such as IPTABLE.
                        $bufferIptable = $this->properties['deny_attempt_buffer']['system_firewall'];

                        if ($attempts >= $bufferIptable) {

                            if ($this->properties['deny_attempt_notify']['system_firewall']) {
                                $isTriggerMessenger = true;
                            }

                            $folder = rtrim($this->properties['iptables_watching_folder'], '/');

                            if (file_exists($folder) && is_writable($folder)) {
                                $filePath = $folder . '/iptables_queue.log';

                                // command, ipv4/6, ip, subnet, port, protocol, action
                                // add,4,127.0.0.1,null,all,all,drop  (example)
                                // add,4,127.0.0.1,null,80,tcp,drop   (example)
                                $command = 'add,4,' . $this->ip . ',null,all,all,deny';

                                if (filter_var($this->ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6)) {
                                    $command = 'add,6,' . $this->ip . ',null,all,allow';
                                }

                                // Add this IP address to itables_queue.log
                                // Use `bin/iptables.sh` for adding it into IPTABLES. See document for more information. 
                                file_put_contents($filePath, $command . "\n", FILE_APPEND | LOCK_EX);

                                $logData['attempts'] = 0;
                                $handleType = 2;
                            }
                        }
                    }
                }

                // We only update data when `deny_attempt_enable` is enable.
                // Because we want to get the last visited time and attempt counter.
                // Otherwise we don't update it everytime to avoid wasting CPU resource.
                if ($isUpdatRuleTable) {
                    $this->driver->save($this->ip, $logData, 'rule');
                }

                /**
                 * Notify this event to messengers.
                 */
                if ($isTriggerMessenger) {

                    // The data strings that will be appended to message body.
                    $prepareMessageData = [
                        __('core', 'messenger_text_ip')       => $logData['log_ip'],
                        __('core', 'messenger_text_rdns')     => $logData['ip_resolve'],
                        __('core', 'messenger_text_reason')   => __('core', 'messenger_text_reason_code_' . $logData['reason']),
                        __('core', 'messenger_text_handle')   => __('core', 'messenger_text_handle_type_' . $handleType),
                        __('core', 'messenger_text_system')   => '',
                        __('core', 'messenger_text_cpu')      => get_cpu_usage(),
                        __('core', 'messenger_text_memory')   => get_memory_usage(),
                        __('core', 'messenger_text_time')     => date('Y-m-d H:i:s', $logData['time']),
                        __('core', 'messenger_text_timezone') => date_default_timezone_get(),
                    ];

                    $message = __('core', 'messenger_notification_subject', 'Notification for {0}', [$this->ip]) . "\n\n";

                    foreach ($prepareMessageData as $key => $value) {
                        $message .= $key . ': ' . $value . "\n";
                    }

                    try {

                        foreach ($this->messengers as $messenger) {
                            $messenger->send($message);
                        }

                    // @codeCoverageIgnoreStart
                    } catch (RuntimeException $e) {
                        // echo $e->getMessage();
                        // Do not throw error, becasue the third-party services might be unavailable.
                    }
                    // @codeCoverageIgnoreEnd
                }

                // For an incoming request already in the rule list, return the rule type immediately.
                return $this->result = $ruleType;
            }
        }

        if ($this->ruleStatus['allow']) {

            // The requests that are allowed in rule table will not go into sessionHandler.
            return $this->result = self::RESPONSE_ALLOW;

        } else {

            /*
            |--------------------------------------------------------------------------
            | Statge - Detect popular search engine.
            |--------------------------------------------------------------------------
            */

            if ($this->getComponent('TrustedBot')) {
 
                // We want to put all the allowed robot into the rule list, so that the checking of IP's resolved hostname 
                // is no more needed for that IP.
                if ($this->getComponent('TrustedBot')->isAllowed()) {

                    if ($this->getComponent('TrustedBot')->isGoogle()) {
                        // Add current IP into allowed list, because it is from real Google domain.
                        $this->action(
                            self::ACTION_ALLOW,
                            self::REASON_IS_GOOGLE
                        );

                    } elseif ($this->getComponent('TrustedBot')->isBing()) {
                        // Add current IP into allowed list, because it is from real Bing domain.
                        $this->action(
                            self::ACTION_ALLOW,
                            self::REASON_IS_BING
                        );

                    } elseif ($this->getComponent('TrustedBot')->isYahoo()) {
                        // Add current IP into allowed list, because it is from real Yahoo domain.
                        $this->action(
                            self::ACTION_ALLOW,
                            self::REASON_IS_YAHOO
                        );

                    } else {
                        // Add current IP into allowed list, because you trust it.
                        // You have already defined it in the settings.
                        $this->action(
                            self::ACTION_ALLOW,
                            self::REASON_IS_SEARCH_ENGINE
                        );
                    }
                    // Allowed robots not join to our traffic handler.
                    return $this->result = self::RESPONSE_ALLOW;
                }

                // After `isAllowed()` executed, we can check if the currect access is fake by `isFakeRobot()`.
                if ($this->getComponent('TrustedBot')->isFakeRobot()) {
                    $this->action(
                        self::ACTION_DENY,
                        self::REASON_COMPONENT_TRUSTED_ROBOT
                    );

                    return $this->result = self::RESPONSE_DENY;
                }
            }

            /*
            |--------------------------------------------------------------------------
            | Stage - IP component.
            |--------------------------------------------------------------------------
            */

            if ($this->getComponent('Ip')) {

                $result = $this->getComponent('Ip')->check();

                if (!empty($result)) {
    
                    switch ($result['status']) {
    
                        case 'allow':
                            $actionCode = self::ACTION_ALLOW;
                            $reasonCode = $result['code'];
                            break;
        
                        case 'deny':
                            $actionCode = self::ACTION_DENY;
                            $reasonCode = $result['code']; 
                            break;
                    }
    
                    // @since 0.1.8
                    $this->action($actionCode, $reasonCode);
    
                    // $resultCode = $actionCode
                    return $this->result = $this->sessionHandler($actionCode);
                }
            }
    
            /*
            |--------------------------------------------------------------------------
            | Stage - Check all other components.
            |--------------------------------------------------------------------------
            */

            foreach ($this->component as $component) {
    
                // check if is a a bad robot already defined in settings.
                if ($component->isDenied()) {
    
                    // @since 0.1.8
                    $this->action(
                        self::ACTION_DENY,
                        $component->getDenyStatusCode()
                    );
    
                    return $this->result = self::RESPONSE_DENY;
                }
            }
        }

        /*
        |--------------------------------------------------------------------------
        | Stage - Filters
        |--------------------------------------------------------------------------
        | This IP address is not listed in rule table, let's detect it.
        |
        */

        if (
            $this->filterStatus['frequency'] ||
            $this->filterStatus['referer'] ||
            $this->filterStatus['session'] ||
            $this->filterStatus['cookie']
        ) {
            return $this->result = $this->sessionHandler($this->filter());
        }

        return $this->result = $this->sessionHandler(self::RESPONSE_ALLOW);
    }

    /**
     * Detect and analyze an user's behavior.
     *
     * @return int The response code.
     */
    protected function filter(): int
    {
        $now = time();
        $logData = [];
        $isFlaggedAsUnusualBehavior = false;

        $resetPageviews = [
            's' => false, // second.
            'm' => false, // minute.
            'h' => false, // hour.
            'd' => false, // day.
        ];

        // Fetch an IP data from Shieldon log table.
        $ipDetail = $this->driver->get($this->ip, 'filter_log');

        $ipDetail = $this->driver->parseData($ipDetail, 'filter_log');
        $logData = $ipDetail;

        // Counting user pageviews.
        foreach (array_keys($resetPageviews) as $timeUnit) {

            // Each time unit will increase by 1.
            $logData["pageviews_{$timeUnit}"] = $ipDetail["pageviews_{$timeUnit}"] + 1;
            $logData["first_time_{$timeUnit}"] = $ipDetail["first_time_{$timeUnit}"];
        }

        $logData['first_time_flag'] = $ipDetail['first_time_flag'];

        if (!empty($ipDetail['ip'])) {
            $logData['ip']        = $this->ip;
            $logData['session']   = get_session()->get('id');
            $logData['hostname']  = $this->rdns;
            $logData['last_time'] = $now;

            /*
            |--------------------------------------------------------------------------
            | HTTP_REFERER
            |--------------------------------------------------------------------------
            */

            if ($this->filterStatus['referer']) {

                if ($now - $ipDetail['last_time'] > $this->properties['interval_check_referer']) {

                    // Get values from data table. We will count it and save it back to data table.
                    // If an user is already in your website, it is impossible no referer when he views other pages.
                    $logData['flag_empty_referer'] = $ipDetail['flag_empty_referer'] ?? 0;

                    if (empty(get_request()->getHeaderLine('referer'))) {
                        $logData['flag_empty_referer']++;
                        $isFlaggedAsUnusualBehavior = true;
                    }

                    // Ban this IP if they reached the limit.
                    if ($logData['flag_empty_referer'] > $this->properties['limit_unusual_behavior']['referer']) {
                        $this->action(
                            self::ACTION_TEMPORARILY_DENY,
                            self::REASON_EMPTY_REFERER
                        );
                        return self::RESPONSE_TEMPORARILY_DENY;
                    }
                }
            }

            /*
            |--------------------------------------------------------------------------
            | SESSION
            |--------------------------------------------------------------------------
            */

            if ($this->filterStatus['session']) {

                if ($now - $ipDetail['last_time'] > $this->properties['interval_check_session']) {

                    // Get values from data table. We will count it and save it back to data table.
                    $logData['flag_multi_session'] = $ipDetail['flag_multi_session'] ?? 0;
                    
                    if (get_session()->get('id') !== $ipDetail['session']) {

                        // Is is possible because of direct access by the same user many times.
                        // Or they don't have session cookie set.
                        $logData['flag_multi_session']++;
                        $isFlaggedAsUnusualBehavior = true;
                    }

                    // Ban this IP if they reached the limit.
                    if ($logData['flag_multi_session'] > $this->properties['limit_unusual_behavior']['session']) {
                        $this->action(
                            self::ACTION_TEMPORARILY_DENY,
                            self::REASON_TOO_MANY_SESSIONS
                        );

                        return self::RESPONSE_TEMPORARILY_DENY;
                    }
                }
            }

            /*
            |--------------------------------------------------------------------------
            | JAVASCRIPT COOKIE
            |--------------------------------------------------------------------------
            */

            // Let's checking cookie created by javascript..
            if ($this->filterStatus['cookie']) {

                // Get values from data table. We will count it and save it back to data table.
                $logData['flag_js_cookie']   = $ipDetail['flag_js_cookie']   ?? 0;
                $logData['pageviews_cookie'] = $ipDetail['pageviews_cookie'] ?? 0;

                $c = $this->properties['cookie_name'];

                $jsCookie = get_request()->getCookieParams()[$c] ?? 0;

                // Checking if a cookie is created by JavaScript.
                if (!empty($jsCookie)) {

                    if ($jsCookie == '1') {
                        $logData['pageviews_cookie']++;

                    } else {
                        // Flag it if the value is not 1.
                        $logData['flag_js_cookie']++;
                        $isFlaggedAsUnusualBehavior = true;
                    }
                } else {
                    // If we cannot find the cookie, flag it.
                    $logData['flag_js_cookie']++;
                    $isFlaggedAsUnusualBehavior = true;
                }

                if ($logData['flag_js_cookie'] > $this->properties['limit_unusual_behavior']['cookie']) {

                    // Ban this IP if they reached the limit.
                    $this->action(
                        self::ACTION_TEMPORARILY_DENY,
                        self::REASON_EMPTY_JS_COOKIE
                    );

                    return self::RESPONSE_TEMPORARILY_DENY;
                }

                // Remove JS cookie and reset.
                if ($logData['pageviews_cookie'] > $this->properties['limit_unusual_behavior']['cookie']) {

                    // Reset to 0.
                    $logData['pageviews_cookie'] = 0;
                    $logData['flag_js_cookie']   = 0;

                    unset_superglobal($c, 'cookie');
                }
            }

            /*
            |--------------------------------------------------------------------------
            | ACCESS FREQUENCY
            |--------------------------------------------------------------------------
            */

            if ($this->filterStatus['frequency']) {

                foreach (array_keys($this->properties['time_unit_quota']) as $timeUnit) {

                    switch ($timeUnit) {
                        case 's': $timeSecond = 1;     break;
                        case 'm': $timeSecond = 60;    break;
                        case 'h': $timeSecond = 3600;  break;
                        case 'd': $timeSecond = 86400; break;
                    }

                    if (($now - $ipDetail["first_time_{$timeUnit}"]) >= ($timeSecond + 1)) {

                        // For example:
                        // (1) minutely: now > first_time_m about 61, (2) hourly: now > first_time_h about 3601, 
                        // Let's prepare to rest the the pageview count.
                        $resetPageviews[$timeUnit] = true;

                    } else {

                        // If an user's pageview count is more than the time period limit
                        // He or she will get banned.
                        if ($logData["pageviews_{$timeUnit}"] > $this->properties['time_unit_quota'][$timeUnit]) {

                            if ($timeUnit === 's') {
                                $this->action(
                                    self::ACTION_TEMPORARILY_DENY,
                                    self::REASON_REACHED_LIMIT_SECOND
                                );
                            }

                            if ($timeUnit === 'm') {
                                $this->action(
                                    self::ACTION_TEMPORARILY_DENY,
                                    self::REASON_REACHED_LIMIT_MINUTE
                                );
                            }

                            if ($timeUnit === 'h') {
                                $this->action(
                                    self::ACTION_TEMPORARILY_DENY,
                                    self::REASON_REACHED_LIMIT_HOUR
                                );
                            }

                            if ($timeUnit === 'd') {
                                $this->action(
                                    self::ACTION_TEMPORARILY_DENY,
                                    self::REASON_REACHED_LIMIT_DAY
                                );
                            }
                            
                            return self::RESPONSE_TEMPORARILY_DENY;
                        }
                    }
                }

                /* The user is passed from the pageview check. */

                foreach ($resetPageviews as $timeUnit => $resetStatus) {

                    // Reset the pageview check for specfic time unit.
                    if ($resetStatus) {
                        $logData["first_time_{$timeUnit}"] = $now;
                        $logData["pageviews_{$timeUnit}"] = 0;
                    }
                }
            }

            // Is fagged as unusual beavior? Count the first time.
            if ($isFlaggedAsUnusualBehavior) {
                $logData['first_time_flag'] = (!empty($logData['first_time_flag'])) ? $logData['first_time_flag'] : $now;
            }

            // Reset the flagged factor check.
            if (!empty($ipDetail['first_time_flag'])) {
                if ($now - $ipDetail['first_time_flag'] >= $this->properties['time_reset_limit']) {
                    $logData['flag_multi_session'] = 0;
                    $logData['flag_empty_referer'] = 0;
                    $logData['flag_js_cookie']     = 0;
                }
            }

            $this->driver->save($this->ip, $logData, 'filter_log');

        } else {

            // If $ipDetail[ip] is empty.
            // It means that the user is first time visiting our webiste.

            $logData['ip']        = $this->ip;
            $logData['session']   = get_session()->get('id');
            $logData['hostname']  = $this->rdns;
            $logData['last_time'] = $now;

            foreach ($resetPageviews as $key => $resetStatus) {
                $logData["first_time_{$key}"] = $now;
            }

            $this->driver->save($this->ip, $logData, 'filter_log');
        }

        return self::RESPONSE_ALLOW;
    }

    /**
     * Start an action for this IP address, allow or deny, and give a reason for it.
     *
     * @param int    $actionCode - 0: deny, 1: allow, 9: unban.
     * @param string $reasonCode
     * @param string $assignIp
     * 
     * @return void
     */
    protected function action(int $actionCode, int $reasonCode, string $assignIp = ''): void
    {
        $ip = $this->ip;

        $rdns = $this->rdns;

        $now = time();
    
        if ('' !== $assignIp) {
            $ip = $assignIp;
            $rdns = gethostbyaddr($ip);
        }

        switch ($actionCode) {
            case self::ACTION_ALLOW: // acutally not used.
            case self::ACTION_DENY:  // actually not used.
            case self::ACTION_TEMPORARILY_DENY:
                $logData['log_ip']     = $ip;
                $logData['ip_resolve'] = $rdns;
                $logData['time']       = $now;
                $logData['type']       = $actionCode;
                $logData['reason']     = $reasonCode;
                $logData['attempts']   = 0;

                $this->driver->save($ip, $logData, 'rule');
                break;
            
            case self::ACTION_UNBAN:
                $this->driver->delete($ip, 'rule');
                break;
        }

        // Remove logs for this IP address because It already has it's own rule on system.
        // No need to count it anymore.
        $this->driver->delete($ip, 'filter_log');

        if (null !== $this->logger) {
            $log['ip']          = $ip;
            $log['session_id']  = get_session()->get('id');
            $log['action_code'] = $actionCode;
            $log['timesamp']    = $now;

            $this->logger->add($log);
        }
    }

    /**
     * Deal with online sessions.
     *
     * @param bool $statusCode The response code.
     *
     * @return int The response code.
     */
    protected function sessionHandler($statusCode): int
    {
        if (self::RESPONSE_ALLOW !== $statusCode) {
            return $statusCode;
        }

        // If you don't enable `limit traffic`, ignore the following steps.
        if (empty($this->sessionLimit['count'])) {
            return self::RESPONSE_ALLOW;

        } else {

            // Get the proerties.
            $limit = (int) ($this->sessionLimit['count'] ?? 0);
            $period = (int) ($this->sessionLimit['period'] ?? 300);
            $now = time();

            $sessionData = $this->driver->getAll('session');
            $sessionPools = [];

            $i = 1;
            $sessionOrder = 0;

            if (!empty($sessionData)) {
                foreach ($sessionData as $v) {
                    $sessionPools[] = $v['id'];
                    $lasttime = (int) $v['time'];
    
                    if (get_session()->get('id') === $v['id']) {
                        $sessionOrder = $i;
                    }
    
                    // Remove session if it expires.
                    if ($now - $lasttime > $period) {
                        $this->driver->delete($v['id'], 'session');
                    }
                    $i++;
                }

                if (0 === $sessionOrder) {
                    $sessionOrder = $i;
                }
            } else {
                $sessionOrder = 0;
            }

            // Count the online sessions.
            $this->sessionStatus['count'] = count($sessionPools);
            $this->sessionStatus['order'] = $sessionOrder;
            $this->sessionStatus['queue'] = $sessionOrder - $limit;

            if (!in_array(get_session()->get('id'), $sessionPools)) {
                $this->sessionStatus['count']++;

                // New session, record this data.
                $data['id'] = get_session()->get('id');
                $data['ip'] = $this->ip;
                $data['time'] = $now;

                $microtimesamp = explode(' ', microtime());
                $microtimesamp = $microtimesamp[1] . str_replace('0.', '', $microtimesamp[0]);
                $data['microtimesamp'] = $microtimesamp;

                $this->driver->save(get_session()->get('id'), $data, 'session');
            }

            // Online session count reached the limit. So return RESPONSE_LIMIT_SESSION response code.
            if ($sessionOrder >= $limit) {
                return self::RESPONSE_LIMIT_SESSION;
            }
        }

        return self::RESPONSE_ALLOW;
    }

    // @codeCoverageIgnoreStart

    /**
     * For testing propose.
     *
     * @param string $sessionId
     *
     * @return void
     */
    protected function setSessionId(string $sessionId = ''): void
    {
        if ('' !== $sessionId) {
            get_session()->set('id', $sessionId);
        }
    }

    // @codeCoverageIgnoreEnd

    /*
    | -------------------------------------------------------------------
    |                            Public APIs
    | -------------------------------------------------------------------
    */

    /**
     * Register classes to Shieldon core.
     * setDriver, setLogger, setComponent and setCaptcha are deprecated methods
     * and no more used.
     *
     * @param object $instance Component classes that used on Shieldon.
     *
     * @return void
     */
    public function add($instance)
    {
        $class = get_class($instance);
        $class = substr($class, strrpos($class, '\\') + 1);

        if ($instance instanceof DriverProvider) {
            $this->driver = $instance;
            $this->registrar[0] = ['driver' => $class];
        }

        if ($instance instanceof ActionLogger) {
            $this->logger = $instance;
            $this->registrar[1] = ['logger' => $class];
        }

        $i = 2;

        if ($instance instanceof CaptchaInterface) {
            $this->captcha[$class] = $instance;
            $this->registrar[$i] = ['captcha' => $class];
        }

        if ($instance instanceof ComponentProvider) {
            $this->component[$class] = $instance;
            $this->registrar[$i] = ['component' => $class];
        }

        if ($instance instanceof MessengerInterface) {
            $this->messengers[] = $instance;
            $this->registrar[$i] = ['messenger' => $class];
        }

        $i++;
    }

    /**
     * Get a component instance from component's container.
     *
     * @param string $name The component's class name.
     *
     * @return ComponentInterface|null
     */
    public function getComponent(string $name)
    {
        if (isset($this->component[$name])) {
            return $this->component[$name];
        }

        return null;
    }

    /**
     * Strict mode.
     * This option will take effects to all components.
     * 
     * @param bool $bool Set true to enble strict mode, false to disable it overwise.
     *
     * @return void
     */
    public function setStrict(bool $bool)
    {
        $this->strictMode = $bool;
    }

    /**
     * Disable filters.
     */
    public function disableFilters(): void
    {
        $this->setFilters([
            'session'   => false,
            'cookie'    => false,
            'referer'   => false,
            'frequency' => false,
        ]);
    }

    /**
     * For first time installation only. This is for creating data tables automatically.
     * Turning it on will check the data tables exist or not at every single pageview, 
     * it's not good for high traffic websites.
     *
     * @param bool $bool
     * 
     * @return void
     */
    public function createDatabase(bool $bool)
    {
        $this->autoCreateDatabase = $bool;
    }

    /**
     * Set a data channel.
     *
     * This will create databases for the channel.
     *
     * @param string $channel Specify a channel.
     *
     * @return void
     */
    public function setChannel(string $channel)
    {
        if (!$this->driver instanceof DriverProvider) {
            throw new LogicException('setChannel method requires setDriver set first.');
        } else {
            $this->driver->setChannel($channel);
        }
    }

    /**
     * Return the result from Captchas.
     *
     * @return bool
     */
    public function captchaResponse(): bool
    {
        foreach ($this->captcha as $captcha) {
            if (!$captcha->response()) {
                return false;
            }
        }

        if (!empty($this->sessionLimit['count'])) {
            $this->result = $this->sessionHandler(self::RESPONSE_ALLOW);
        }

        return true;
    }

    /**
     * Ban an IP.
     *
     * @param string $ip A valid IP address.
     *
     * @return void
     */
    public function ban(string $ip = ''): void
    {
        if ('' === $ip) {
            $ip = $this->ip;
        }
 
        $this->action(
            self::ACTION_DENY,
            self::REASON_MANUAL_BAN, $ip
        );
    }

    /**
     * Unban an IP.
     *
     * @param string $ip A valid IP address.
     *
     * @return void
     */
    public function unban(string $ip = ''): void
    {
        if ('' === $ip) {
            $ip = $this->ip;
        }

        $this->action(
            self::ACTION_UNBAN,
            self::REASON_MANUAL_BAN, $ip
        );
        $this->log(self::ACTION_UNBAN);

        $this->result = self::RESPONSE_ALLOW;
    }

    /**
     * Set a property setting.
     *
     * @param string $key   The key of a property setting.
     * @param mixed  $value The value of a property setting.
     *
     * @return void
     */
    public function setProperty(string $key = '', $value = '')
    {
        if (isset($this->properties[$key])) {
            $this->properties[$key] = $value;
        }
    }

    /**
     * Set the property settings.
     * 
     * @param array $settings The settings.
     *
     * @return void
     */
    public function setProperties(array $settings): void
    {
        foreach (array_keys($this->properties) as $k) {
            if (isset($settings[$k])) {
                $this->properties[$k] = $settings[$k];
            }
        }
    }

    /**
     * Limt online sessions.
     *
     * @param int $count
     * @param int $period
     *
     * @return void
     */
    public function limitSession(int $count = 1000, int $period = 300): void
    {
        $this->sessionLimit = [
            'count' => $count,
            'period' => $period
        ];
    }

    /**
     * Set result page's HTML.
     *
     * @param string $content The HTML text.
     * @param string $type    The page type: stop, limit, deny.
     *
     * @return void
     */
    public function setView(string $content, string $type): void
    {
        if ('session_limitation' === $type || 'captcha' === $type || 'rejection' === $type) {
            $this->html[$type] = $content;
        }
    }

    /**
     * Customize the dialog UI.
     *
     * @return void
     */
    public function setDialogUI(array $settings): void
    {
        $this->dialogUI = $settings;
    }

    /**
     * Set the frontend template directory.
     *
     * @param string $directory
     *
     * @return void
     */
    public function setTemplateDirectory(string $directory)
    {
        if (!is_dar($directory)) {
            throw new InvalidArgumentException('The template directory does not exist.');
        }
        $this->templateDirectory = $directory;
    }

    /**
     * Get a template PHP file.
     *
     * @param string $type The template type.
     *
     * @return string
     */
    protected function getTemplate(string $type): string
    {
        $directory = self::KERNEL_DIR . '/../../templates/frontend';

        if (!empty($this->templateDirectory)) {
            $directory = $this->templateDirectory;
        }

        $path = $directory . '/' . $type . '.php';

        if (!file_exists($path)) {
            throw new RuntimeException(
                sprintf(
                    'The templeate file is missing. (%s)',
                    $path
                )
            );
        }

        return $path;
    }

    /**
     * Respond the result.
     *
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function respond(): ResponseInterface
    {
        $response = get_response();
        $type = '';

        if (self::RESPONSE_TEMPORARILY_DENY === $this->result) {
            $type = 'captcha';
            $statusCode = 403; // Forbidden.

        } elseif (self::RESPONSE_LIMIT_SESSION === $this->result) {
            $type = 'session_limitation';
            $statusCode = 429; // Too Many Requests.

        } elseif (self::RESPONSE_DENY === $this->result) {
            $type = 'rejection';
            $statusCode = 400; // Bad request.
        }

        // Nothing happened. Return.
        if (empty($type)) {
            return $response;
        }

        $viewPath = $this->getTemplate($type);

        // The language of output UI. It is used on views.
        $langCode = get_session()->get('shieldon_ui_lang') ?? 'en';
        // Show online session count. It is used on views.
        $showOnlineInformation = true;
        // Show user information such as IP, user-agent, device name.
        $showUserInformation = true;

        if (empty($this->properties['display_online_info'])) {
            $showOnlineInformation = false;
        }

        if (empty($this->properties['display_user_info'])) {
            $showUserInformation = false;
        }

        if ($showUserInformation) {
            $dialoguserinfo['ip'] = $this->ip;
            $dialoguserinfo['rdns'] = $this->rdns;
            $dialoguserinfo['user_agent'] = get_request()->getHeaderLine('user-agent');
        }

        $ui = [
            'background_image' => $this->dialogUI['background_image'] ?? '',
            'bg_color'         => $this->dialogUI['bg_color']         ?? '#ffffff',
            'header_bg_color'  => $this->dialogUI['header_bg_color']  ?? '#212531',
            'header_color'     => $this->dialogUI['header_color']     ?? '#ffffff',
            'shadow_opacity'   => $this->dialogUI['shadow_opacity']   ?? '0.2',
        ];

        if (!defined('SHIELDON_VIEW')) {
            define('SHIELDON_VIEW', true);
        }

        $css = require $this->getTemplate('css/default');

        ob_start();
        require $viewPath;
        $output = ob_get_contents();
        ob_end_clean();

        // Remove unused variable notices generated from PHP intelephense.
        unset(
            $css,
            $ui,
            $langCode,
            $showOnlineInformation,
            $showLineupInformation,
            $showUserInformation
        );

        $stream = $response->getBody();
        $stream->write($output);
        $stream->rewind();

        return $response->
            withHeader('X-Protected-By', 'shieldon.io')->
            withBody($stream)->
            withStatus($statusCode);
    }

    /**
     * Run, run, run!
     *
     * Check the rule tables first, if an IP address has been listed.
     * Call function filter() if an IP address is not listed in rule tables.
     *
     * @return 
     */
    public function run(): int
    {
        if (!isset($this->registrar[0])) {
            throw new RuntimeException(
                'Must register at least one data driver.'
            );
        }
        
        // Ignore the excluded urls.
        if (!empty($this->excludedUrls)) {
            foreach ($this->excludedUrls as $url) {
                if (0 === strpos(get_request()->getUri()->getPath(), $url)) {
                    return $this->result = self::RESPONSE_ALLOW;
                }
            }
        }

        // Execute closure functions.
        foreach ($this->closures as $closure) {
            $closure();
        }

        $result = $this->process();

        if ($result !== self::RESPONSE_ALLOW) {

            // Current session did not pass the CAPTCHA, it is still stuck in CAPTCHA page.
            $actionCode = self::LOG_CAPTCHA;

            // If current session's respone code is RESPONSE_DENY, record it as `blacklist_count` in our logs.
            // It is stuck in warning page, not CAPTCHA.
            if ($result === self::RESPONSE_DENY) {
                $actionCode = self::LOG_BLACKLIST;
            }

            if ($result === self::RESPONSE_LIMIT_SESSION) {
                $actionCode = self::LOG_LIMIT;
            }

            $this->log($actionCode);

        } else {

            $this->log(self::LOG_PAGEVIEW);
        }

        return $result;
    }

    /**
     * Set the filters.
     *
     * @param array $settings filter settings.
     *
     * @return void
     */
    public function setFilters(array $settings)
    {
        foreach (array_keys($this->filterStatus) as $k) {
            if (isset($settings[$k])) {
                $this->filterStatus[$k] = $settings[$k] ?? false;
            }
        }
    }

    /**
     * Set a filter.
     *
     * @param string $filterName The filter's name.
     * @param bool   $value      True for enabling the filter, overwise.
     *
     * @return void
     */
    public function setFilter(string $filterName, bool $value): void
    {
        if (isset($this->filterStatus[$filterName])) {
            $this->filterStatus[$filterName] = $value;
        }
    }

    /**
     * Get online people count. If enable limitSession.
     *
     * @return int
     */
    public function getSessionCount(): int
    {
        return $this->sessionStatus['count'];
    }

    /**
     * Set the URLs you want them to be excluded them from protection.
     *
     * @param array $urls The list of URL want to be excluded.
     *
     * @return void
     */
    public function setExcludedUrls(array $urls = []): void
    {
        $this->excludedUrls = $urls;
    }

    /**
     * Set a closure function.
     *
     * @param string  $key     The name for the closure class.
     * @param Closure $closure An instance will be later called.
     *
     * @return void
     */
    public function setClosure(string $key, Closure $closure): void
    {
        $this->closures[$key] = $closure;
    }

    /**
     * Print a JavasSript snippet in your webpages.
     * 
     * This snippet generate cookie on client's browser,then we check the 
     * cookie to identify the client is a rebot or not.
     *
     * @return string
     */
    public function outputJsSnippet(): string
    {
        $tmpCookieName = $this->properties['cookie_name'];
        $tmpCookieDomain = $this->properties['cookie_domain'];

        if (empty($tmpCookieDomain) && get_request()->getHeaderLine('host')) {
            $tmpCookieDomain = get_request()->getHeaderLine('host');
        }

        $tmpCookieValue = $this->properties['cookie_value'];

        $jsString = '
            <script>
                var d = new Date();
                d.setTime(d.getTime()+(60*60*24*30));
                document.cookie = "' . $tmpCookieName . '=' . $tmpCookieValue . ';domain=.' . $tmpCookieDomain . ';expires="+d.toUTCString();
            </script>
        ';

        return $jsString;
    }

    /**
     * Get current visior's path.
     *
     * @return string
     */
    public function getCurrentUrl(): string
    {
        return get_request()->getUri()->getPath();
    }

    /**
     * Displayed on Firewall Panel, tell you current what type of current
     * configuration is used for.
     * 
     * @param $type The type of configuration.
     *              demo | managed | config
     *
     * @return void
     */
    public function managedBy(string $type = ''): void
    {
        if (in_array($type, ['managed', 'config', 'demo'])) {
            $this->firewallType = $type;
        }
    }
}
