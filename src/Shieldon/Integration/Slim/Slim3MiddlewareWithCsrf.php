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
 * Middleware for Slim 3 framework with Slim CSRF Guard.
 * 
 * Note: This middleware must be implemented after Slim CSRF Guard.
 * 
 * @since 3.1.0
 */
class Slim3MiddlewareWithCsrf
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
        $name = $request->getAttribute('csrf_name');
        $value = $request->getAttribute('csrf_value');

        $firewall = new Firewall(storage_path('shieldon'));

        // Pass Laravel CSRF Token to Captcha form.
        $firewall->shieldon->setCaptcha(new \Shieldon\Captcha\Csrf([
            'key' => $name,
            'value' => $value,
        ]));

        $firewall->restful();
        $firewall->run();

        return $next($request, $response);
    }
}
