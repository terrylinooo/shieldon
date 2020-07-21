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
use Shieldon\Firewall\IpTrait;
use Shieldon\Firewall\Kernel\FilterTrait;
use Shieldon\Firewall\Kernel\ComponentTrait;
use Shieldon\Firewall\Kernel\RuleTrait;
use Shieldon\Firewall\Kernel\LimitSessionTrait;
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
    use ComponentTrait;
    use RuleTrait;
    use LimitSessionTrait;

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
    public $captcha = [];

    /**
     * The ways Shieldon send a message to when someone has been blocked.
     * The collection of \Shieldon\Messenger\Messenger\MessengerInterface
     *
     * @var array
     */
    protected $messenger = [];

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
     * Strict mode.
     * 
     * Set by `strictMode()` only. The default value of this propertry is undefined.
     *
     * @var bool|null
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

        $request = $request ?? HttpFactory::createRequest();
        $response = $response ?? HttpFactory::createResponse();
        $session = HttpFactory::createSession();

        $this->properties = get_default_properties();
        $this->setCaptcha(new Foundation());

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
    protected function log(int $actionCode, $ip = ''): void
    {
        if (!$this->logger) {
            return;
        }

        $logData = [];
        $logData['ip'] = $ip ?? $this->getIp();
        $logData['session_id'] = get_session()->get('id');
        $logData['action_code'] = $actionCode;
        $logData['timesamp'] = time();

        $this->logger->add($logData);
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

        if ($this->IsRuleExist()) {
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

        if ($this->isIpComponent()) {
            return $this->result;
        }

        /*
        |--------------------------------------------------------------------------
        | Stage - Check all other components.
        |--------------------------------------------------------------------------
        */

        foreach ($this->component as $component) {

            // check if is a a bad robot already defined in settings.
            if ($component->isDenied()) {

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

        if (array_search(true, $this->filterStatus)) {
            return $this->result = $this->sessionHandler($this->filter());
        }

        return $this->result = $this->sessionHandler(self::RESPONSE_ALLOW);
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

        if ($actionCode === self::ACTION_UNBAN) {
            $this->driver->delete($ip, 'rule');
        } else {
            $logData['log_ip']     = $ip;
            $logData['ip_resolve'] = $rdns;
            $logData['time']       = $now;
            $logData['type']       = $actionCode;
            $logData['reason']     = $reasonCode;
            $logData['attempts']   = 0;

            $this->driver->save($ip, $logData, 'rule');
        }

        // Remove logs for this IP address because It already has it's own rule on system.
        // No need to count for it anymore.
        $this->driver->delete($ip, 'filter');

        // Log this action.
        $this->log($actionCode, $ip);
    }

    // @codeCoverageIgnoreEnd

    /*
    | -------------------------------------------------------------------
    |                            Public APIs
    | -------------------------------------------------------------------
    */

    /**
     * Set a captcha.
     *
     * @param CaptchaInterface $instance
     *
     * @return void
     */
    public function setCaptcha(CaptchaInterface $instance): void
    {
        $class = $this->getClassName($instance);
        $this->captcha[$class] = $instance;
    }

    /**
     * Set a data driver.
     *
     * @param DriverProvider $driver Query data from the driver you choose to use.
     *
     * @return void
     */
    public function setDriver(DriverProvider $driver): void
    {
        $this->driver = $driver;
    }

    /**
     * Set a action log logger.
     *
     * @param ActionLogger $logger
     *
     * @return void
     */
    public function setLogger(ActionLogger $logger): void
    {
        $this->logger = $logger;
    }

    /**
     * Set a messenger
     *
     * @param MessengerInterfa $instance
     *
     * @return void
     */
    public function setMessenger(MessengerInterface $instance): void
    {
        $class = $this->getClassName($instance);
        $this->messengers[$class] = $instance;
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
            self::REASON_MANUAL_BAN,
            $ip
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
            self::REASON_MANUAL_BAN,
            $ip
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
        if (!isset($this->driver)) {
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
