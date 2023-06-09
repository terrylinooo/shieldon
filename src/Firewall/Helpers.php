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

use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;
use Shieldon\Firewall\Container;
use Shieldon\Firewall\Driver\FileDriver;
use Shieldon\Firewall\HttpFactory;
use Shieldon\Firewall\Session;
use function explode;
use function file_exists;
use function file_put_contents;
use function func_get_arg;
use function func_num_args;
use function implode;
use function is_array;
use function is_null;
use function md5;
use function microtime;
use function preg_split;
use function rand;
use function round;
use function shell_exec;
use function str_repeat;
use function str_replace;
use function stripos;
use function strtoupper;
use function substr;
use function sys_getloadavg;
use function time;
use function trim;
use const PHP_OS;

/**
 * This value will be only displayed on Firewall Panel.
 */
define('SHIELDON_FIREWALL_VERSION', '2.0');

/**
 * Just use PSR-4 autoloader to load those helper functions.
 */
class Helpers
{

}

/**
 *   Public methods       | Desctiotion
 *  ----------------------|---------------------------------------------
 *  __                    | Get locale message.
 *  _e                    | Echo string from __()
 *  get_user_lang         | Get user lang.
 *  include_i18n_file     | Include i18n file.
 *  mask_string           | Mask strings with asterisks.
 *  get_cpu_usage         | Get current CPU usage information.
 *  get_memory_usage      | Get current RAM usage information.
 *  get_default_properties| The default settings of Shieldon core.
 *  get_request           | Get PSR-7 HTTP server request from container.
 *  get_response          | Get PSR-7 HTTP response from container.
 *  set_request           | Set PSR-7 HTTP server request to container.
 *  set_response          | Set PSR-7 HTTP response to container.
 *  unset_global_cookie   | Unset superglobal COOKIE variable.F
 *  unset_global_post     | Unset superglobal POST variable.
 *  unset_global_get      | Unset superglobal GET variable.
 *  unset_global_session  | Unset superglobal SESSION variable.
 *  unset_superglobal     | Unset superglobal variables.
 *  get_ip                | Get an IP address from container.
 *  set_ip                | Set an IP address to container.
 *  get_microtimestamp    | Get the microtimestamp.
 *  get_session_instance  | Get a session instance.
 *  create_new_session_i- | Create a new session instance for current user.
 *  n stance              |
 *  get_mock_session      | For unit testing purpose.
 *  set_session_instance  | Set a session instance to container.
 *  get_session_id        | Get session ID from cookie or creating new.
 *  create_session_id     | Create a new session ID.
 *  ----------------------|---------------------------------------------
 */

/**
 * Get locale message.
 *
 * @return string
 */
function __(): string
{
    /**
     * Load locale string from i18n files and store them into this array
     * for further use.
     *
     * @var array
     */
    static $i18n;

    /**
     * Check the file exists for not.
     *
     * @var array
     */
    static $fileChecked;

    $num = func_num_args();

    $filename    = func_get_arg(0); // required.
    $langcode    = func_get_arg(1); // required.
    $placeholder = ($num > 2) ? func_get_arg(2) : '';
    $replacement = ($num > 3) ? func_get_arg(3) : [];
    $lang        = get_user_lang();

    if (empty($i18n[$filename]) && empty($fileChecked[$filename])) {
        $fileChecked[$filename] = true;
        $i18n[$filename] = include_i18n_file($lang, $filename);
    }

    // If we don't get the string from the localization file, use placeholder
    // instead.
    $resultString = $placeholder;

    if (!empty($i18n[$filename][$langcode])) {
        $resultString = $i18n[$filename][$langcode];
    }

    if (is_array($replacement)) {
        /**
         * Example:
         *     __('test', 'example_string', 'Search results: {0} items. Total items: {1}.', [5, 150]);
         *
         * Result:
         *     Search results: 5 items. Total items: 150.
         */
        foreach ($replacement as $i => $r) {
            $resultString = str_replace('{' . $i . '}', (string) $replacement[$i], (string) $resultString);
        }
    }
    
    return str_replace("'", '’', $resultString);
}

/**
 * Echo string from __()
 *
 * @return void
 */
function _e(): void
{
    $num = func_num_args();

    $filename    = func_get_arg(0); // required.
    $langcode    = func_get_arg(1); // required.
    $placeholder = ($num > 2) ? func_get_arg(2) : '';
    $replacement = ($num > 3) ? func_get_arg(3) : [];

    echo __($filename, $langcode, $placeholder, $replacement);
}

/**
 * Get user lang.
 *
 * This method is a part of  __()
 *
 * @return string
 */
function get_user_lang(): string
{
    static $lang;

    if (!$lang) {
        $lang = 'en';

        // Fetch session variables.
        $session = get_session_instance();
        $panelLang = $session->get('shieldon_panel_lang');
        $uiLang = $session->get('shieldon_ui_lang');
    
        if (!empty($panelLang)) {
            $lang = $panelLang;
        } elseif (!empty($uiLang)) {
            $lang = $uiLang;
        }
    }

    return $lang;
}

/**
 * Include i18n file.
 *
 * This method is a part of  __()
 *
 * @param string $lang     The language code.
 * @param string $filename The i18n language pack file.
 *
 * @return void
 */
function include_i18n_file(string $lang, string $filename): array
{
    $content = [];
    $lang = str_replace('-', '_', $lang);

    if (stripos($lang, 'zh_') !== false) {
        if (stripos($lang, 'zh_CN') !== false) {
            $lang = 'zh_CN';
        } else {
            $lang = 'zh';
        }
    }

    $file = __DIR__ . '/../../localization/' . $lang . '/' . $filename . '.php';
    
    if (file_exists($file)) {
        $content = include $file;
    }

    return $content;
}

/**
 * Mask strings with asterisks.
 *
 * @param string $str The text.
 *
 * @return string
 */
function mask_string($str): string
{
    if (filter_var($str, FILTER_VALIDATE_IP) !== false) {
        $tmp = explode('.', $str);
        $tmp[0] = '*';
        $tmp[1] = '*';
        $masked = implode('.', $tmp);
    } else {
        $masked = str_repeat('*', strlen($str) - 6) . substr($str, -6);
    }

    return $masked;
}

/**
 * Get current CPU usage information.
 *
 * This function is only used in sending notifications and it is unavailable
 * on Win system. If you are using shared hosting and your hosting provider
 * has disabled `sys_getloadavg` and `shell_exec`, it won't work either.
 *
 * @return string
 */
function get_cpu_usage(): string
{
    $return = '';

    // This feature is not available on Windows platform.
    if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
        return $return;
    }

    $cpuLoads = @sys_getloadavg();
    $cpuCores = trim(@shell_exec("grep -P '^processor' /proc/cpuinfo|wc -l"));

    if (!empty($cpuCores) && !empty($cpuLoads)) {
        $return = round($cpuLoads[1] / ($cpuCores + 1) * 100, 0) . '%';
    }
    return $return;
}

/**
 * Get current RAM usage information.
 *
 * If you are using shared hosting and your hosting provider has disabled
 * `shell_exec`, this function may not work as expected.
 *
 * @return string
 */
function get_memory_usage(): string
{
    $return = '';

    // This feature is not available on Windows platform.
    if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
        return $return;
    }

    $freeResult = explode("\n", trim(@shell_exec('free')));

    if (!empty($freeResult)) {
        $parsed = preg_split("/[\s]+/", $freeResult[1]);
        $return = round($parsed[2] / $parsed[1] * 100, 0) . '%';
    }
    return $return;
}

/**
 * The default settings of Shieldon core.
 *
 * @return array
 */
function get_default_properties(): array
{
    return [

        'time_unit_quota' => [
            's' => 2,
            'm' => 10,
            'h' => 30,
            'd' => 60,
        ],

        'time_reset_limit'       => 3600,
        'interval_check_referer' => 5,
        'interval_check_session' => 5,
        'limit_unusual_behavior' => [
            'cookie'  => 5,
            'session' => 5,
            'referer' => 10,
        ],

        'cookie_name'         => 'ssjd',
        'cookie_domain'       => '',
        'cookie_value'        => '1',
        'display_online_info' => true,
        'display_user_info'   => false,
        'display_http_code'   => false,
        'display_reason_code' => false,
        'display_reason_text' => false,

        /**
         * If you set this option enabled, Shieldon will record every CAPTCHA fails
         * in a row, once that user have reached the limitation number, Shieldon will
         * put it as a blocked IP in rule table, until the new data cycle begins.
         *
         * Once that user have been blocked, they are still access the warning page,
         * it means that they are not humain for sure, so let's throw them into the
         * system firewall and say goodbye to them forever.
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
         * To prevent dropping social platform robots into iptables firewall, such
         * as Facebook, Line and others who scrape snapshots from your web pages,
         * you should adjust the values below to fit your needs. (unit: second)
         */
        'record_attempt_detection_period' => 5, // 5 seconds.

        // Reset the counter after n second.
        'reset_attempt_counter' => 1800, // 30 minutes.

        // System-layer firewall, ip6table service watches this folder to
        // receive command created by Shieldon Firewall.
        'iptables_watching_folder' => '/tmp/',
    ];
}

/*
|--------------------------------------------------------------------------
| PSR-7 helpers.
|--------------------------------------------------------------------------
*/

/**
 * PSR-7 HTTP server request
 *
 * @return ServerRequestInterface
 */
function get_request(): ServerRequestInterface
{
    $request = Container::get('request');

    if (is_null($request)) {
        $request = HttpFactory::createRequest();
        Container::set('request', $request);
    }

    return $request;
}

/**
 * PSR-7 HTTP response.
 *
 * @return ResponseInterface
 */
function get_response(): ResponseInterface
{
    $response = Container::get('response');

    if (is_null($response)) {
        $response = HttpFactory::createResponse();
        Container::set('response', $response);
    }

    return $response;
}

/**
 * Set a PSR-7 HTTP server request into container.
 *
 * @param ServerRequestInterface $request The PSR-7 server request.
 *
 * @return void
 */
function set_request(ServerRequestInterface $request): void
{
    Container::set('request', $request, true);
}

/**
 * Set a PSR-7 HTTP response into container.
 *
 * @param ResponseInterface $response The PSR-7 server response.
 *
 * @return void
 */
function set_response(ResponseInterface $response): void
{
    Container::set('response', $response, true);
}

/*
|--------------------------------------------------------------------------
| Superglobal variables.
|--------------------------------------------------------------------------
*/

/**
 * Unset cookie.
 *
 * @param string|null $name The name (key) in the array of the superglobal.
 *
 * @return void
 */
function unset_global_cookie($name = null): void
{
    if (empty($name)) {
        $cookieParams = get_request()->getCookieParams();
        set_request(get_request()->withCookieParams([]));

        foreach (array_keys($cookieParams) as $name) {
            set_response(
                get_response()->withHeader(
                    'Set-Cookie',
                    "$name=; expires=Thu, 01-Jan-1970 00:00:01 GMT; Max-Age=0"
                )
            );
        }
        $_COOKIE = [];

        return;
    }

    $cookieParams = get_request()->getCookieParams();
    unset($cookieParams[$name]);

    set_request(
        get_request()->withCookieParams(
            $cookieParams
        )
    );

    set_response(
        get_response()->withHeader(
            'Set-Cookie',
            "$name=; expires=Thu, 01-Jan-1970 00:00:01 GMT; Max-Age=0"
        )
    );
    // Prevent direct access to superglobal.
    unset($_COOKIE[$name]);
}

/**
 * Unset post.
 *
 * @param string|null $name The name (key) in the array of the superglobal.
 *
 * @return void
 */
function unset_global_post($name = null): void
{
    if (empty($name)) {
        set_request(get_request()->withParsedBody([]));
        $_POST = [];

        return;
    }

    $postParams = get_request()->getParsedBody();
    unset($postParams[$name]);
    set_request(get_request()->withParsedBody($postParams));
    unset($_POST[$name]);
}

/**
 * Unset get.
 *
 * @param string|null $name The name (key) in the array of the superglobal.
 *
 * @return void
 */
function unset_global_get($name = null): void
{
    if (empty($name)) {
        set_request(get_request()->withQueryParams([]));
        $_GET = [];

        return;
    }

    $getParams = get_request()->getQueryParams();
    unset($getParams[$name]);
    set_request(get_request()->withQueryParams($getParams));
    unset($_GET[$name]);
}

/**
 * Unset session.
 *
 * @param string|null $name The name (key) in the array of the superglobal.
 *
 * @return void
 */
function unset_global_session($name = null): void
{
    if (empty($name)) {
        get_session_instance()->clear();
        get_session_instance()->save();
        return;
    }

    get_session_instance()->remove($name);
    get_session_instance()->save();
}

/**
 * Unset a variable of superglobal.
 *
 * @param string|null $name The name (key) in the array of the superglobal.
 *                          If $name is null that means clear all.
 * @param string      $type The type of the superglobal.
 *
 * @return void
 */
function unset_superglobal($name, string $type): void
{
    $types = [
        'get',
        'post',
        'cookie',
        'session',
    ];

    if (!in_array($type, $types)) {
        return;
    }

    $method = '\Shieldon\Firewall\unset_global_' . $type;
    $method($name, $type);
}

/*
|--------------------------------------------------------------------------
| IP address.
|--------------------------------------------------------------------------
*/

/**
 * Get an IP address.
 *
 * @return string
 */
function get_ip(): string
{
    $ip = Container::get('ip_address');
    
    if (empty($ip)) {
        $ip = $_SERVER['REMOTE_ADDR'];
    }

    return $ip;
}

/**
 * Set an IP address.
 *
 * @param string $ip An IP address.
 *
 * @return void
 */
function set_ip(string $ip)
{
    Container::set('ip_address', $ip, true);
}

/*
|--------------------------------------------------------------------------
| Time.
|--------------------------------------------------------------------------
*/

/**
 * Get the microtimestamp.
 *
 * @return string
 */
function get_microtimestamp()
{
    $microtimestamp = explode(' ', microtime());
    $microtimestamp = $microtimestamp[1] . str_replace('0.', '', $microtimestamp[0]);

    return $microtimestamp;
}

/*
|--------------------------------------------------------------------------
| Session.
|--------------------------------------------------------------------------
*/

/**
 * Session
 *
 * @return Session
 */
function get_session_instance(): Session
{
    $session = Container::get('session');

    if (is_null($session)) {
        $session = HttpFactory::createSession(get_session_id());
        set_session_instance($session);
    }

    return $session;
}

/**
 * For unit testing purpose. Not use in production.
 * Create new session by specifying a session ID.
 *
 * @param string $sessionId A session ID string.
 *
 * @return void
 */
function create_new_session_instance(string $sessionId)
{
    Container::set('session_id', $sessionId, true);
    $session = Container::get('session');

    if ($session instanceof Session) {
        $session->setId($sessionId);
        set_session_instance($session);
    }
}

/**
 * For unit testing purpose. Not use in production.
 *
 * @param string $sessionId A session ID string.
 *
 * @return Session
 */
function get_mock_session($sessionId): Session
{
    Container::set('session_id', $sessionId, true);

    // Constant BOOTSTRAP_DIR is available in unit testing mode.
    $fileDriverStorage = BOOTSTRAP_DIR . '/../tmp/shieldon/data_driver_file';
    $dir = $fileDriverStorage . '/shieldon_sessions';
    $file = $dir . '/' . $sessionId . '.json';

    if (!is_dir($dir)) {
        $originalUmask = umask(0);

        if (!is_dir($dir)) {
            mkdir($dir, 0777, true);
        }

        umask($originalUmask);
    }

    $session = HttpFactory::createSession($sessionId);

    $driver = new FileDriver($fileDriverStorage);

    if (!file_exists($file)) {
        $data = [];

        // Prepare mock data.
        $data['id'] = $sessionId;
        $data['ip'] = get_ip();
        $data['time'] = time();
        $data['microtimestamp'] = get_microtimestamp();
        $data['data'] = '{}';
    
        $json = json_encode($data);
        
        // Check and build the folder.
        $originalUmask = umask(0);

        if (!is_dir($dir)) {
            mkdir($dir, 0777, true);
        }

        umask($originalUmask);

        file_put_contents($file, $json);
    }

    $session->init($driver);

    Container::set('session', $session, true);

    return $session;
}

/**
 * Set the Session, if exists, it will be overwritten.
 *
 * @param Session $session The session instance.
 *
 * @return void
 */
function set_session_instance(Session $session): void
{
    Container::set('session', $session, true);
}

/**
 * Get session ID.
 *
 * @return string
 */
function get_session_id(): string
{
    static $sessionId;

    if (!$sessionId) {
        $cookie = get_request()->getCookieParams();

        if (!empty($cookie['_shieldon'])) {
            $sessionId = $cookie['_shieldon'];
        } else {
            $sessionId = create_session_id();
        }
    }

    return $sessionId;
}

/**
 * Create a hash code for the Session ID.
 *
 * @return string
 */
function create_session_id(): string
{
    $hash = rand() . 'ej;1zj47vu;3e;31g642941ek62au/41' . time();

    return md5($hash);
}
