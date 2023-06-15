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

use Psr\Http\Message\ServerRequestInterface as Request;
use Shieldon\Firewall\Firewall;
use Shieldon\Firewall\HttpResolver;
use Shieldon\Firewall\Captcha\Csrf;
use function storage_path; // Laravel
use function csrf_token; // Laravel

/**
 * Middleware for Laravel framework (5.x - 6.x)
 */
class Laravel
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
        // The Shieldon generated data is stored at that place.
        $this->storage = storage_path('shieldon_firewall');
        $this->panelUri = '/firewall/panel/';

        if ('' !== $storage) {
            $this->storage = $storage;
        }

        if ('' !== $panelUri) {
            $this->panelUri = $panelUri;
        }
    }

    /**
     * Handle an incoming request.
     *
     * @param Request $request The PSR-7 server request.
     * @param Closure $next    The next middleware.
     *
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $firewall = new Firewall($request);
        $firewall->configure($this->storage);
        $firewall->controlPanel($this->panelUri);

        // Pass Laravel CSRF Token to Captcha form.
        $firewall->getKernel()->setCaptcha(
            new Csrf([
                'name' => '_token',
                'value' => csrf_token(),
            ])
        );

        $response = $firewall->run();

        if ($response->getStatusCode() !== 200) {
            $httpResolver = new HttpResolver();
            $httpResolver($response);
        }

        return $next($request);
    }
}
