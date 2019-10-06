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

namespace Shieldon\Integration\Slim;

use Shieldon\Firewall;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

/**
 * Middleware for Slim 3 framework with Slim CSRF Guard.
 * Please notice that, this middleware must be implemented after Slim CSRF Guard.
 *
 * @since 3.0.1
 */
class Slim3MiddlewareWithCsrf extends Slim3Middleware
{
    /**
     * Constructor.
     *
     * @param string $storage See property `storage` explanation in Slim3Middleware.
     */
    public function __construct($storage = '')
    {
        parent::__construct($storage);
    }

    /**
     * Shieldon middleware invokable class
     *
     * @param  \Psr\Http\Message\ServerRequestInterface $request  PSR7 request
     * @param  \Psr\Http\Message\ResponseInterface      $response PSR7 response
     * @param  callable                                 $next     Next middleware
     *
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function __invoke(Request $request, Response $response, $next)
    {
        $firewall = new Firewall(storage_path('shieldon'));

        // Pass Slim CSRF Token to Captcha form.
        $firewall->shieldon->setCaptcha(new \Shieldon\Captcha\Csrf([
            'key' => $request->getAttribute('csrf_name'),
            'value' => $request->getAttribute('csrf_value'),
        ]));

        $firewall->restful();
        $firewall->run();

        return $next($request, $response);
    }
}
