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
use Shieldon\Firewall\HttpResolver;
use Shieldon\Firewall\Panel\CsrfTrait;
use Shieldon\Firewall\Panel\DemoModeTrait;
use Shieldon\Firewall\Panel\User;
use Shieldon\Firewall\Container;
use Shieldon\Firewall\Firewall;
use RuntimeException;
use function Shieldon\Firewall\get_request;
use function Shieldon\Firewall\get_response;
use function call_user_func;
use function explode;
use function in_array;
use function property_exists;
use function str_replace;
use function trim;
use function ucfirst;

/**
 * Firewall's Control Panel
 *
 * Display a Control Panel UI for developers or administrators.
 */
class Panel
{
    /**
     *   Public methods       | Desctiotion
     *  ----------------------|---------------------------------------------
     *   __call               | Magic method. Let property can run as a method.
     *   entry                | Initialize the entry point of the control panel
     *   ::getRoutes          | Get the path string list for all routes.
     *  ----------------------|---------------------------------------------
     */

    /**
     *   Public methods       | Desctiotion
     *  ----------------------|---------------------------------------------
     *   demo                 | Start a demo mode. Setting fields are hidden.
     *  ----------------------|---------------------------------------------
     */
    use DemoModeTrait;

    /**
     *   Public methods       | Desctiotion
     *  ----------------------|---------------------------------------------
     *   csrf                 | Receive the CSRF name and token from the App.
     *   setCsrfField         | Set CSRF input fields.
     *   fieldCsrf            | Output HTML input element with CSRF token.
     *  ----------------------|---------------------------------------------
     */
    use CsrfTrait;

    /**
     * Route map.
     *
     * @var array
     */
    protected $registerRoutes;

    /**
     * The HTTP resolver.
     *
     * We need to resolve the HTTP result by ourselves to prevent conficts
     * with other frameworks.
     *
     * @var \Shieldon\Firewall\HttpResolver
     */
    protected $resolver = null;

    /**
     * Firewall panel constructor.
     */
    public function __construct()
    {
        $this->registerRoutes = self::getRoutes();
        $this->resolver = new HttpResolver();
    }

    /**
     * Display pages.
     *
     * @return void
     */
    public function entry(): void
    {
        $firewall = $this->getFirewallInstance();

        $response = get_response();

        /**
         * Ex: /firewall/panel/user/login/
         *   => firewall/panel/user/login
         */
        $path = $firewall->getKernel()->getCurrentUrl();
        $path = trim($path, '/');

        /**
         * Ex: /firewall/panel/
         *   => firewall/panel
         */
        $base = $firewall->controlPanel();
        $base = trim($base, '/');

        /**
         * Ex: /user/login
         *   => user/login
         */
        $urlSegment = str_replace($base, '', $path);
        $urlSegment = trim($urlSegment, '/');

        if ($urlSegment === $base || $urlSegment === '') {
            $urlSegment = 'home/index';
        }

        $urlParts = explode('/', $urlSegment);

        $controller = $urlParts[0] ?? 'home';
        $method = $urlParts[1] ?? 'index';

        if (in_array($controller . '/' . $method, $this->registerRoutes)) {
            $this->setRouteBase($base);
            $this->checkAuth();

            $controller = __CLASS__ . '\\' . ucfirst($controller);
            $controllerClass = new $controller();
            $controllerClass->setCsrfField($this->getCsrfField());

            if ('demo' === $this->mode) {
                // For security reasons, the POST method is not allowed
                // in the Demo mode.
                set_request(get_request()->withParsedBody([])->withMethod('GET'));
                unset_superglobal(null, 'post');

                $controllerClass->demo(
                    $this->demoUser['user'],
                    $this->demoUser['pass']
                );
            }

            $this->resolver(call_user_func([$controllerClass, $method]));
        }

        $this->resolver($response->withStatus(404));
    }

    /**
     * Get routes.
     *
     * @return array
     */
    public static function getRoutes(): array
    {
        return [
            'ajax/changeLocale',
            'ajax/tryMessenger',
            'circle/filter',
            'circle/rule',
            'circle/session',
            'home/index',
            'home/overview',
            'iptables/ip4',
            'iptables/ip4status',
            'iptables/ip6',
            'iptables/ip6status',
            'report/actionLog',
            'report/operation',
            'security/authentication',
            'security/xssProtection',
            'setting/basic',
            'setting/exclusion',
            'setting/export',
            'setting/import',
            'setting/ipManager',
            'setting/messenger',
            'user/login',
            'user/logout',
            // Render the static asset files for embedding.
            // Since 2.0, not link to shieldon-io.github.io anymore.
            'asset/css',
            'asset/js',
            'asset/favicon',
            'asset/logo',
        ];
    }

    /**
     * Set the base route for the panel.
     *
     * @param string $base The base path.
     *
     * @return void
     */
    protected function setRouteBase(string $base)
    {
        if (!defined('SHIELDON_PANEL_BASE')) {
            // @codeCoverageIgnoreStart
            define('SHIELDON_PANEL_BASE', $base);
            // @codeCoverageIgnoreEnd
        }
    }

    /**
     * Prompt an authorization login.
     *
     * @return void
     */
    protected function checkAuth(): void
    {
        $check = get_session_instance()->get('shieldon_user_login');

        if (empty($check)) {
            $user = new User();
            $user->setCsrfField($this->getCsrfField());

            if ($this->mode === 'demo') {
                $user->demo(
                    $this->demoUser['user'],
                    $this->demoUser['pass']
                );
            }

            $this->resolver($user->login());
        }
    }

    /**
     * Magic method.
     *
     * Helps the property `$resolver` to work like a function.
     *
     * @param string $method The method name.
     * @param array  $args   The arguments.
     *
     * @return mixed
     */
    public function __call($method, $args)
    {
        if (property_exists($this, $method)) {
            $callable = $this->{$method};

            if (isset($args[0]) && $args[0] instanceof ResponseInterface) {
                return $callable($args[0]);
            }
        }
        // @codeCoverageIgnoreStart
    }
    // @codeCoverageIgnoreEnd

    /**
     * Get Firewall instance.
     *
     * @return Firewall
     */
    private function getFirewallInstance(): Firewall
    {
        $firewall = Container::get('firewall');

        if (!($firewall instanceof Firewall)) {
            throw new RuntimeException(
                'The Firewall instance should be initialized first.'
            );
        }

        return $firewall;
    }
}
