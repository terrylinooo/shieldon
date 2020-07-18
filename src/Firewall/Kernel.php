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
use Shieldon\Firewall\Driver\DriverProvider;
use Shieldon\Firewall\Helpers;
use Shieldon\Firewall\HttpFactory;
use Shieldon\Firewall\Log\ActionLogger;
use Shieldon\Firewall\Utils\Container;
use Shieldon\Firewall\Kernel\IpTrait;
use Shieldon\Firewall\Kernel\FilterTrait;
use Shieldon\Messenger\Messenger\MessengerInterface;
use function Shieldon\Firewall\__;
use function Shieldon\Firewall\get_cpu_usage;
use function Shieldon\Firewall\get_default_properties;
use function Shieldon\Firewall\get_memory_usage;
use function Shieldon\Firewall\get_request;
use function Shieldon\Firewall\get_response;
use function Shieldon\Firewall\get_session;


use Closure;
use InvalidArgumentException;
use LogicException;
use RuntimeException;
use function file_exists;
use function file_put_contents;
use function filter_var;
use function get_class;
use function gethostbyaddr;
use function is_dir;
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
    use FilterTrait;

    // Reason codes (allow)
    const REASON_IS_SEARCH_ENGINE = 100;
    const REASON_IS_GOOGLE = 101;
    const REASON_IS_BING = 102;
    const REASON_IS_YAHOO = 103;
    const REASON_IS_SOCIAL_NETWORK = 110;
    const REASON_IS_FACEBOOK = 111;
    const REASON_IS_TWITTER = 112;

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
     * The collection of \Shieldon\Firewall\Captcha\CaptchaInterface
     *
     * @var array
     */
    protected $captcha = [];

    /**
     * The ways Shieldon send a message to when someone has been blocked.
     * The collection of \Shieldon\Messenger\Messenger\MessengerInterface
     *
     * @var array
     */
    protected $messenger = [];

    /**
     * Is to limit traffic?
     *
     * @var array
     */
    protected $sessionLimit = [

        // How many sessions will be available?
        // 0 = no limit.
        'count' => 0,

        // How many minutes will a session be availe to visit?
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
     * The events.
     *
     * @var array
     */
    protected $event = [

        // Update rule table when this value true.
        'update_rule_table' => false,

        // Send notifications when this value true.
        'trigger_messengers' => false,
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
     * The message that will be sent to the third-party API.
     *
     * @var string
     */
    protected $msgBody = '';

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
            $logData = [];
            $logData['ip'] = $this->getIp();
            $logData['session_id'] = get_session()->get('id');
            $logData['action_code'] = $actionCode;
            $logData['timesamp'] = time();
    
            $this->logger->add($logData);
        }
    }

    /**
     * Initialize components.
     *
     * @return void
     */
    private function initComponents()
    {
        foreach (array_keys($this->component) as $name) {
            $this->component[$name]->setIp($this->ip);
            $this->component[$name]->setRdns($this->rdns);

            // Apply global strict mode to all components by `strictMode()` if nesscessary.
            if (isset($this->strictMode)) {
                $this->component[$name]->setStrict($this->strictMode);
            }
        }
    }

    /**
     * Look up the rule table.
     *
     * If a specific IP address doesn't exist, return false. 
     * Otherwise, return true.
     *
     * @return bool
     */
    private function isRuleTable()
    {
        $ipRule = $this->driver->get($this->ip, 'rule');

        if (empty($ipRule)) {
            return false;
        }

        $ruleType = (int) $ipRule['type'];

        // Apply the status code.
        $this->result = $ruleType;

        if ($ruleType === self::ACTION_ALLOW) {
            return true;
        }

        // Current visitor has been blocked. If he still attempts accessing the site, 
        // then we can drop him into the permanent block list.
        $attempts = $ipRule['attempts'] ?? 0;
        $attempts = (int) $attempts;
        $now = time();
        $logData = [];
        $handleType = 0;

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

        if ($ruleType === self::ACTION_TEMPORARILY_DENY) {
            $ratd = $this->determineAttemptsTemporaryDeny($logData, $handleType, $attempts);
            $logData = $ratd['log_data'];
            $handleType = $ratd['handle_type'];
        }

        if ($ruleType === self::ACTION_DENY) {
            $rapd = $this->determineAttemptsPermanentDeny($logData, $handleType, $attempts);
            $logData = $rapd['log_data'];
            $handleType = $rapd['handle_type'];
        }

        // We only update data when `deny_attempt_enable` is enable.
        // Because we want to get the last visited time and attempt counter.
        // Otherwise, we don't update it everytime to avoid wasting CPU resource.
        if ($this->event['update_rule_table']) {
            $this->driver->save($this->ip, $logData, 'rule');
        }

        // Notify this event to messenger.
        if ($this->event['trigger_messengers']) {
            $this->prepareMessengerBody($logData, $handleType);
        }

        return true;
    }

    /**
     * Record the attempts when the user is temporarily denied by rule table.
     *
     * @param array $logData
     * @param int   $handleType
     * @param int   $attempts
     * 
     * @return array
     */
    private function determineAttemptsTemporaryDeny(array $logData, int $handleType, int $attempts): array
    {
        if ($this->properties['deny_attempt_enable']['data_circle']) {
            $this->event['update_rule_table'] = true;

            $buffer = $this->properties['deny_attempt_buffer']['data_circle'];

            if ($attempts >= $buffer) {

                if ($this->properties['deny_attempt_notify']['data_circle']) {
                    $this->event['trigger_messengers'] = true;
                }

                $logData['type'] = self::ACTION_DENY;

                // Reset this value for next checking process - iptables.
                $logData['attempts'] = 0;
                $handleType = 1;
            }
        }

        return [
            'log_data' => $logData,
            'handle_type' => $handleType,
        ];
    }

    /**
     * Record the attempts when the user is permanently denied by rule table.
     *
     * @param array $logData
     * @param int   $handleType
     * @param int   $attempts
     * 
     * @return array
     */
    private function determineAttemptsPermanentDeny(array $logData, int $handleType, int $attempts): array
    {
        if ($this->properties['deny_attempt_enable']['system_firewall']) {
            $this->event['update_rule_table'] = true;

            // For the requests that are already banned, but they are still attempting access, that means 
            // that they are programmably accessing your website. Consider put them in the system-layer fireall
            // such as IPTABLE.
            $bufferIptable = $this->properties['deny_attempt_buffer']['system_firewall'];

            if ($attempts >= $bufferIptable) {

                if ($this->properties['deny_attempt_notify']['system_firewall']) {
                    $this->event['trigger_messengers'] = true;
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

        return [
            'log_data' => $logData,
            'handle_type' => $handleType,
        ];
    }

    /**
     * Prepare the message body for messenger modules to sent.
     *
     * @param array $logData
     * @param int   $handleType
     * 
     * @return void
     */
    private function prepareMessengerBody(array $logData, int $handleType): void
    {
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

        $this->msgBody = $message;
    }

    /**
     * Check if current IP is trusted or not.
     *
     * @return bool
     */
    private function isTrustedBot()
    {
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
                $this->result = self::RESPONSE_ALLOW;
                return true;
            }
        }
        return false;
    }

    /**
     * Check whether the IP is fake search engine or not.
     * The method "isTrustedBot()" must be executed before this method.
     *
     * @return bool
     */
    private function isFakeRobot(): bool
    {
        if ($this->getComponent('TrustedBot')) {
            if ($this->getComponent('TrustedBot')->isFakeRobot()) {
                $this->action(
                    self::ACTION_DENY,
                    self::REASON_COMPONENT_TRUSTED_ROBOT
                );
                $this->result = self::RESPONSE_DENY;
                return true;
            }
        }
        return false;
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

        $this->initComponents();

        /*
        |--------------------------------------------------------------------------
        | Stage - Looking for rule table.
        |--------------------------------------------------------------------------
        */

        if ($this->isRuleTable()) {
            return $this->result;
        }

        /*
        |--------------------------------------------------------------------------
        | Statge - Detect popular search engine.
        |--------------------------------------------------------------------------
        */

        if ($this->isTrustedBot()) {
            return $this->result;
        }

        if ($this->isFakeRobot()) {
            return $this->result;
        }
        
        /*
        |--------------------------------------------------------------------------
        | Stage - IP component.
        |--------------------------------------------------------------------------
        */

        if ($this->getComponent('Ip')) {

            $result = $this->getComponent('Ip')->check();
            $actionCode = self::ACTION_DENY;

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
        $isFlagged = false;

        // Fetch an IP data from Shieldon log table.
        $ipDetail = $this->driver->get($this->ip, 'filter');

        $ipDetail = $this->driver->parseData($ipDetail, 'filter');
        $logData = $ipDetail;

        // Counting user pageviews.
        foreach (array_keys($this->filterResetStatus) as $unit) {

            // Each time unit will increase by 1.
            $logData['pageviews_' . $unit] = $ipDetail['pageviews_' . $unit] + 1;
            $logData['first_time_' . $unit] = $ipDetail['first_time_' . $unit];
        }

        $logData['first_time_flag'] = $ipDetail['first_time_flag'];

        if (!empty($ipDetail['ip'])) {
            $logData['ip'] = $this->ip;
            $logData['session'] = get_session()->get('id');
            $logData['hostname'] = $this->rdns;
            $logData['last_time'] = $now;

            // Filter: HTTP referrer information.
            $filterReferer = $this->filterReferer($logData, $ipDetail, $isFlagged);
            $isFlagged = $filterReferer['is_flagged'];
            $logData = $filterReferer['log_data'];

            if ($filterReferer['is_reject']) {
                return self::RESPONSE_TEMPORARILY_DENY;
            }

            // Filter: Session.
            $filterSession = $this->filterSession($logData, $ipDetail, $isFlagged);
            $isFlagged = $filterSession['is_flagged'];
            $logData = $filterSession['log_data'];

            if ($filterSession['is_reject']) {
                return self::RESPONSE_TEMPORARILY_DENY;
            }

            // Filter: JavaScript produced cookie.
            $filterCookie = $this->filterCookie($logData, $ipDetail, $isFlagged);
            $isFlagged = $filterCookie['is_flagged'];
            $logData = $filterCookie['log_data'];

            if ($filterCookie['is_reject']) {
                return self::RESPONSE_TEMPORARILY_DENY;
            }

            // Filter: frequency.
            $filterFrequency = $this->filterFrequency($logData, $ipDetail, $isFlagged);
            $isFlagged = $filterFrequency['is_flagged'];
            $logData = $filterFrequency['log_data'];

            if ($filterFrequency['is_reject']) {
                return self::RESPONSE_TEMPORARILY_DENY;
            }

            // Is fagged as unusual beavior? Count the first time.
            if ($isFlagged) {
                $logData['first_time_flag'] = (!empty($logData['first_time_flag'])) ? $logData['first_time_flag'] : $now;
            }

            // Reset the flagged factor check.
            if (!empty($ipDetail['first_time_flag'])) {
                if ($now - $ipDetail['first_time_flag'] >= $this->properties['time_reset_limit']) {
                    $logData['flag_multi_session'] = 0;
                    $logData['flag_empty_referer'] = 0;
                    $logData['flag_js_cookie'] = 0;
                }
            }

            $this->driver->save($this->ip, $logData, 'filter');

        } else {

            // If $ipDetail[ip] is empty.
            // It means that the user is first time visiting our webiste.
            $this->initializeFilterLogData($logData);
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
        $logData = [];
    
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
        $this->driver->delete($ip, 'filter');

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
     * @param int $statusCode The response code.
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

    /**
     * When the user is first time visiting our webiste.
     * Initialize the log data.
     * 
     * @param array $logData The user's log data.
     *
     * @return void
     */
    protected function initializeFilterLogData($logData)
    {
        $now = time();

        $logData['ip']        = $this->ip;
        $logData['session']   = get_session()->get('id');
        $logData['hostname']  = $this->rdns;
        $logData['last_time'] = $now;

        foreach (array_keys($this->filterResetStatus) as $unit) {
            $logData['first_time_' . $unit] = $now;
        }

        $this->driver->save($this->ip, $logData, 'filter');
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
        static $i = 2;

        $class = $this->getClassName($instance);

        if ($instance instanceof DriverProvider) {
            $this->driver = $instance;
            $this->registrar[0] = [
                'category' => 'driver',
                'class' => $class,
            ];
        }

        if ($instance instanceof ActionLogger) {
            $this->logger = $instance;
            $this->registrar[1] = [
                'category' => 'logger',
                'class' => $class,
            ];
        }

        if ($instance instanceof CaptchaInterface) {
            $this->captcha[$class] = $instance;
            $this->registrar[$i] = [
                'category' => 'captcha',
                'class' => $class,
            ];
            $i++;
        }

        if ($instance instanceof ComponentProvider) {
            $this->component[$class] = $instance;
            $this->registrar[$i] = [
                'category' => 'component',
                'class' => $class,
            ];
            $i++;
        }

        if ($instance instanceof MessengerInterface) {
            $this->messenger[] = $instance;
            $this->registrar[$i] = [
                'category' => 'messenger',
                'class' => $class,
            ];
            $i++;
        }
    }

    /**
     * Remove registered classes from the Kernel.
     *
     * @param string $category  The class category.
     * @param string $className The class name.
     *
     * @return void
     */
    public function remove(string $category, string $className = '')
    {
        if ($className !== '') {
            foreach ($this->getRegistrar() as $k => $v) {
                if ($category === $v['category'] && $className === $v['class']) {
                    if (is_array($this->{$category})) {
                        foreach ($this->{$category} as $k2 => $instance) {
                            if ($this->getClassName($instance) === $className) {
                                unset($this->{$category}[$k2]);
                            }
                        }
                    } else {
                        $this->{$category} = null;
                    }
                    unset($this->registrar[$k]);
                }
            }
        } else {
            foreach ($this->getRegistrar() as $k => $v) {
                if ($category === $v['category']) {
                    if (is_array($this->{$category})) {
                        $this->{$category} = [];
                    } else {
                        $this->{$category} = null;
                    }
                    unset($this->registrar[$k]);
                }
            }
        }
    }

    /**
     * Fetch the class list from registrar.
     *
     * @return array
     */
    public function getRegistrar(): array
    {
        return $this->registrar;
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
        if (!is_dir($directory)) {
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
     * Get a class name without namespace string.
     *
     * @param object $instance Class
     * 
     * @return void
     */
    protected function getClassName($instance): string
    {
        $class = get_class($instance);
        return substr($class, strrpos($class, '\\') + 1); 
    }

    /**
     * Respond the result.
     *
     * @return ResponseInterface
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
            // @codeCoverageIgnoreStart
            return $response;
            // @codeCoverageIgnoreEnd
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

 
        if (!empty($this->msgBody)) {
 
            // @codeCoverageIgnoreStart

            try {
                foreach ($this->messenger as $messenger) {
                    $messenger->setTimeout(2);
                    $messenger->send($this->msgBody);
                }
            } catch (RuntimeException $e) {
                // Do not throw error, becasue the third-party services might be unavailable.
            }

            // @codeCoverageIgnoreEnd
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
     * @param string $type The type of configuration.
     *                     demo | managed | config
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
