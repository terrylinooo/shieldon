<?php declare(strict_types=1);

/*
 * @name        Shieldon
 * @author      Terry Lin
 * @link        https://github.com/terrylinooo/shieldon
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

use Shieldon\Driver\DriverProvider;
use Shieldon\Component\ComponentInterface;
use Shieldon\Captcha\CaptchaInterface;

use LogicException;
use UnexpectedValueException;

use function get_class;
use function gethostbyaddr;
use function session_id;
use function strrpos;
use function substr;


class Shieldon
{
    use IpTrait;

    // Reason codes (allow)
    public const CODE_IS_SEARCH_ENGINE = 100;
    public const CODE_IS_GOOGLE = 101;
    public const CODE_IS_BING = 102;
    public const CODE_IS_YAHOO = 103;

    // Reason codes (deny)
    public const CODE_TOO_MANY_SESSIONS = 1;
    public const CODE_TOO_MANY_ACCESSES = 2;
    public const CODE_EMPTY_JS_COOKIE = 3;
    public const CODE_EMPTY_REFERER = 4;

    public const CODE_REACHED_LIMIT_DAY = 11;
    public const CODE_REACHED_LIMIT_HOUR = 12;
    public const CODE_REACHED_LIMIT_MINUTE = 13;
    public const CODE_REACHED_LIMIT_SECOND = 14;

    public const CODE_MANUAL_BAN = 99;

    // Action codes.
    public const ACTION_ALLOW = 1;
    public const ACTION_DENY = 0;
    public const ACTION_UNBAN = 9;

    // Result codes.
    public const RESPONSE_DENY = 0;
    public const RESPONSE_ALLOW = 1;
    public const RESPONSE_LIMIT = 2;

    // Shieldon directory.
    private const SHIELDON_DIR = __DIR__;

    // Most of web crawlers do not render JavaScript, they only get text content they want,
    // so we can check if the cookie can be created by JavaScript.
    // This is hard to prevent headless browser robots, but it can stop probably 70% poor robots.
    private $enableJsCookieCheck = false;

    // Every unique user has an unique session, but if an user creates different sessions in every connection..
    // that means the user's browser doesn't support cookie.
    // It is almost impossible that modern browsers don't support cookie, so we suspect the user is a robot or web crawler,
    // that is why we need session cookie check.
    private $enableSessionCheck = true;

    // Check how many pageviews an user made in a short period time.
    // For example, limit an user can only view 30 pages in 60 minutes.
    private $enableFrequencyCheck = true;

    // Even we can't get HTTP_REFERER information from users come from Google search,
    // but if an user checks any internal link on your website, the user's browser will generate HTTP_REFERER information.
    // If an user view many pages on your website without HTTP_REFERER information, that means the user is a web crawler 
    // and it directly downloads your web pages.
    private $enableRefererCheck = true;

    // If you don't want Shieldon to detect bad robots or crawlers, you can set it FALSE;
    // In this case AntiScriping can still deny users by querying rule table (in MySQL, or Redis, etc.) and $denyIpPool (Array)
    private $enableFiltering = true;

    // default settings
    private $properties = [
        'time_unit_quota'        => ['s' => 2, 'm' => 10, 'h' => 30, 'd' => 60],
        'time_reset_limit'       => 3600,
        'interval_check_referer' => 5,
        'interval_check_session' => 30,
        'limit_unusual_behavior' => ['cookie' => 5, 'session' => 5, 'referer' => 10],
        'cookie_name'            => 'ssjd',
        'cookie_domain'          => '',
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
    protected $referer = '';

    /**
     * Driver for storing data.
     *
     * @var DriverInterface
     */
    protected $driver = null;

    /**
     * Container for Shieldon components.
     *
     * @var Interface
     */
    protected $component = [];

    /**
     * Container for captcha addons.
     *
     * @var Interface
     */
    protected $captcha = [];

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
    protected $sessionId = null;

    /**
     * Is this IP in the rule list?
     *
     * @var bool
     */
    private $isRuleList = false;

    /**
     * Is to limit traffic? This will recond online sessions.
     *
     * @var bool
     */
    private $isLimitSession = [];

    /**
     * Result.
     *
     * @var int
     */
    private $result = 1;

    /**
     * Constructor.
     * 
     * @return void
     */
    public function __construct()
    {
        $this->referer = $_SERVER['HTTP_REFERER'] ?? '';

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

        // At least load a captcha instance. Example is the base one.
        if (! isset($this->captcha['Example'])) {
            $this->setCaptcha(new \Shieldon\Captcha\Example());
        }

        $this->setIp();
    }

    /**
     * Detect and analyze an user's behavior.
     *
     * @return bool
     */
    protected function detect()
    {
        $now = time();
        $logData = [];

        $resetPageviews = [
            's' => false, // second.
            'm' => false, // minute.
            'h' => false, // hour.
            'd' => false, // day.
        ];

        // Fetch an IP data from Shieldon log table.
        $ipDetail = $this->driver->get($this->ip, 'log');
        $ipDetail = $this->driver->parseData($ipDetail, 'log');

        // Counting user pageviews.
        foreach ($resetPageviews as $timeUnit => $valueNotUsed) {

            // Each time unit will increase by 1.
            $logData["pageviews_{$timeUnit}"] = $ipDetail["pageviews_{$timeUnit}"] + 1;
            $logData["first_time_{$timeUnit}"] = $ipDetail["first_time_{$timeUnit}"];
        }

        $logData['first_time_flag'] = $ipDetail['first_time_flag'];

        if (! empty($ipDetail['ip'])) {

            $logData['ip']             = $this->ip;
            $logData['session']        = $this->sessionId;
            $logData['hostname']       = $this->ipResolvedHostname;
            $logData['last_time']      = $now;
            $logData['flag_js_cookie'] = 0;

            /*** HTTP_REFERER ***/

            if ($this->enableRefererCheck) {

                if ($now - $ipDetail['last_time'] <= $this->properties['interval_check_referer']) {

                    // Get values from data table. We will count it and save it back to data table.
                    // If an user is already in your website, it is impossible no referer when he views other pages.
                    $logData['flag_empty_referer'] = $ipDetail['flag_empty_referer'] ?? 0;

                    if (empty($this->user_http_referer)) {
                        $logData['flag_empty_referer']++;
                    }

                    // Ban this IP if they reached the limit.
                    if ($logData['flag_empty_referer'] >= $this->properties['limit_unusual_behavior']['referer']) {
                        $this->action(self::ACTION_DENY, self::CODE_EMPTY_REFERER);
                        return false;
                    }
                }
            }

            /*** SESSION ***/

            if ($this->enableSessionCheck) {

                if ($now - $ipDetail['last_time'] <= $this->properties['interval_check_session']) {

                    // Get values from data table. We will count it and save it back to data table.
                    $logData['flag_multi_session'] = $ipDetail['flag_multi_session'] ?? 0;

                    if ($this->sessionId !== $ipDetail['session']) {

                        // Is is possible because of direct access by the same user many times.
                        // Or they don't have session cookie set.
                        $logData['flag_multi_session']++;
                    }

                    // Ban this IP if they reached the limit.
                    if ($logData['flag_multi_session'] >= $this->properties['limit_unusual_behavior']['session']) {
                        $this->action(self::ACTION_DENY, self::CODE_TOO_MANY_SESSIONS);
                        return false;
                    }
                }
            }

            /*** JAVASCRIPT COOKIE ***/

            // Let's checking cookie created by javascript..
            if ($this->enableJsCookieCheck) {

                // Get values from data table. We will count it and save it back to data table.
                $logData['flag_js_cookie']   = $ipDetail['flag_js_cookie']   ?? 0;
                $logData['pageviews_cookie'] = $ipDetail['pageviews_cookie'] ?? 0;

                $jsCookie = $_COOKIE[$this->properties['cookie_name']];

                // Checking if a cookie is created by JavaScript.
                if (isset($jsCookie)) {

                    if ($jsCookie == '1') {
                        $logData['pageviews_cookie']++;

                    } else {
                        // Flag it if the value is not 1.
                        $logData['flag_js_cookie']++;
                    }
                } else {
                    // If we cannot find the cookie, flag it.
                    $logData['flag_js_cookie']++;
                }

                if ($logData['flag_js_cookie'] >= $this->properties['limit_unusual_behavior']['cookie']) {

                    // Ban this IP if they reached the limit.
                    $this->action(self::ACTION_DENY, self::CODE_EMPTY_JS_COOKIE);
                    return false;
                }

                // Remove JS cookie and reset.
                if ($logData['pageviews_cookie'] > $this->properties['limit_unusual_behavior']['cookie']) {

                    // Reset to 0.
                    $logData['pageviews_cookie'] = 0;
                    $logData['flag_js_cookie']   = 0;

                    // Remove cookie.
                    unset($_COOKIE[$this->properties['cookie_name']]);
                    setcookie($this->properties['cookie_name'], null, -1, '/');
                }
            }

            /*** ACCESS FREQUENCY ***/

            if ($this->enableFrequencyCheck) {

                foreach ($this->properties['time_unit_quota'] as $timeUnit => $valueNotUsed) {
                    if ($timeUnit === 's') $timeSecond = 1;
                    if ($timeUnit === 'm') $timeSecond = 60;
                    if ($timeUnit === 'h') $timeSecond = 3600;
                    if ($timeUnit === 'd') $timeSecond = 86400;
                    if (($now - $ipDetail["first_time_{$timeUnit}"]) >= ($timeSecond + 1)) {

                        // For example:
                        // (1) minutely: now > first_time_m about 61, (2) hourly: now > first_time_h about 3601, 
                        // Let's prepare to rest the the pageview count.
                        $resetPageviews[$timeUnit] = true;

                    } else {

                        // If an user's pageview count is more than the time period limit
                        // He or she will get banned.
                        if ($logData["pageviews_{$timeUnit}"] >= $this->properties['time_unit_quota'][$timeUnit]) {
                            if ($timeUnit === 's') $this->action(self::ACTION_DENY, self::CODE_REACHED_LIMIT_SECOND);
                            if ($timeUnit === 'm') $this->action(self::ACTION_DENY, self::CODE_REACHED_LIMIT_MINUTE);
                            if ($timeUnit === 'h') $this->action(self::ACTION_DENY, self::CODE_REACHED_LIMIT_HOUR);
                            if ($timeUnit === 'd') $this->action(self::ACTION_DENY, self::CODE_REACHED_LIMIT_DAY);
                            return false;
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

            // Reset the flagged factor check.
            if ($now - $ipDetail['first_time_flag'] >= $this->properties['time_reset_limit']) {
                $logData['flag_multi_session'] = 0;
                $logData['flag_empty_referer'] = 0;
                $logData['flag_js_cookie']     = 0;
            }

            //die(var_dump($logData));

            $this->driver->save($this->ip, $logData, 'log');

        } else {

            // If $ipDetail[ip] is empty.
            // It means that the user is first time visiting our webiste.

            $logData['ip']       = $this->ip;
            $logData['session']  = $this->sessionId;
            $logData['hostname'] = $this->ipResolvedHostname;

            foreach ($resetPageviews as $key => $resetStatus) {
                $logData["first_time_{$key}"] = $now;
            }

            $this->driver->save($this->ip, $logData, 'log');
        }

        return true;
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
    
        if ('' !== $assignIp) {
            $ip = $assignIp;
        }

        switch ($actionCode) {
            case self::ACTION_ALLOW:
            case self::ACTION_DENY:
                $logData['log_ip']     = $this->ip;
                $logData['ip_resolve'] = $this->ipResolvedHostname;
                $logData['time']       = time();
                $logData['type']       = $actionCode;
                $logData['reason']     = $reasonCode;

                $this->driver->save($this->ip, $logData, 'rule');
                break;
            
            case self::ACTION_UNBAN:
                $this->driver->delete($this->ip, 'rule');
                break;
        }

        // Remove logs for this IP address because It already has it's own rule on system.
        // No need to count it anymore.
        $this->driver->delete($this->ip, 'log');
    }

    /**
     * Get a component instance from component's container.
     *
     * @param string $name The component's class name.
     *
     * @return ComponentInterface|null
     */
    protected function getComponent(string $name): ?ComponentInterface
    {
        if (isset($this->component[$name]) && ($this->component[$name] instanceof ComponentInterface)) {
            return $this->component[$name];
        }
        return null;
    }

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
        } else {
            throw new UnexpectedValueException('Incorrect data driver provider.');
        }

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
            $class = get_class($instance);
            $class = substr($class, strrpos($class, '\\') + 1);
            $this->captcha[$class] = $instance;
        } else {
            throw new UnexpectedValueException('Incorrect Captcha instance.');
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

        return true;
    }

    /**
     * Set a commponent.
     * Sheildon needs commponents to work.
     *
     * @param ComponentInterface $instance
     *
     * @return self
     */
    public function setComponent(ComponentInterface $instance): self
    {
        if ($instance instanceof ComponentInterface) {
            $class = get_class($instance);
            $class = substr($class, strrpos($class, '\\') + 1);
            $this->component[$class] = $instance;
        } else {
            throw new UnexpectedValueException('Incorrect component.');
        }

        return $this;
    }

    /**
     * Deny an IP.
     *
     * @param string|array $ip
     *
     * @return void
     */
    public function deny($ip = ''): self
    {
        if ('' === $ip) {
            $ip = $this->ip;
        }

        if ($this->getComponent('Ip')) {
            if (is_array($ip)) {
                $this->getComponent('Ip')->setDeniedList($ip);
            } elseif (is_string($ip)) {
                $this->getComponent('Ip')->setDeniedIp($ip);
            }
        }
        return $this;
    }

    /**
     * Allow an IP.
     *
     * @param string|array $ip
     *
     * @return void
     */
    public function allow($ip = ''): self
    {
        if ('' === $ip) {
            $ip = $this->ip;
        }

        if ($this->getComponent('Ip')) {
            if (is_array($ip)) {
                $this->getComponent('Ip')->setAllowedList($ip);
            } elseif (is_string($ip)) {
                $this->getComponent('Ip')->setAllowedIp($ip);
            }
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
 
        $this->deny($ip);
        $this->action(self::ACTION_DENY, self::CODE_MANUAL_BAN, $ip);

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

        if ($this->getComponent('Ip')) {
            $this->getComponent('Ip')->removeIp($ip, 'deny');
        }

        $this->action(self::ACTION_UNBAN, self::CODE_MANUAL_BAN);

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
            if (! empty($settings['k'])) {
                $this->properties['k'] = $v;
            }
        }

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
     * Remove possible injection charactors.
     *
     * @param string $type GET, POST, COOKIE.
     * @param string $key  The key name of an array.
     *
     * @return self
     */
    public function xssClean(string $type, string $key = '', bool $exact = false): self
    {
        $unwantedCharacters = [
            '"', "'", '[', ']', '~', '=', '*', '&', '^', '%',
            '$', '#', '@', '!', '<', '>', ';', '.', '|', '/',
            '+', '_', ':', '(', ')', '{', '}', '%', '\\'
        ];

        switch ($type) {
            case 'GET':
            case 'COOKIE':
            case 'POST':
            case 'GLOBAL':

                if (empty($_{$type})) {
                    return $this;
                }

                $filiter = $_{$type};

                if ($exact) {
                    if (isset($filiter[$key])) {
                        // string
                        if (is_string($filiter[$key])) {
                            foreach ($unwantedCharacters as $c) {
                                if (strpos($filiter[$key], $c) !== false) {
                                    $filiter[$key] = '';
                                }
                            }
                        }
                        // array
                        if (is_array($filiter[$key])) {
                            foreach ($filiter[$key] as $j => $k) {
                                // string
                                if (is_string($filiter[$key][$j])) {
                                    foreach ($unwantedCharacters as $c) {
                                        if (strpos($filiter[$key][$j], $c) !== false) {
                                            $filiter[$key][$j] = '';
                                        }
                                    }
                                }
                            }
                        }
                    } else {
                        // Check first layer.
                        if (is_array($filiter)) {
                            foreach ($filiter as $k => $v) {
                                // string
                                if (is_string($filiter[$k])) {
                                    foreach ($unwantedCharacters as $c) {
                                        if (strpos($filiter[$k], $c) !== false) {
                                            $filiter[$k] = '';
                                        }
                                    }
                                }
                                // array
                                if (is_array($filiter[$k])) {
                                    foreach ($filiter[$k] as $kk => $vv) {
                                        // string
                                        if (is_string($filiter[$kk])) {
                                            foreach ($unwantedCharacters as $c) {
                                                if (strpos($filiter[$kk], $c) !== false) {
                                                    $filiter[$k][$kk] = '';
                                                }
                                            }
                                        }
                                    }
                                }
                            } 
                        }
                    }
                } else {
                    if (isset($filiter[$key])) {
                        // string
                        if (is_string($filiter[$key])) {
                            $filiter[$key] = trim(str_replace($unwantedCharacters, '', $filiter[$key]));
                        }
                        // array
                        if (is_array($filiter[$key])) {
                            foreach ($filiter[$key] as $j => $k) {
                                if (is_string($filiter[$key][$j])) {
                                    $filiter[$key] = trim(str_replace($unwantedCharacters, '', $filiter[$key]));
                                }
                            }
                        }
                    } else {
                        // Check first layer.
                        if (is_array($filiter)) {
                            foreach ($filiter as $k => $v) {
                                // string
                                if (is_string($filiter[$k])) {
                                    $filiter[$k] = trim(str_replace($unwantedCharacters, '', $filiter[$k]));
                                }
                                // array
                                if (is_array($filiter[$k])) {
                                    foreach ($filiter[$k] as $kk => $vv) {
                                        if (is_string($filiter[$kk])) {
                                            $filiter[$kk] = trim(str_replace($unwantedCharacters, '', $filiter[$kk]));
                                        }
                                    }
                                }
                            } 
                        }
                    }

                }

                break;
            default:
                break;
        }

        $_{$type} = $filiter;

        return $this;
    }

    /**
     * Set result page's HTML.
     *
     * @param string $content The HTML text.
     * @param string $type    The page type: stop, busy.
     *
     * @return self
     */
    public function setHtml(string $content, string $type): self
    {
        if ('busy' === $type || 'stop' === $type) {
            $this->html[$type] = $content;
        }
        return $this;
    }

    /**
     * Output result page.
     *
     * @param int $httpStatus 
     *
     * @echo string
     */
    public function output(int $httpStatus = 0): string
    {
        $output = '';

        if (0 === $this->result) {
            $type = 'stop';
        } elseif (2 === $this->result) {
            $type = 'busy';
        } else {
            return '';
        }

        if (empty($this->html[$type])) {
            $viewPath = self::SHIELDON_DIR . '/../views/' . $type . '.phtml';
            if (file_exists($viewPath)) {
                define('SHIELDON_VIEW', true);
                ob_start();
                require $viewPath;
                $output = ob_get_contents();
                ob_end_clean();
            }
        } else {
            $output = $this->html[$type];
        }

        if (0 !== $httpStatus) {
            http_response_code($httpStatus);
        }

        echo $output;
        exit;
    }

    /**
     * Run, run, run!
     *
     * Check the rule tables first, if an IP address has been listed.
     * Call function detect() if an IP address is not listed in rule tables.
     *
     * @return int RESPONSE_CODE
     */
    public function run(): int
    {
        $this->driver->init($this->autoCreateDatabase);

        if ($this->getComponent('Robot')) {

            // First of all, check if is a a bad robot already defined in settings.
            if ($this->getComponent('Robot')->isDenied()) {
                return $this->result = self::RESPONSE_DENY;
            }
        }

        // Looking for rule table.
        $ipRule = $this->driver->get($this->ip, 'rule');

        if ($this->getComponent('Ip')) {

            $result = $this->getComponent('Ip')->check($this->ip, function() use ($ipRule) {

                // Here should return ['ip', 'type', 'reason']
                // for further checking in IP component.
                if (! empty($ipRule)) {
                    return [
                        'ip' => $ipRule['log_ip'],
                        'type' => $ipRule['type'],
                        'reason' =>  $ipRule['reason'],
                    ];
                }

                return [];
            });

            if (! empty($result) && is_array($result)) {
                if (
                    $result['code'] == $this->getComponent('Ip')::CODE_DENY_IP_RULE &&
                    $result['code'] == $this->getComponent('Ip')::CODE_ALLOW_IP_RULE
                ) {
                    // This IP has been listed in rule table, so set $isRuleList = true.
                    $this->isRuleList = true;
                }

                if ($this->getComponent('Robot')) {

                    // We want to put all the allowed robot into the rule list, so that the checking of IP's resolved hostname 
                    // is no more needed for that IP.

                    if (! $this->isRuleList && $this->getComponent('Robot')->isAllowed()) {

                        if ($this->getComponent('Robot')->isGoogle()) {
    
                            // Add current IP into allowed list, because it is from real Google domain.
                            $this->action(self::ACTION_ALLOW, self::CODE_IS_GOOGLE);
                            
                        } elseif ($this->getComponent('Robot')->isBing()) {

                            // Add current IP into allowed list, because it is from real Bing domain.
                            $this->action(self::ACTION_ALLOW, self::CODE_IS_BING);

                        } elseif ($this->getComponent('Robot')->isYahoo()) {
                            // Add current IP into allowed list, because it is from real Yahoo domain.
                            $this->action(self::ACTION_ALLOW, self::CODE_IS_YAHOO);

                        } else {
                            // Add current IP into allowed list, because you trust it.
                            // You have already defined it in the settings.
                            $this->action(self::ACTION_ALLOW, self::CODE_IS_SEARCH_ENGINE);
                        }

                        // Allowed robots not join to our traffic handler.
                        return $this->result = self::RESPONSE_ALLOW;
                    }
                }

                // The rule list checking result is here.
                switch ($result['status']) {
                    case 'allow':
                        return $this->result = $this->sessionHandler(true);
                        break;
    
                    case 'deny':
                        return $this->result = $this->sessionHandler(false);
                        break;
                }
            }
        }

        if (! empty($ipRule) && $ipRule['type'] == 0) {
            return $this->result = self::RESPONSE_DENY;
        }

        // This IP address is not listed in rule table, let's detect it.
        if ($this->enableFiltering) {

            // We need to record the live sessions first.
            // If they got banned, 
            return $this->result = $this->sessionHandler($this->detect());
        }

        return $this->result = self::RESPONSE_ALLOW;
    }

    /**
     * Deal with online sessions.
     *
     * @param bool $checkPassed
     *
     * @return int RESPONSE_CODE
     */
    private function sessionHandler($checkPassed = false): int
    {
        if (! $checkPassed) {
            return self::RESPONSE_DENY;
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

            foreach ($onlineSessions as $k => $v) {
                $sessionPools[] = $v['id'];

                if ($this->sessionId === $v['id']) {
                    $lasttime = (int) $v['time'];
                }
            }

            if (! in_array($this->sessionId, $sessionPools)) {

                // New session, record this data.
                $data['id'] = $this->sessionId;
                $data['ip'] = $this->ip;
                $data['time'] = $now;
                $this->driver->save($this->sessionId, $data, 'session');

            } else {

                // Remove current session if it expires.
                if ($now - $lasttime > $period) {
                    $this->driver->delete($this->sessionId, 'session');
                }
            }

            // Count the online sessions.
            $onlineCount = count($sessionPools);

            // Online session count reached the limit. So return RESPONSE_LIMIT response code.
            if ($onlineCount >= $limit) {
                return self::RESPONSE_LIMIT;
            }
        }

        return self::RESPONSE_ALLOW;
    }
}


