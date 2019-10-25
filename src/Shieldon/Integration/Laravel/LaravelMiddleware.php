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

namespace Shieldon\Integration\Laravel;

use Shieldon\Firewall;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

/**
 * Middleware for Laravel framework (5.x - 6.x)
 * 
 * @since 3.1.0
 */
class LaravelMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param Request
     * @param Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        $firewall = new Firewall(storage_path('shieldon'));

        // Pass Laravel CSRF Token to Captcha form.
        $firewall->getShieldon()->setCaptcha(new \Shieldon\Captcha\Csrf([
            'name' => '_token',
            'value' => csrf_token(),
        ]));
        
        $firewall->restful();
        $firewall->run();

        return $next($request);
    }
}
