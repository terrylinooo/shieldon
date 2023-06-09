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
use Shieldon\Firewall\Kernel\TemplateTrait;
use Shieldon\Firewall\Kernel\Enum;
use Shieldon\Firewall\Log\ActionLogger;
use Shieldon\Firewall\Container;
use Shieldon\Event\Event;
use Closure;
use function Shieldon\Firewall\get_default_properties;
use function Shieldon\Firewall\get_request;
use function Shieldon\Firewall\get_session_instance;
use function array_push;
use function get_class;
use function gethostbyaddr;
use function ltrim;
use function strpos;
use function strrpos;
use function substr;
use function time;

/**
 * The primary Shiendon class.
 */
class Kernel
{
    /**
     *   Public methods       | Desctiotion
     *  ----------------------|---------------------------------------------
     *   ban                  | Ban an IP.
     *   getCurrentUrl        | Get current user's browsing path.
     *   managedBy            | Used on testing purpose.
     *   run                  | Run the checking process.
     *   setClosure           | Set a closure function.
     *   exclude              | Set a URL you want them excluded them from protection.
     *   setExcludedList      | Set the URLs you want them excluded them from protection.
     *   setLogger            | Set the action log logger.
     *   setProperties        | Set the property settings.
     *   setProperty          | Set a property setting.
     *   setStrict            | Strict mode apply to all components.
     *   unban                | Unban an IP.
     *  ----------------------|---------------------------------------------
     */

    /**
     *   Public methods       | Desctiotion
     *  ----------------------|---------------------------------------------
     *   setCaptcha           | Set a captcha.
     *   captchaResponse      | Return the result from Captchas.
     *   disableCaptcha       | Mostly be used in unit testing purpose.
     *  ----------------------|---------------------------------------------
     */
    use CaptchaTrait;

    /**
     *   Public methods       | Desctiotion
     *  ----------------------|---------------------------------------------
     *   setComponent         | Set a commponent.
     *   getComponent         | Get a component instance from component's container.
     *   disableComponents    | Disable all components.
     *  ----------------------|---------------------------------------------
     */
    use ComponentTrait;

    /**
     *   Public methods       | Desctiotion
     *  ----------------------|---------------------------------------------
     *   setDriver            | Set a data driver.
     *   setChannel           | Set a data channel.
     *   disableDbBuilder     | disable creating data tables.
     *  ----------------------|---------------------------------------------
     */
    use DriverTrait;

    /**
     *   Public methods       | Desctiotion
     *  ----------------------|---------------------------------------------
     *   setFilters           | Set the filters.
     *   setFilter            | Set a filter.
     *   disableFilters       | Disable all filters.
     *  ----------------------|---------------------------------------------
     */
    use FilterTrait;

    /**
     *   Public methods       | Desctiotion
     *  ----------------------|---------------------------------------------
     *   setIp                | Set an IP address.
     *   getIp                | Get current set IP.
     *   setRdns              | Set a RDNS record for the check.
     *   getRdns              | Get IP resolved hostname.
     *  ----------------------|---------------------------------------------
     */
    use IpTrait;

    /**
     *   Public methods       | Desctiotion
     *  ----------------------|---------------------------------------------
     *   setMessenger         | Set a messenger
     *  ----------------------|---------------------------------------------
     */
    use MessengerTrait;

    /**
     *   Public methods       | Desctiotion
     *  ----------------------|---------------------------------------------
     *                        | No public methods.
     *  ----------------------|---------------------------------------------
     */
    use RuleTrait;

    /**
     *   Public methods       | Desctiotion
     *  ----------------------|---------------------------------------------
     *   limitSession         | Limit the amount of the online users.
     *   getSessionCount      | Get the amount of the sessions.
     *  ----------------------|---------------------------------------------
     */
    use SessionTrait;

    /**
     *   Public methods       | Desctiotion
     *  ----------------------|---------------------------------------------
     *   setDialog            | Set the dialog UI.
     *   respond              | Respond the result.
     *   setTemplateDirectory | Set the frontend template directory.
     *   getJavascript        | Print a JavaScript snippet in the pages.
     *  ----------------------|---------------------------------------------
     */
    use TemplateTrait;

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
     * Strict mode.
     *
     * Set by `strictMode()` only. The default value of this propertry is undefined.
     *
     * @var bool|null
     */
    protected $strictMode;

    /**
     * Which type of configuration source that Shieldon firewall managed?
     * value: managed | config | self | demo
     *
     * @var string
     */
    protected $firewallType = 'self';

    /**
     * The reason code of a user to be allowed or denied.
     *
     * @var int|null
     */
    protected $reason;

    /**
     * The session cookie will be created by the PSR-7 HTTP resolver.
     * If this option is false, created by PHP native function `setcookie`.
     *
     * @var bool
     */
    public $psr7 = true;

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
        // Load helper functions. This is the must and first.
        new Helpers();

        if (is_null($request)) {
            $request = HttpFactory::createRequest();
            $this->psr7 = false;
        }

        if (is_null($response)) {
            $response = HttpFactory::createResponse();
        }

        // Load default settings.
        $this->properties = get_default_properties();

        // Basic form for Captcha.
        $this->setCaptcha(new Foundation());

        Container::set('request', $request);
        Container::set('response', $response);
        Container::set('shieldon', $this);

        Event::AddListener(
            'set_session_driver',
            function ($args) {
                $session = get_session_instance();
                $session->init(
                    $args['driver'],
                    $args['gc_expires'],
                    $args['gc_probability'],
                    $args['gc_divisor'],
                    $args['psr7']
                );

                /**
                 * Hook - session_init
                 */
                Event::doDispatch('session_init');
                set_session_instance($session);
            }
        );
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
                return $this->result = Enum::RESPONSE_ALLOW;
            }
        }

        // Execute closure functions.
        foreach ($this->closures as $closure) {
            $closure();
        }

        $result = $this->process();

        if ($result !== Enum::RESPONSE_ALLOW) {
            // Current session did not pass the CAPTCHA, it is still stuck in
            // CAPTCHA page.
            $actionCode = Enum::LOG_CAPTCHA;

            // If current session's respone code is RESPONSE_DENY, record it as
            // `blacklist_count` in our logs.
            // It is stuck in warning page, not CAPTCHA.
            if ($result === Enum::RESPONSE_DENY) {
                $actionCode = Enum::LOG_BLACKLIST;
            }

            if ($result === Enum::RESPONSE_LIMIT_SESSION) {
                $actionCode = Enum::LOG_LIMIT;
            }

            $this->log($actionCode);
        } else {
            $this->log(Enum::LOG_PAGEVIEW);
        }

        // @ MessengerTrait
        $this->triggerMessengers();

        /**
         * Hook - kernel_end
         */
        Event::doDispatch('kernel_end');

        return $result;
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
            Enum::ACTION_DENY,
            Enum::REASON_MANUAL_BAN_DENIED,
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
            Enum::ACTION_UNBAN,
            Enum::REASON_MANUAL_BAN_DENIED,
            $ip
        );
        $this->log(Enum::ACTION_UNBAN);

        $this->result = Enum::RESPONSE_ALLOW;
    }

    /**
     * Set a property setting.
     *
     * @param string $key   The key of a property setting.
     * @param mixed  $value The value of a property setting.
     *
     * @return void
     */
    public function setProperty(string $key = '', $value = ''): void
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
    public function setStrict(bool $bool): void
    {
        $this->strictMode = $bool;
    }

    /**
     * Set an action log logger.
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
     * Add a path into the excluded list.
     *
     * @param string $uriPath The path component of a URI.
     *
     * @return void
     */
    public function exclude(string $uriPath): void
    {
        $uriPath = '/' . ltrim($uriPath, '/');

        array_push($this->excludedUrls, $uriPath);
    }

    /**
     * Set the URLs you want them excluded them from protection.
     *
     * @param array $urls The list of URL want to be excluded.
     *
     * @return void
     */
    public function setExcludedList(array $urls = []): void
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
    | Non-public methods.
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
        $this->initComponents();

        $processMethods = [
            'isRuleExist',   // Stage 1 - Looking for rule table.
            'isTrustedBot',  // Stage 2 - Detect popular search engine.
            'isFakeRobot',   // Stage 3 - Reject fake search engine crawlers.
            'isIpComponent', // Stage 4 - IP manager.
            'isComponents',  // Stage 5 - Check other components.
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
        return $this->result = $this->sessionHandler(Enum::RESPONSE_ALLOW);
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

        if ($actionCode === Enum::ACTION_UNBAN) {
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

        $this->removeSessionsByIp($ip);

        // Log this action.
        $this->log($actionCode, $ip);

        $this->reason = $reasonCode;
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
 
        $logData['ip'] = $ip ?: $this->getIp();
        $logData['session_id'] = get_session_instance()->getId();
        $logData['action_code'] = $actionCode;
        $logData['timestamp'] = time();

        $this->logger->add($logData);
    }

    /**
     * Get a class name without namespace string.
     *
     * @param object $instance Class
     *
     * @return string
     */
    protected function getClassName($instance): string
    {
        $class = get_class($instance);
        return substr($class, strrpos($class, '\\') + 1);
    }

    /**
     * Save and return the result identifier.
     * This method is for passing value from traits.
     *
     * @param int $resultCode The result identifier.
     *
     * @return int
     */
    protected function setResultCode(int $resultCode): int
    {
        return $this->result = $resultCode;
    }
}
