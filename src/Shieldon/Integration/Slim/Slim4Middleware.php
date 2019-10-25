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
use Psr\Http\Server\MiddlewareInterface as Middleware;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;

/**
 * Middleware for Slim 4 framework
 * 
 * @since 3.0.1
 */
class Slim4Middleware implements Middleware
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
     * Shieldon middleware invokable class.
     *
     * @param ServerRequest  $request PSR-7 request
     * @param RequestHandler $handler PSR-15 request handler
     *
     * @return Response
     */
    public function process(Request $request, RequestHandler $handler): Response
    {
        $firewall = new Firewall($this->storage);
        
        // Pass Slim CSRF Token to Captcha form.
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

        return $handler->handle($request);
    }
}
