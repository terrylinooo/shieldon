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

namespace Shieldon\FirewallTest\Panel;

use Shieldon\Firewall\HttpResolver;
use Shieldon\Firewall\Helpers;
use function Shieldon\Firewall\get_request;
use function Shieldon\Firewall\get_response;
use function call_user_func;
use function define;
use function explode;
use function in_array;
use function str_replace;
use function trim;
use function ucfirst;

trait RouteTrait
{
    /**
     * Route map.
     *
     * @var array
     */
    public $registerRoutes = [
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

    /**
     * Test routes.
     *
     * @param string $basePath The base URL of the firewall panel.
     *
     * @return void
     */
    public function route($basePath)
    {
        $firewall = new \Shieldon\Firewall\Firewall();
        $firewall->configure(BOOTSTRAP_DIR . '/../tmp/shieldon');

        new Helpers();

        $resolver = new HttpResolver();

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

            $controller = '\Shieldon\Firewall\Panel\\' . ucfirst($controller);
            $controllerClass = new $controller();

            $resolver(call_user_func([$controllerClass, $method]));
        }

        $resolver($response->withStatus(404));
    }
}