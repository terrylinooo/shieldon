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
use Shieldon\Firewall\Firewall;
use Shieldon\Firewall\HttpResolver;
use Shieldon\Firewall\Captcha\Csrf;
use const TMP; // CakePHP

/**
 * CakePHP Middleware
 *
 * This middleware has been tested succesfully with CakePHP 3.8
 */
class CakePhp
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
        // The constant TMP is the path of CakePHP's tmp folder.
        // The Shieldon generated data is stored at that place.
        $this->storage = TMP . 'shieldon_firewall';
        $this->panelUri = '/firewall/panel/';

        if ('' !== $storage) {
            $this->storage = $storage;
        }

        if ('' !== $panelUri) {
            $this->panelUri = $panelUri;
        }
    }

    /**
     * Middleware invokable class.
     *
     * @param Request  $request  PSR7 request
     * @param Response $response PSR7 response
     * @param callable $next     Next middleware
     *
     * @return Response
     */
    public function __invoke(Request $request, Response $response, $next): Response
    {
        $firewall = new Firewall($request);
        $firewall->configure($this->storage);
        $firewall->controlPanel($this->panelUri);

        // Pass CSRF token to the Captcha form.
        // Note: The CsrfProtectionMiddleware was added in 3.5.0
        $firewall->getKernel()->setCaptcha(
            new Csrf([
                'name' => '_csrfToken',
                'value' => $request->getParam('_csrfToken'),
            ])
        );

        $response = $firewall->run();

        if ($response->getStatusCode() !== 200) {
            $httpResolver = new HttpResolver();
            $httpResolver($response);
        }

        return $next($request, $response);
    }
}
