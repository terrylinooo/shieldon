<?php declare(strict_types=1);

/*
 * @name        Shieldon
 * @author      Terry Lin
 * @link        https://github.com/terrylinooo/shieldon
 * @package     Shieldon
 * @since       1.0.0
 * @version     3.0.0
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

namespace Shieldon;

use Shieldon\Captcha\CaptchaInterface;
use Shieldon\Component\ComponentInterface;
use Shieldon\Component\ComponentProvider;
use Shieldon\Container;
use Shieldon\Driver\DriverProvider;
use Shieldon\Log\ActionLogger;
use Messenger\MessengerInterface;
use function Shieldon\Helper\get_cpu_usage;
use function Shieldon\Helper\get_memory_usage;
use function Shieldon\Helper\__;

use LogicException;
use RuntimeException;
use Closure;

use function get_class;
use function gethostbyaddr;
use function session_id;
use function strrpos;
use function strpos;
use function substr;
use function ob_start;
use function ob_end_clean;
use function php_sapi_name;
use function str_replace;
use function time;

/**
 * The primary Shiendon class.
 * 
 * @since   1.0.0
 * @version 3.0.0
 */
class Shieldon
{
    use IpTrait;

    // Reason codes (allow)
    const REASON_IS_SEARCH_ENGINE = 100;
    const REASON_IS_GOOGLE = 101;
    const REASON_IS_BING = 102;
    const REASON_IS_YAHOO = 103;

    // Reason codes (deny)
    const REASON_TOO_MANY_SESSIONS = 1;
    const REASON_TOO_MANY_ACCESSES = 2; // Filter - frequency (not used)
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

    // Action codes.
    const ACTION_DENY = 0;
    const ACTION_ALLOW = 1;
    const ACTION_TEMPORARILY_DENY = 2;
    const ACTION_UNBAN = 9;

    // Result codes.
    const RESPONSE_DENY = 0;
    const RESPONSE_ALLOW = 1;
    const RESPONSE_TEMPORARILY_DENY = 2;
    const RESPONSE_LIMIT = 3;

    const LOG_LIMIT = 3;
    const LOG_PAGEVIEW = 11;
    const LOG_BLACKLIST = 98;
    const LOG_CAPTCHA = 99;

    // Shieldon directory.
    const SHIELDON_DIR = __DIR__;

    /**
     * Driver for storing data.
     *
     * @var DriverProvider
     */
    public $driver = null;

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
    public $logger = null;

    /**
     * The closure functions that will be executed in this->run()
     *
     * @var array
     */
    private $closures = [];

    /**
     * Most of web crawlers do not render JavaScript, they only get text content they want,
     * so we can check if the cookie can be created by JavaScript.
     * This is hard to prevent headless browser robots, but it can stop probably 70% poor robots.
     *
     * @var boolean
     */
    private $enableCookieCheck = false;

    /**
     * Every unique user has an unique session, but if an user creates different sessions in every connection..
     * that means the user's browser doesn't support cookie.
     * It is almost impossible that modern browsers don't support cookie, so we suspect the user is a robot or web crawler,
     * that is why we need session cookie check.
     *
     * @var boolean
     */
    private $enableSessionCheck = true;

    /**
     * Check how many pageviews an user made in a short period time.
     * For example, limit an user can only view 30 pages in 60 minutes.
     *
     * @var boolean
     */
    private $enableFrequencyCheck = true;

    /**
     * Even we can't get HTTP_REFERER information from users come from Google search,
     * but if an user checks any internal link on your website, the user's browser will generate HTTP_REFERER information.
     * If an user view many pages on your website without HTTP_REFERER information, that means the user is a web crawler
     * and it directly downloads your web pages.
     *
     * @var boolean
     */
    private $enableRefererCheck = true;

    /**
     * If you don't want Shieldon to detect bad robots or crawlers, you can set it FALSE;
     * In this case AntiScriping can still deny users by querying rule table (in MySQL, or Redis, etc.) and $denyIpPool (Array)
     *
     * @var boolean
     */
    private $enableFiltering = true;

    /**
     * default settings
     *
     * @var array
     */
    private $properties = [
        'time_unit_quota' => [
            's' => 2,
            'm' => 10,
            'h' => 30,
            'd' => 60
        ],
        'time_reset_limit'       => 3600,
        'interval_check_referer' => 5,
        'interval_check_session' => 30,
        'limit_unusual_behavior' => [
            'cookie'  => 5,
            'session' => 5,
            'referer' => 10
        ],
        'cookie_name'         => 'ssjd',
        'cookie_domain'       => '',
        'cookie_value'        => '1',
        'display_online_info' => true,
        'display_user_info'   => false,

        /**
         * If you set this option enabled, Shieldon will record every CAPTCHA fails in a row, 
         * Once that user have reached the limitation number, Shieldon will put it as a blocked IP in rule table,
         * until the new data cycle begins.
         * 
         * Once that user have been blocked, they are still access the warning page, it means that they are not
         * humain for sure, so let's throw them into the system firewall and say goodbye to them forever.
         */
        'deny_attempt_enable' => [
            'data_circle'     => false,
            'system_firewall' => false,
        ],
        'deny_attempt_notify' => [
            'data_circle'     => false,
            'system_firewall' => false,
        ],
        'deny_attempt_buffer' => [
            'data_circle'     => 10,
            'system_firewall' => 10,
        ],
        /**
         * To prevent dropping social platform robots into iptables firewall, such as Facebook, Line, 
         * and others who scrape snapshots from your web pages, you should adjust the values below 
         * to fit your needs. (unit: second)
         */
        'record_attempt_detection_period' => 5, // 5 seconds.

        // Reset the counter after n second.
        'reset_attempt_counter' => 1800, // 30 minutes.

        /**
         * System-layer firewall, ip6table service watches this folder to receive command created by Shieldon Firewall.
         */
        'iptables_watching_folder' => '/tmp/',
    ];

    /**
     * This is for creating data tables automatically
     * Turn it off, if you don't want to check data tables every connection.
     *
     * @var bool
     */
    private $autoCreateDatabase = true;

    /**
     * The HTTP referer (misspelling of referrer) 
     *
     * @var string
     */
    private $referer = '';

    /**
     * Container for captcha addons.
     *
     * @var Interface
     */
    private $captcha = [];

    /**
     * Html output.
     *
     * @var array
     */
    private $html = [];

    /**
     * The session ID.
     *
     * @var string
     */
    private $sessionId = null;

    /**
     * Is this IP in the rule list?
     *
     * @var bool
     */
    private $isAllowedRule = false;

    /**
     * Is to limit traffic? This will recond online sessions.
     *
     * @var array
     */
    private $isLimitSession = [];

    /**
     * Result.
     *
     * @var int
     */
    private $result = 1;

    /**
     * Get online session count
     *
     * @var integer
     */
    private $sessionCount = 0;

    /**
     * Current session order.
     *
     * @var integer
     */
    private $currentSessionOrder = 0;

    /**
     * Used on limitSession.
     *
     * @var integer
     */
    private $currentWaitNumber = 0;

    /**
     * Strict mode.
     *
     * @var boolean
     */
    private $strictMode = false;

    /**
     * Vistor's current browsering URL.
     *
     * @var string
     * @since 3.0.0
     */
    private $currentUrl = '';

    /**
     * URLs that are excluded from Shieldon's protection.
     *
     * @var array
     * @since 3.0.0
     */
    private $excludedUrls = [];

    /**
     * Which type of configuration source that Shieldon firewall managed?
     *
     * @var string
     * @since 3.0.0
     */
    private $firewallType = 'self'; // managed | config | self | demo

    /**
     * Custom dialog UI settings.
     *
     * @var array
     * @since 3.1.0
     */
    private $dialogUI = [];

    /**
     * The ways Shieldon send a message to when someone has been blocked.
     *
     * @var MessengerInterface[]
     */
    private $messengers = [];

    /**
     * Constructor.
     * 
     * @return void
     */
    public function __construct(array $properties = [])
    {
        // Set to container.
        Container::set('shieldon', $this);

        $this->referer = $_SERVER['HTTP_REFERER'] ?? '';

        $this->setSessionId();

        // At least load a captcha instance. Example is the base one.
        // if (! isset($this->captcha['Example'])) {
        $this->setCaptcha(new \Shieldon\Captcha\Example());
        // }

        if (! empty($properties)) {
            $this->setProperties($properties);
        }

        // Get current session's browsing position.
        $this->currentUrl = $_SERVER['REQUEST_URI'];
    
        $this->setIp('', true);

        /**
         * @since 3.1.0
         */
        include_once __DIR__ . '/helpers.php';
    }

    /**
     * Detect and analyze an user's behavior.
     *
     * @return integer
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
        $logData  = $ipDetail;

        // Counting user pageviews.
        foreach (array_keys($resetPageviews) as $timeUnit) {

            // Each time unit will increase by 1.
            $logData["pageviews_{$timeUnit}"] = $ipDetail["pageviews_{$timeUnit}"] + 1;
            $logData["first_time_{$timeUnit}"] = $ipDetail["first_time_{$timeUnit}"];
        }

        $logData['first_time_flag'] = $ipDetail['first_time_flag'];

        if (! empty($ipDetail['ip'])) {
            $logData['ip']        = $this->ip;
            $logData['session']   = $this->sessionId;
            $logData['hostname']  = $this->ipResolvedHostname;
            $logData['last_time'] = $now;

            /*** HTTP_REFERER ***/

            if ($this->enableRefererCheck) {

                if ($now - $ipDetail['last_time'] > $this->properties['interval_check_referer']) {

                    // Get values from data table. We will count it and save it back to data table.
                    // If an user is already in your website, it is impossible no referer when he views other pages.
                    $logData['flag_empty_referer'] = $ipDetail['flag_empty_referer'] ?? 0;

                    if (empty($this->referer)) {
                        $logData['flag_empty_referer']++;
                        $isFlaggedAsUnusualBehavior = true;
                    }

                    // Ban this IP if they reached the limit.
                    if ($logData['flag_empty_referer'] > $this->properties['limit_unusual_behavior']['referer']) {
                        $this->action(self::ACTION_TEMPORARILY_DENY, self::REASON_EMPTY_REFERER);
                        return self::RESPONSE_TEMPORARILY_DENY;
                    }
                }
            }

            /*** SESSION ***/

            if ($this->enableSessionCheck) {

                if ($now - $ipDetail['last_time'] > $this->properties['interval_check_session']) {

                    // Get values from data table. We will count it and save it back to data table.
                    $logData['flag_multi_session'] = $ipDetail['flag_multi_session'] ?? 0;
                    
                    if ($this->sessionId !== $ipDetail['session']) {

                        // Is is possible because of direct access by the same user many times.
                        // Or they don't have session cookie set.
                        $logData['flag_multi_session']++;
                        $isFlaggedAsUnusualBehavior = true;
                    }

                    // Ban this IP if they reached the limit.
                    if ($logData['flag_multi_session'] > $this->properties['limit_unusual_behavior']['session']) {
                        $this->action(self::ACTION_TEMPORARILY_DENY, self::REASON_TOO_MANY_SESSIONS);
                        return self::RESPONSE_TEMPORARILY_DENY;
                    }
                }
            }

            /*** JAVASCRIPT COOKIE ***/

            // Let's checking cookie created by javascript..
            if ($this->enableCookieCheck) {

                // Get values from data table. We will count it and save it back to data table.
                $logData['flag_js_cookie']   = $ipDetail['flag_js_cookie']   ?? 0;
                $logData['pageviews_cookie'] = $ipDetail['pageviews_cookie'] ?? 0;

                $c = $this->properties['cookie_name'];

                $jsCookie = $_COOKIE[$c] ?? 0;

                // Checking if a cookie is created by JavaScript.
                if (! empty($jsCookie)) {

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
                    $this->action(self::ACTION_TEMPORARILY_DENY, self::REASON_EMPTY_JS_COOKIE);
                    return self::RESPONSE_TEMPORARILY_DENY;
                }

                // Remove JS cookie and reset.
                if ($logData['pageviews_cookie'] > $this->properties['limit_unusual_behavior']['cookie']) {

                    // Reset to 0.
                    $logData['pageviews_cookie'] = 0;
                    $logData['flag_js_cookie']   = 0;

                    // Remove cookie.
                    unset($_COOKIE[$this->properties['cookie_name']]);
                    $this->resetCookie();
                }
            }

            /*** ACCESS FREQUENCY ***/

            if ($this->enableFrequencyCheck) {

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

                            if ($timeUnit === 's') $this->action(self::ACTION_TEMPORARILY_DENY, self::REASON_REACHED_LIMIT_SECOND);
                            if ($timeUnit === 'm') $this->action(self::ACTION_TEMPORARILY_DENY, self::REASON_REACHED_LIMIT_MINUTE);
                            if ($timeUnit === 'h') $this->action(self::ACTION_TEMPORARILY_DENY, self::REASON_REACHED_LIMIT_HOUR);
                            if ($timeUnit === 'd') $this->action(self::ACTION_TEMPORARILY_DENY, self::REASON_REACHED_LIMIT_DAY);
                            
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
                $logData['first_time_flag'] = (! empty($logData['first_time_flag'])) ? $logData['first_time_flag'] : $now;
            }

            // Reset the flagged factor check.
            if (! empty($ipDetail['first_time_flag'])) {
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
            $logData['session']   = $this->sessionId;
            $logData['hostname']  = $this->ipResolvedHostname;
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

        $ipResolvedHostname = $this->ipResolvedHostname;

        $now = time();
    
        if ('' !== $assignIp) {
            $ip = $assignIp;
            $ipResolvedHostname = gethostbyaddr($ip);
        }

        switch ($actionCode) {
            case self::ACTION_ALLOW: // acutally not used.
            case self::ACTION_DENY:  // actually not used.
            case self::ACTION_TEMPORARILY_DENY:
                $logData['log_ip']     = $ip;
                $logData['ip_resolve'] = $ipResolvedHostname;
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
            $log['session_id']  = $this->sessionId;
            $log['action_code'] = $actionCode;
            $log['timesamp']    = $now;

            $this->logger->add($log);
        }
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
        if (isset($this->component[$name]) && ($this->component[$name] instanceof ComponentInterface)) {
            return $this->component[$name];
        }
        return null;
    }

    /**
     * Deal with online sessions.
     *
     * @param bool $checkPassed
     *
     * @return int RESPONSE_CODE
     */
    private function sessionHandler($statusCode): int
    {
        if (self::RESPONSE_ALLOW !== $statusCode) {
            return $statusCode;
        }

        // If you don't enable `limit traffic`, ignore the following steps.
        if (empty($this->isLimitSession)) {
            return self::RESPONSE_ALLOW;

        } else {

            // Get the proerties.
            $limit = (int) ($this->isLimitSession[0] ?? 0);
            $period = (int) ($this->isLimitSession[1] ?? 300);
            $now = time();

            $onlineSessions = $this->driver->getAll('session');
            $sessionPools = [];

            $i = 1;
            $currentSessionOrder = 0;

            //die('<pre>' . var_dump($onlineSessions) . '</pre>');
            if (! empty($onlineSessions)) {
                foreach ($onlineSessions as $k => $v) {
                    $sessionPools[] = $v['id'];
                    $lasttime = (int) $v['time'];
    
                    if ($this->sessionId === $v['id']) {
                        $currentSessionOrder = $i;
                    }
    
                    // Remove session if it expires.
                    if ($now - $lasttime > $period) {
                        $this->driver->delete($v['id'], 'session');
                    }
                    $i++;
                }

                if (0 === $currentSessionOrder) {
                    $currentSessionOrder = $i;
                }
            } else {
                $currentSessionOrder = 0;
            }

            // Count the online sessions.
            $this->sessionCount = count($sessionPools);
            $this->currentSessionOrder = $currentSessionOrder;
            $this->currentWaitNumber = $currentSessionOrder - $limit;

            if (! in_array($this->sessionId, $sessionPools)) {
                $this->sessionCount++;

                // New session, record this data.
                $data['id'] = $this->sessionId;
                $data['ip'] = $this->ip;
                $data['time'] = $now;

                $microtimesamp = explode(' ', microtime());
                $microtimesamp = $microtimesamp[1] . str_replace('0.', '', $microtimesamp[0]);
                $data['microtimesamp'] = $microtimesamp;

                $this->driver->save($this->sessionId, $data, 'session');
            }

            // Online session count reached the limit. So return RESPONSE_LIMIT response code.
            if ($currentSessionOrder >= $limit) {
                return self::RESPONSE_LIMIT;
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
    private function setSessionId(string $sessionId = ''): void
    {
        if ('' !== $sessionId) {
            $this->sessionId = $sessionId;
        } else {
            if ((php_sapi_name() !== 'cli')) {
                if ($this->enableSessionCheck) {
                    if (session_status() === PHP_SESSION_NONE) {
                        session_start();
                    }
                    if (! $this->sessionId) {
                        $this->sessionId = session_id();
                    }
                }
            }
        }
    }

    /**
     * Reset cookie.
     *
     * @return void
     */
    private function resetCookie(): void
    {
        if ((php_sapi_name() !== 'cli')) {
            setcookie($this->properties['cookie_name'], '', -1, '/');
        }
    }

    // @codeCoverageIgnoreEnd

    /*
    | -------------------------------------------------------------------
    |                            Public APIs
    | -------------------------------------------------------------------
    |  The public APIs can be chaining yet `SetDriver` must be the first 
    |  and `run` must be the last.
    */

    /**
     * Set a data driver.
     *
     * @param DriverProvider $driver Query data from the driver you choose to use.
     *
     * @return self
     */
    public function setDriver(DriverProvider $driver): self
    {
        if ($driver instanceof DriverProvider) {
            $this->driver = $driver;
        }

        return $this;
    }

    /**
     * Set a action log logger.
     *
     * @param ActionLogger $logger
     *
     * @return self
     */
    public function setLogger(ActionLogger $logger): self
    {
        if ($logger instanceof ActionLogger) {
            $this->logger = $logger;
        }

        return $this;
    }

    /**
     * Set a messenger
     *
     * @param MessengerInterfa $instance
     *
     * @return self
     */
    public function setMessenger(MessengerInterface $instance): self
    {
        $this->messengers[] = $instance;

        return $this;
    }

    /**
     * For first time installation only. This is for creating data tables automatically.
     * Turning it on will check the data tables exist or not at every single pageview, 
     * it's not good for high traffic websites.
     *
     * @param bool $bool
     * 
     * @return self
     */
    public function createDatabase(bool $bool): self
    {
        $this->autoCreateDatabase = $bool;

        return $this;
    }

    /**
     * Set a data channel.
     *
     * @param string $channel Oh, it is a channel.
     *
     * @return self
     */
    public function setChannel(string $channel): self
    {
        if (! $this->driver instanceof DriverProvider) {
            throw new LogicException('setChannel method requires setDriver set first.');
        } else {
            $this->driver->setChannel($channel);
        }

        return $this;
    }

    /**
     * Set a captcha.
     *
     * @param CaptchaInterface $instance
     *
     * @return self
     */
    public function setCaptcha(CaptchaInterface $instance): self
    {
        if ($instance instanceof CaptchaInterface) {
            // $class = get_class($instance);
            // $class = substr($class, strrpos($class, '\\') + 1);
            // $this->captcha[$class] = $instance;

            $this->captcha[] = $instance;
        }

        return $this;
    }

    /**
     * Return the result from Captchas.
     *
     * @return bool
     */
    public function captchaResponse(): bool
    {
        foreach ($this->captcha as $captcha) {
            if (! $captcha->response()) {
                return false;
            }
        }

        if (! empty($this->isLimitSession)) {
            $this->result = $this->sessionHandler(self::RESPONSE_ALLOW);
        }

        return true;
    }

    /**
     * Set a commponent.
     * Sheildon needs commponents to work.
     *
     * @param ComponentProvider $instance
     *
     * @return self
     */
    public function setComponent(ComponentProvider $instance): self
    {
       
        if ($instance instanceof ComponentProvider) {
            $class = get_class($instance);

            
            $class = substr($class, strrpos($class, '\\') + 1);
            $this->component[$class] =& $instance;
  
        }

        return $this;
    }

    /**
     * Ban an IP.
     *
     * @param string $ip
     *
     * @return void
     */
    public function ban(string $ip = ''): self
    {
        if ('' === $ip) {
            $ip = $this->ip;
        }
 
        $this->action(self::ACTION_DENY, self::REASON_MANUAL_BAN, $ip);

        return $this;
    }

    /**
     * Unban an IP.
     *
     * @param string $ip
     *
     * @return self
     */
    public function unban(string $ip = ''): self
    {
        if ('' === $ip) {
            $ip = $this->ip;
        }

        $this->action(self::ACTION_UNBAN, self::REASON_MANUAL_BAN, $ip);
        $this->_log(self::ACTION_UNBAN);

        $this->result = self::RESPONSE_ALLOW;

        return $this;
    }

    /**
     * @param string $key
     * @param mixed  $value
     *
     * @return self
     */
    public function setProperty(string $key = '', $value = ''): self
    {
        if (isset($this->properties[$key])) {
            $this->properties[$key] = $value;
        }

        return $this;
    }

    /**
     * @param array $settings
     *
     * @return self
     */
    public function setProperties(array $settings): self
    {
        foreach ($this->properties as $k => $v) {
            if (isset($settings[$k])) {
                $this->properties[$k] = $settings[$k];
            }
        }

        return $this;
    }

    /**
     * Strict mode.
     * 
     * @param bool $bool Set true to enble strict mode, false to disable it overwise.
     *
     * @return self
     */
    public function setStrict(bool $bool): self
    {
        $this->strictMode = $bool;

        return $this;
    }

    /**
     * Limt online sessions.
     *
     * @param int $count
     * @param int $period
     *
     * @return self
     */
    public function limitSession(int $count = 1000, int $period = 300): self
    {
        $this->isLimitSession = [$count, $period];

        return $this;
    }

    /**
     * Set result page's HTML.
     *
     * @param string $content The HTML text.
     * @param string $type    The page type: stop, limit, deny.
     *
     * @return self
     */
    public function setView(string $content, string $type): self
    {
        if ('limit' === $type || 'stop' === $type || 'deny' === $type) {
            $this->html[$type] = $content;
        }

        return $this;
    }

    /**
     * Customize the dialog UI.
     *
     * @since 3.1.0
     *
     * @return self
     */
    public function setDialogUI(array $settings): self
    {
        $this->dialogUI = $settings;

        return $this;
    }

    /**
     * Output result page.
     *
     * @param int $httpStatus
     * @param bool $echo
     *
     * @echo string
     */
    public function output(int $httpStatus = 0, bool $echo = true): string
    {
        $output = '';

        if (self::RESPONSE_TEMPORARILY_DENY === $this->result) {
            $type = 'stop';
        } elseif (self::RESPONSE_LIMIT === $this->result) {
            $type = 'limit';
        } elseif (self::RESPONSE_DENY === $this->result) {
            
            $type = 'deny';
        } else {

            // @codeCoverageIgnoreStart

            return '';

            // @codeCoverageIgnoreEnd
        }

        header('X-Protected-By: shieldon.io');

        /**
         * @var string The language of output UI. It is used on views.
         */
        $langCode = $_SESSION['shieldon_ui_lang'] ?? 'en';

        /**
         * @var bool Show online session count. It is used on views.
         */
        $showOnlineInformation = true;

        /**
         * @var bool Show user information such as IP, user-agent, device name.
         */
        $showUserInformation = true;

        // Use default template if there is no custom HTML template.
        if (empty($this->html[$type])) {

            $viewPath = self::SHIELDON_DIR . '/../../templates/' . $type . '.php';

            if (empty($this->properties['display_online_info'])) {
                $showOnlineInformation = false;
            }

            if (empty($this->properties['display_user_info'])) {
                $showUserInformation = false;
            }

            if ($showUserInformation) {
                $dialoguserinfo['ip'] = $this->ip;
                $dialoguserinfo['rdns'] = $this->ipResolvedHostname;
                $dialoguserinfo['user_agent'] = $_SERVER['HTTP_USER_AGENT'] ?? '';
            }

            if (file_exists($viewPath)) {

                if (! defined('SHIELDON_VIEW')) {
                    define('SHIELDON_VIEW', true);
                }

                $ui = [
                    'background_image' => $this->dialogUI['background_image'] ?? '',
                    'bg_color'         => $this->dialogUI['bg_color'] ?? '#ffffff',
                    'header_bg_color'  => $this->dialogUI['header_bg_color'] ?? '#212531',
                    'header_color'     => $this->dialogUI['header_color'] ?? '#ffffff',
                    'shadow_opacity'   => $this->dialogUI['shadow_opacity'] ?? '0.2',
                ];

                $css = require self::SHIELDON_DIR . '/../../templates/css-default.php';

                ob_start();
                require $viewPath;
                $output = ob_get_contents();
                ob_end_clean();

                unset($css, $lang, $ui);
            }
        } else {
    
            // @codeCoverageIgnoreStart

            $output = $this->html[$type];

            if ('stop' === $type) {

                // Build captcha form.
                ob_start();

                foreach ($this->captcha as $captcha) {
                    echo $captcha->form();
                }

                $captchaFormElements = ob_get_contents();
                ob_end_clean();

                // Inject captcha HTML form elements into custom template.
                $output = str_replace('{{captcha}}', $captchaFormElements, $output);
            }

            // @codeCoverageIgnoreEnd
        }

        // Remove unused variable notices generated from PHP intelephense.
        unset($langCode, $showOnlineInformation, $showLineupInformation, $showUserInformation);

        if ($echo) {

            // @codeCoverageIgnoreStart

            if (0 !== $httpStatus) {
                http_response_code($httpStatus);
            }

            echo $output;
            exit;

            // @codeCoverageIgnoreEnd
        } else {
            return $output;
        }
    }

    /**
     * Run, run, run!
     *
     * Check the rule tables first, if an IP address has been listed.
     * Call function filter() if an IP address is not listed in rule tables.
     *
     */
    public function run(): int
    {
        // Ignore the excluded urls.
        if (! empty($this->excludedUrls)) {
            foreach ($this->excludedUrls as $url) {
                if (0 === strpos($this->currentUrl, $url)) {
                    return $this->result = self::RESPONSE_ALLOW;
                }
            }
        }

        // Execute closure functions.
        foreach ($this->closures as $closure) {
            $closure();
        }

        $result = $this->_run();

        if ($result !== self::RESPONSE_ALLOW) {

            // Current session did not pass the CAPTCHA, it is still stuck in CAPTCHA page.
            $actionCode = self::LOG_CAPTCHA;

            // If current session's respone code is RESPONSE_DENY, record it as `blacklist_count` in our logs.
            // It is stuck in warning page, not CAPTCHA.
            if ($result === self::RESPONSE_DENY) {
                $actionCode = self::LOG_BLACKLIST;
            }

            if ($result === self::RESPONSE_LIMIT) {
                $actionCode = self::LOG_LIMIT;
            }

            $this->_log($actionCode);

        } else {

            $this->_log(self::LOG_PAGEVIEW);
        }

        return $result;
    }

    /**
     * Logger.
     *
     * @param integer $actionCode
     *
     * @return void
     */
    private function _log(int $actionCode): void
    {
        if (null !== $this->logger) {

            // Just count the page view.
            $logData['ip']          = $this->getIp();
            $logData['session_id']  = $this->getSessionId();
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
     * @return int RESPONSE_CODE
     */
    private function _run(): int
    {
        $this->driver->init($this->autoCreateDatabase);

        foreach (array_keys($this->component) as $name) {
            $this->component[$name]->setIp($this->ip);
            $this->component[$name]->setRdns($this->ipResolvedHostname);
            $this->component[$name]->setStrict($this->strictMode);
        }

        /*
        |--------------------------------------------------------------------------
        | Stage - Looking for rule table.
        |--------------------------------------------------------------------------
        */

        $ipRule = $this->driver->get($this->ip, 'rule');

        if (! empty($ipRule)) {

            $ruleType = (int) $ipRule['type'];

            if ($ruleType === self::ACTION_ALLOW) {
                $this->isAllowedRule = true;
                
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
                $attemptPeriod = $this->properties['record_attempt_interval'];
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

                /**
                 * @since 3.3.0
                 */
                if ($this->properties['deny_attempt_enable']['data_circle']) {

                    if ($ruleType === self::ACTION_TEMPORARILY_DENY) {

                        $isUpdatRuleTable = true;

                        $buffer = $this->properties['deny_attempt_buffer']['data_circle'];

                        if ($attempts >= $buffer) {
                            $isTriggerMessenger = true;

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
                            $isTriggerMessenger = true;

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

                    try {
                        foreach ($this->messengers as $messenger) {
                            $messenger->send(
                                \Shieldon\Helper\__(
                                    'core', 'messenger_notification_subject',
                                    'Notification for {0}',
                                    array($this->ip)
                                ),
                                $prepareMessageData
                            );
                        }

                    } catch (RuntimeException $e) {
                        // echo $e->getMessage();
                        // Do not throw error, becasue the third-party services might be unavailable.
                    }
                }

                // For an incoming request already in the rule list, return the rule type immediately.
                return $this->result = $ruleType;
            }
        }

        if ($this->isAllowedRule) {

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
                        $this->action(self::ACTION_ALLOW, self::REASON_IS_GOOGLE);

                    } elseif ($this->getComponent('TrustedBot')->isBing()) {

                        // Add current IP into allowed list, because it is from real Bing domain.
                        $this->action(self::ACTION_ALLOW, self::REASON_IS_BING);

                    } elseif ($this->getComponent('TrustedBot')->isYahoo()) {

                        // Add current IP into allowed list, because it is from real Yahoo domain.
                        $this->action(self::ACTION_ALLOW, self::REASON_IS_YAHOO);

                    } else {

                        // Add current IP into allowed list, because you trust it.
                        // You have already defined it in the settings.
                        $this->action(self::ACTION_ALLOW, self::REASON_IS_SEARCH_ENGINE);
                    }

                    // Allowed robots not join to our traffic handler.
                    return $this->result = self::RESPONSE_ALLOW;
                }

                // After `isAllowed()` executed, we can check if the currect access is fake by `isFakeRobot()`.
                if ($this->getComponent('TrustedBot')->isFakeRobot()) {
                    $this->action(self::ACTION_DENY, self::REASON_COMPONENT_TRUSTED_ROBOT);

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

                if (! empty($result)) {
    
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
                    $this->action(self::ACTION_DENY, $component->getDenyStatusCode());
    
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

        if ($this->enableFiltering) {
            return $this->result = $this->sessionHandler($this->filter());
        }

        return $this->result = $this->sessionHandler(self::RESPONSE_ALLOW);
    }

    /**
     * Set the filters.
     *
     * @param array $settings filter settings.
     *
     * @return self
     */
    public function setFilters($settings): self
    {
        $requiredFilters = [
            'cookie'    => true,
            'session'   => true,
            'frequency' => true,
            'referer'   => true,
        ];

        foreach ($requiredFilters as $k => $v) {
            if (isset($settings[$k])) {
                $u = 'enable' . ucfirst($k) . 'Check';
                $this->{$u} = $settings[$k] ?? false;
            }
        }

        return $this;
    }

    /**
     * Set a filter.
     *
     * @param string $filterName
     * @param bool   $value
     * @since 3.0.0
     *
     * @return self
     */
    public function setFilter($filterName, $value): self
    {
        $filters = [
            'cookie'    => true,
            'session'   => true,
            'frequency' => true,
            'referer'   => true,
        ];

        if (isset($filters[$filterName])) {
            $u = 'enable' . ucfirst($filterName) . 'Check';
            $this->{$u} = $value;
        }

        return $this;
    }

    /**
     * Disable filitering.
     *
     * @return self
     */
    public function disableFiltering(): self
    {
        $this->enableFiltering = false;

        return $this;
    }

    /**
     * Get online people count. If enable limitSession.
     *
     * @return integer
     */
    public function getSessionCount(): int
    {
        return $this->sessionCount;
    }

    /**
     * Get Session Id.
     *
     * @return string
     */
    public function getSessionId(): string
    {
        if (! empty($this->sessionId)) {
            return $this->sessionId;
        }

        if ((php_sapi_name() === 'cli')) {
            return '_php_cli_';
        }

        // @codeCoverageIgnoreStart
        return $this->sessionId;
        // @codeCoverageIgnoreEnd
    }

    /**
     * Set the URLs you want them to be excluded them from protection.
     *
     * @param array $urls
     * @since 3.0.0
     *
     * @return self
     */
    public function setExcludedUrls(array $urls = []): self
    {
        $this->excludedUrls = $urls;
        return $this;
    }

    /**
     * Set a closure function.
     *
     * @param string  $key
     * @param Closure $closure
     * @since 3.0.0
     *
     * @return self
     */
    public function setClosure(string $key, Closure $closure): self
    {
        $this->closures[$key] = $closure;

        return $this;
    }

    /**
     * Return current URL.
     *
     * @since 3.0.0
     *
     * @return string
     */
    public function getCurrentUrl(): string
    {
        return $this->currentUrl;
    }

    /**
     * Print javascript snippet in your webpages.
     * This snippet generate cookie on client's browser,then we check the cookie to identify the client is a rebot or not.
     *
     * @return string
     */
    public function outputJsSnippet(): string
    {
        $tmpCookieName = $this->properties['cookie_name'];
        $tmpCookieDomain = $this->properties['cookie_domain'];
        $tmpCookieValue = $this->properties['cookie_value'];

        $jsString = <<<"EOF"

            <script>
                var d = new Date();
                d.setTime(d.getTime()+(7*24*60*60*1000));
                var expires = "expires="+d.toUTCString();
                document.cookie = "{$tmpCookieName}={$tmpCookieValue};domain=.{$tmpCookieDomain};"+expires;
            </script>
EOF;
        return $jsString;
    }

    /**
     * Tell Shieldon what type is that Shieldon.
     * 
     * @param $type Type.
     * @since 3.0.0
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
