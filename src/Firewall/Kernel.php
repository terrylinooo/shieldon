<?php
/**
 * This file is part of the Shieldon package.
 *
 * (c) Terry L. <contact@terryl.in>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * php version 7.1.0
 *
 * @category  Web-security
 * @package   Shieldon
 * @author    Terry Lin <contact@terryl.in>
 * @copyright 2019 terrylinooo
 * @license   https://github.com/terrylinooo/shieldon/blob/2.x/LICENSE MIT
 * @link      https://github.com/terrylinooo/shieldon
 * @see       https://shieldon.io
 */

declare(strict_types=1);

namespace Shieldon\Firewall;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Shieldon\Firewall\Captcha\Foundation;
use Shieldon\Firewall\Helpers;
use Shieldon\Firewall\HttpFactory;
use Shieldon\Firewall\IpTrait;
use Shieldon\Firewall\Kernel\CaptchaTrait;
use Shieldon\Firewall\Kernel\ComponentTrait;
use Shieldon\Firewall\Kernel\DriverTrait;
use Shieldon\Firewall\Kernel\FilterTrait;
use Shieldon\Firewall\Kernel\MessengerTrait;
use Shieldon\Firewall\Kernel\RuleTrait;
use Shieldon\Firewall\Kernel\SessionTrait;
use Shieldon\Firewall\Log\ActionLogger;
use Shieldon\Firewall\Utils\Container;
use function Shieldon\Firewall\get_default_properties;
use function Shieldon\Firewall\get_request;
use function Shieldon\Firewall\get_response;
use function Shieldon\Firewall\get_session;

use Closure;
use InvalidArgumentException;
use RuntimeException;
use function file_exists;
use function get_class;
use function gethostbyaddr;
use function is_dir;
use function ob_end_clean;
use function ob_get_contents;
use function ob_start;
use function strpos;
use function strrpos;
use function substr;
use function time;

/**
 * The primary Shiendon class.
 */
class Kernel
{
    use CaptchaTrait;
    use ComponentTrait;
    use DriverTrait;
    use FilterTrait;
    use IpTrait;
    use MessengerTrait;
    use RuleTrait;
    use SessionTrait;

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
     * The result passed from filters, compoents, etc.
     * 
     * DENY    : 0
     * ALLOW   : 1
     * CAPTCHA : 2
     *
     * @var int
     */
    protected $result = 1;

    /**
     * Default settings
     *
     * @var array
     */
    protected $properties = [];

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
     * URLs that are excluded from Shieldon's protection.
     *
     * @var array
     */
    protected $excludedUrls = [];

    /**
     * Custom dialog UI settings.
     *
     * @var array
     */
    protected $dialog = [];

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
     * Which type of configuration source that Shieldon firewall managed?
     * value: managed | config | self | demo
     *
     * @var string
     */
    protected $firewallType = 'self'; 

    /**
     * Shieldon constructor.
     *
     * @param ServerRequestInterface|null $request  A PSR-7 server request.
     * @param ResponseInterface|null      $response A PSR-7 server response.
     *
     * @return void
     */
    public function __construct(?ServerRequestInterface $request = null, ?ResponseInterface $response = null)
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
     * Run, run, run!
     *
     * Check the rule tables first, if an IP address has been listed.
     * Call function filter() if an IP address is not listed in rule tables.
     *
     * @return int
     */
    public function run(): int
    {
        $this->assertDriver();

        // Ignore the excluded urls.
        foreach ($this->excludedUrls as $url) {
            if (strpos($this->getCurrentUrl(), $url) === 0) {
                return $this->result = self::RESPONSE_ALLOW;
            }
        }

        // Execute closure functions.
        foreach ($this->closures as $closure) {
            $closure();
        }

        $result = $this->process();

        if ($result !== self::RESPONSE_ALLOW) {

            // Current session did not pass the CAPTCHA, it is still stuck in 
            // CAPTCHA page.
            $actionCode = self::LOG_CAPTCHA;

            // If current session's respone code is RESPONSE_DENY, record it as 
            // `blacklist_count` in our logs.
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

        // @ MessengerTrait
        $this->triggerMessengers();

        return $result;
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

        $httpStatusCodes = [
            self::RESPONSE_TEMPORARILY_DENY => [
                'type' => 'captcha',
                'code' => 403, // Forbidden.
            ],

            self::RESPONSE_LIMIT_SESSION => [
                'type' => 'session_limitation',
                'code' => 429, // Too Many Requests.
            ],

            self::RESPONSE_DENY => [
                'type' => 'rejection',
                'code' => 400, // Bad request.
            ],
        ];

        // Nothing happened. Return.
        if (empty($httpStatusCodes[$this->result])) {
            return $response;
        }

        $type = $httpStatusCodes[$this->result]['type'];
        $statusCode = $httpStatusCodes[$this->result]['code'];

        $viewPath = $this->getTemplate($type);

        // The language of output UI. It is used on views.
        $langCode = get_session()->get('shieldon_ui_lang') ?? 'en';

        $showOnlineInformation = false;
        $showUserInformation = false;
        
        // Show online session count. It is used on views.
        if (!empty($this->properties['display_online_info'])) {
            $showOnlineInformation = true;
            $onlineinfo['queue'] = $this->sessionStatus['queue'];
            $onlineinfo['count'] = $this->sessionStatus['count'];
            $onlineinfo['period'] = $this->sessionLimit['period'];
        } 

        // Show user information such as IP, user-agent, device name.
        if (!empty($this->properties['display_user_info'])) {
            $showUserInformation = true;
            $dialoguserinfo['ip'] = $this->ip;
            $dialoguserinfo['rdns'] = $this->rdns;
            $dialoguserinfo['user_agent'] = get_request()->getHeaderLine('user-agent');
        }

        // Captcha form
        $form = $this->getCurrentUrl();
        $captchas = $this->captcha;

        $ui = [
            'background_image' => '',
            'bg_color'         => '#ffffff',
            'header_bg_color'  => '#212531',
            'header_color'     => '#ffffff',
            'shadow_opacity'   => '0.2',
        ];

        foreach (array_keys($ui) as $key) {
            if (!empty($this->dialog[$key])) {
                $ui[$key] = $this->dialog[$key];
            }
        }

        if (!defined('SHIELDON_VIEW')) {
            define('SHIELDON_VIEW', true);
        }

        $css = include $this->getTemplate('css/default');

        ob_start();
        include $viewPath;
        $output = ob_get_contents();
        ob_end_clean();

        // Remove unused variable notices generated from PHP intelephense.
        unset(
            $css,
            $ui,
            $form,
            $captchas,
            $csrf,
            $langCode,
            $showOnlineInformation,
            $showUserInformation
        );

        $stream = $response->getBody();
        $stream->write($output);
        $stream->rewind();

        return $response
            ->withHeader('X-Protected-By', 'shieldon.io')
            ->withBody($stream)
            ->withStatus($statusCode);
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
        if ($ip === '') {
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
     * Set a action log logger.
     *
     * @param ActionLogger $logger Record action logs for users.
     *
     * @return void
     */
    public function setLogger(ActionLogger $logger): void
    {
        $this->logger = $logger;
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
     * Customize the dialog UI.
     * 
     * @param array $settings The dialog UI settings.
     *
     * @return void
     */
    public function setDialog(array $settings): void
    {
        $this->dialog = $settings;
    }

    /**
     * Set the frontend template directory.
     *
     * @param string $directory The directory in where the template files are placed.
     *
     * @return void
     */
    public function setTemplateDirectory(string $directory)
    {
        if (!is_dir($directory)) {
            throw new InvalidArgumentException(
                'The template directory does not exist.'
            );
        }
        $this->templateDirectory = $directory;
    }

    /**
     * Print a JavasSript snippet in your webpages.
     * 
     * This snippet generate cookie on client's browser,then we check the 
     * cookie to identify the client is a rebot or not.
     *
     * @return string
     */
    public function getJavascript(): string
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
     * Displayed on Firewall Panel, telling you current what type of 
     * configuration is used.
     * 
     * @param string $type The type of configuration.
     *                     accepted value: demo | managed | config
     *
     * @return void
     */
    public function managedBy(string $type = ''): void
    {
        if (in_array($type, ['managed', 'config', 'demo'])) {
            $this->firewallType = $type;
        }
    }

    /*
    |-------------------------------------------------------------------
    | Non-public methids.
    |-------------------------------------------------------------------
    */

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

        $processMethods = [
            'isRuleExist',   // Stage 1 - Looking for rule table.
            'isTrustedBot',  // Stage 2 - Detect popular search engine.
            'isFakeRobot',   // Stage 3 - Reject fake search engine crawlers.
            'isIpComponent', // Stage 4 - IP manager.
            'isComponents'   // Stage 5 - Check other components.
        ];

        foreach ($processMethods as $method) {
            if ($this->{$method}()) {
                return $this->result;
            }
        }

        // Stage 6 - Check filters if set.
        if (array_search(true, $this->filterStatus)) {
            return $this->result = $this->sessionHandler($this->filter());
        }

        // Stage 7 - Go into session limit check.
        return $this->result = $this->sessionHandler(self::RESPONSE_ALLOW);
    }

    /**
     * Start an action for this IP address, allow or deny, and give a reason for it.
     *
     * @param int    $actionCode The action code. - 0: deny, 1: allow, 9: unban.
     * @param string $reasonCode The response code.
     * @param string $assignIp   The IP address.
     * 
     * @return void
     */
    protected function action(
        int    $actionCode,
        int    $reasonCode,
        string $assignIp = ''
    ): void {

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

    /**
     * Log actions.
     *
     * @param int    $actionCode The code number of the action.
     * @param string $ip         The IP address.
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
}
