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

namespace Shieldon\Firewall\Integration;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\MiddlewareInterface as Middleware;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use Shieldon\Firewall\Firewall;
use Shieldon\Firewall\HttpResolver;
use Shieldon\Firewall\Captcha\Csrf;
use function dirname;

/**
 * Middleware for Slim 4 framework
 */
class Slim4 implements Middleware
{
    /**
     * The absolute path of the storage where stores Shieldon generated data.
     *
     * @var string
     */
    protected $storage;

    /**
     * The entry point of Shieldon Firewall's control panel.
     *
     * For example: https://yoursite.com/firewall/panel/
     * Just use the path component of a URI.
     *
     * @var string
     */
    protected $panelUri;

    /**
     * Constructor.
     *
     * @param string $storage  See property `storage` explanation.
     * @param string $panelUri See property `panelUri` explanation.
     *
     * @return void
     */
    public function __construct(string $storage = '', string $panelUri = '')
    {
        $dir = dirname($_SERVER['SCRIPT_FILENAME']);

        $this->storage = $dir . '/../shieldon_firewall';
        $this->panelUri = '/firewall/panel/';

        if ('' !== $storage) {
            $this->storage = $storage;
        }

        if ('' !== $panelUri) {
            $this->panelUri = $panelUri;
        }
    }

    /**
     * Shieldon middleware invokable class.
     *
     * @param Request        $request PSR-7 request
     * @param RequestHandler $handler PSR-15 request handler
     *
     * @return Response
     */
    public function process(Request $request, RequestHandler $handler): Response
    {
        $firewall = new Firewall($request);
        $firewall->configure($this->storage);
        $firewall->controlPanel($this->panelUri);
        
        // Pass Slim CSRF Token to Captcha form.
        $firewall->getKernel()->setCaptcha(
            new Csrf([
                'name' => 'csrf_name',
                'value' => $request->getAttribute('csrf_name'),
            ])
        );

        $firewall->getKernel()->setCaptcha(
            new Csrf([
                'name' => 'csrf_value',
                'value' => $request->getAttribute('csrf_value'),
            ])
        );

        $response = $firewall->run();

        if ($response->getStatusCode() !== 200) {
            $httpResolver = new HttpResolver();
            $httpResolver($response);
        }

        return $handler->handle($request);
    }
}
