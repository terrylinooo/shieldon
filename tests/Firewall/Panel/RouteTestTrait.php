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

namespace Shieldon\FirewallTest\Panel;

use Psr\Http\Message\ResponseInterface;
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

trait RouteTestTrait
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
        'asset/css',
        'asset/js',
        'asset/favicon',
        'asset/logo',
    ];

    /**
     * IP address.
     *
     * @var stromg
     */
    public $ip = '';

    /**
     * Test routes.
     */
    public function route($output = true)
    {

        $basePath = 'firewall/panel';
        $firewall = new \Shieldon\Firewall\Firewall();
        $firewall->configure(BOOTSTRAP_DIR . '/../tmp/shieldon');
        
        $firewall->setConfig('messengers.telegram.enable', true);
        $firewall->setConfig('messengers.telegram.confirm_test', true);
        $firewall->setConfig('messengers.components.ip.enable', false);
        $firewall->setConfig('messengers.components.rdns.enable', false);
        $firewall->setConfig('messengers.components.header.enable', false);
        $firewall->setConfig('messengers.components.user_agent.enable', false);
        $firewall->setConfig('messengers.components.trusted_bot.enable', false);
        $firewall->setConfig('messengers.filters.fequency.enable', false);
        $firewall->setConfig('messengers.filters.referer.enable', false);
        $firewall->setConfig('messengers.filters.cookie.enable', false);
        $firewall->setConfig('messengers.filters.cookie.enable', false);
        $firewall->setConfig('captcha_modules.recaptcha.enable', false);
        $firewall->setConfig('captcha_modules.image.enable', false);
        $firewall->setConfig('iptables.enable', true);
        $firewall->setConfig('iptables.config.watching_folder', BOOTSTRAP_DIR . '/../tmp/shieldon/iptables');
        $firewall->setConfig('ip6tables.enable', true);
        $firewall->setConfig('ip6tables.config.watching_folder', BOOTSTRAP_DIR . '/../tmp/shieldon/iptables');
        $firewall->setup();

        $firewall->getKernel()->setProperty(
            'iptables_watching_folder',
            BOOTSTRAP_DIR . '/../tmp/shieldon/iptables'
        );

        $firewall->getKernel()->setLogger(
            new \Shieldon\Firewall\Log\ActionLogger(
                BOOTSTRAP_DIR . '/../tmp/shieldon/action_logs'
            )
        );

        $firewall->getKernel()->setDriver(
            new \Shieldon\Firewall\Driver\FileDriver(BOOTSTRAP_DIR . '/../tmp/shieldon')
        );

        $firewall->getKernel()->setMessenger(
            new \Shieldon\Messenger\Telegram('mock-key', 'mock-channel-id')
        );

        $firewall->getKernel()->disableFilters();
        $firewall->getKernel()->disableComponents();
        $firewall->getKernel()->disableCaptcha();

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
            if (!defined('SHIELDON_PANEL_BASE')) {
                define('SHIELDON_PANEL_BASE', $base);
            }

            $controller = '\Shieldon\Firewall\Panel\\' . ucfirst($controller);
            $controllerClass = new $controller();

            if ($output) {
                $resolver(call_user_func([$controllerClass, $method]));
            } else {
                return call_user_func([$controllerClass, $method]);
            }
        }

        if ($output) {
            $resolver($response->withStatus(404));
        } else {
            return $response->withStatus(404);
        }
    }

    /**
     * Check whether the page contains a string.
     *
     * @param string $uri    The page's URI path.
     * @param string $string Usually the page title.
     *
     * @return void
     */
    public function assertOutputContainsString(string $uri, string $string)
    {
        $_SERVER['REQUEST_URI'] = '/' . trim($uri, '/');

        ob_start();
        $this->route();
        $output = ob_get_contents();
        ob_end_clean();

        $this->assertStringContainsString($string, $output);
    }

    /**
     * Check whether the page "NOT" contains a string.
     *
     * @param string $uri    The page's URI path.
     * @param string $string Usually the page title.
     *
     * @return void
     */
    public function assertOutputNotContainsString(string $uri, string $string)
    {
        $response = $this->getRouteResponse($uri);
        $stream = $response->getBody();

        if (strpos($stream->getContents(), $string) === false) {
            $this->assertTrue(true);
        } else {
            $this->assertTrue(false);
        }
    }

    /**
     * Check whether the page contains a string.
     *
     * @param string $uri    The page's URI path.
     * @param string $string Usually the page title.
     *
     * @return ResponseInterface
     */
    public function getRouteResponse(string $uri): ResponseInterface
    {
        $_SERVER['REQUEST_URI'] = '/' . trim($uri, '/');

        return $this->route(false);
    }

    /**
     * Set IP address.
     *
     * @param string $ip
     *
     * @return void
     */
    public function setIp($ip)
    {
        $this->ip = $ip;
    }
}
