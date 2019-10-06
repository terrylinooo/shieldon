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
     * @param  \Psr\Http\Message\ServerRequestInterface $request  PSR7 request
     * @param  \Psr\Http\Message\ResponseInterface      $response PSR7 response
     * @param  callable                                 $next     Next middleware
     *
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function __invoke(Request $request, Response $response, $next)
    {
        $firewall = new Firewall($this->storage);
        $firewall->restful();
        $firewall->run();

        return $next($request, $response);
    }
}
