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

namespace Shieldon\Integration\CakePhp;

use Shieldon\Firewall;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

/**
 * CakephpMiddleware
 *
 * This middleware has been tested succesfully with CakePHP 3.8
 * 
 * @since 3.0.1
 */
class CakePhpMiddleware
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
        // shieldon folder is placed at CakePHP's tmp folder, creating a `shieldon` folder.
        $this->storage = TMP . 'shieldon';

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

        // Pass CSRF Token to Captcha form.
        // Cakephp notice: The CsrfProtectionMiddleware was added in 3.5.0
        $firewall->getShieldon()->setCaptcha(new \Shieldon\Captcha\Csrf([
            'name' => '_csrfToken',
            'value' => $request->getParam('_csrfToken'),
        ]));

        $firewall->restful();
        $firewall->run();

        return $next($request, $response);
    }
}
