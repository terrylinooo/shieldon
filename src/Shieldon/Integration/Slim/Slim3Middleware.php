<?php declare(strict_types=1);
/*
 * This file is part of the Shieldon package.
 *
 * (c) Terry L. <contact@terryl.in>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 * 
 */

namespace Shieldon\Intergration\Slim;

use Shieldon\Firewall;

/**
 * Middleware for Slim 3 framework
 * 
 * @since 3.1.0
 */
class Slim3Middleware
{
    /**
     * Shieldon middleware invokable class
     *
     * @param  \Psr\Http\Message\ServerRequestInterface $request  PSR7 request
     * @param  \Psr\Http\Message\ResponseInterface      $response PSR7 response
     * @param  callable                                 $next     Next middleware
     *
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function __invoke($request, $response, $next)
    {
        $firewall = new Firewall(storage_path('shieldon'));
        $firewall->restful();
        $firewall->run();

        return $next($request, $response);
    }
}
