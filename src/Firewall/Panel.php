<?php
/*
 * This file is part of the Shieldon package.
 *
 * (c) Terry L. <contact@terryl.in>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Shieldon\Firewall;

use Psr\Http\Message\ResponseInterface;
use Shieldon\Firewall\HttpResolver;
use Shieldon\Firewall\Panel\DemoTrait;
use Shieldon\Firewall\Panel\User;
use function Shieldon\Firewall\get_request;
use function Shieldon\Firewall\get_response;

use function call_user_func;
use function explode;
use function in_array;
use function ini_set;
use function property_exists;
use function set_time_limit;
use function str_replace;
use function trim;
use function ucfirst;

/**
 * Increase PHP execution time. Becasue of taking long time to parse logs in a high-traffic site.
 */
set_time_limit(3600);

/**
 * Increase the memory limit. Becasue the log files may be large in a high-traffic site.
 */
ini_set('memory_limit', '128M');

/**
 * Firewall's Control Panel
 * Display a Control Panel UI for developers or administrators.
 */
class Panel
{
    use DemoTrait;

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
        $this->registerRoutes = [
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
        ];

        $this->resolver = new HttpResolver();
    }

    /**
     * Display pages.
     */
    public function entry($basePath): void
    {
        $request = get_request();
        $response = get_response();

        $path = trim($request->getUri()->getPath(), '/');
        $base = trim($basePath, '/');
        $urlSegment = trim(str_replace($base, '', $path), '/');

        if ($urlSegment === $basePath || $urlSegment === '') {
            $urlSegment = 'home/index';
        }

        $urlParts = explode('/', $urlSegment);

        $controller = $urlParts[0] ?? 'home';
        $method = $urlParts[1] ?? 'index';

        if (in_array("$controller/$method", $this->registerRoutes)) {

            define('SHIELDON_PANEL_BASE', $base);

            $this->checkAuth();

            $controller = __CLASS__ . '\\' . ucfirst($controller);

            $controllerClass = new $controller();

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
     * Prompt an authorization login.
     *
     * @return void
     */
    protected function checkAuth(): void
    {
        $check = get_session()->get('shieldon_user_login');

        if (empty($check)) {
            $this->resolver((new User)->login());
        }
    }

    /**
     * @param string $method
     * @param array  $args
     *
     * @return mixed
     */
    public function __call($method, $args)
    {
        if (property_exists($this, $method)) {
            $callable = $this->{$method};

            if (
                isset($args[0]) && 
                $args[0] instanceof ResponseInterface
            ) {
                return $callable($args[0]);
            }
        }
    }
}
