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

namespace Shieldon\Integration\Zend;

use Shieldon\Firewall;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\MiddlewareInterface as Middleware;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;

/**
 * PSR-15 Middleware for Zend Framework.
 * 
 * @since 3.1.0
 */
class Psr15Middleware implements Middleware
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
        $this->storage = dirname($_SERVER['SCRIPT_FILENAME']) . '/../data';

        if ('' !== $storage) {
            $this->storage = $storage;
        }
    }

    /**
     * Shieldon middleware invokable class.
     *
     * @param ServerRequest  $request PSR-7 request
     * @param RequestHandler $delegat PSR-15 request handler
     *
     * @return Response
     */
    public function process(Request $request, RequestHandler $handler): Response
    {
        $firewall = new Firewall($this->storage);
        
        // Pass \Zend\Validator\Csrf CSRF Token to Captcha form.
        $firewall->getShieldon()->setCaptcha(new \Shieldon\Captcha\Csrf([
            'name' => '_shieldon_csrf',
            'value' => $request->getAttribute('_shieldon_csrf'),
        ]));

        $firewall->restful();
        $firewall->run();

        return $handler->handle($request);
    }
}
