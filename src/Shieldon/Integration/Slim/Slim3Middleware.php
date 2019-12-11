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
 * Middleware for Slim 3 framework
 * 
 * @since 3.0.1
 */
class Slim3Middleware
{
    /**
     * The absolute path of the storage where stores Shieldon generated data.
     *
     * @var string
     */
    protected $storage = '';

    /**
     * Constructor.
     *
     * @param string $storage See property `storage` explanation.
     */
    public function __construct($storage = '')
    {
        // shieldon folder is placed above wwwroot for best security, this folder must be writable.
        $this->storage = dirname($_SERVER['SCRIPT_FILENAME']) . '/../shieldon';

        if ('' !== $storage) {
            $this->storage = $storage;
        }
    }

    /**
     * Shieldon middleware invokable class
     *
     * @param Request  $request  PSR7 request
     * @param Response $response PSR7 response
     * @param callable $next     Next middleware
     *
     * @return Response
     */
    public function __invoke(Request $request, Response $response, $next): Response
    {
        $firewall = new Firewall($this->storage);

        // Pass Slim CSRF Token to Captcha form.

        // Slim-Csrf no longer support Slim 3, please install older version 0.8.3 to get supported.
        // composer require slim/csrf:0.8.3  
        $firewall->getShieldon()->setCaptcha(new \Shieldon\Captcha\Csrf([
            'name' => 'csrf_name',
            'value' => $request->getAttribute('csrf_name'),
        ]));

        $firewall->getShieldon()->setCaptcha(new \Shieldon\Captcha\Csrf([
            'name' => 'csrf_value',
            'value' => $request->getAttribute('csrf_value'),
        ]));

        $firewall->restful();
        $firewall->run();

        return $next($request, $response);
    }
}
