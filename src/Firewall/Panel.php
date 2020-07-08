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
use Shieldon\Psr7\Response;
use function Shieldon\Firewall\get_request;
use function explode;


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
    /**
     * Route map.
     *
     * @var array
     */
    protected $registerRoutes;

    /**
     * Firewall panel constructor.                         
     */
    public function __construct() 
    {
        $this->registerRoutes = [
            '',
            'user/login',
            'user/logout',
        ];
    }

    /**
     * Display pages.
     *
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function entry($basePath): ResponseInterface
    {
        $request = get_request();

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
            $controller = __CLASS__ . '\\' . ucfirst($controller);

            return call_user_func([(new $controller), $method]);
        }

        return (new Response)->withStatus(404);
    }
}
