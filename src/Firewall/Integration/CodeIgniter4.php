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

use Shieldon\Firewall\Firewall;
use Shieldon\Firewall\HttpResolver;
use CodeIgniter\HTTP\RequestInterface as Request;
use CodeIgniter\HTTP\ResponseInterface as Response;
use CodeIgniter\Filters\FilterInterface;
use Shieldon\Firewall\Captcha\Csrf;
use function dirname;
use function csrf_token; // CodeIgniter 4
use function csrf_hash; // CodeIgniter 4

/**
 * CodeIgniter 4 Middleware of Shieldon Firewall.
 */
class CodeIgniter4 implements FilterInterface
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

        $this->storage = $dir . '/../writable/shieldon_firewall';
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
     * @param Request $request
     *
     * @return mixed
     */
    public function before(Request $request)
    {
        if ($request->isCLI()) {
            return;
        }

        // CodeIgniter 4 is not a PSR-7 compatible framework, therefore we don't
        // pass the Reqest and Reposne to Firewall instance.
        // Shieldon will create them by its HTTP factory.
        $firewall = new Firewall();
        $firewall->configure($this->storage);
        $firewall->controlPanel($this->panelUri);

        // Pass CodeIgniter CSRF Token to Captcha form.
        $firewall->getKernel()->setCaptcha(
            new Csrf([
                'name' => csrf_token(),
                'value' => csrf_hash(),
            ])
        );

        $response = $firewall->run();

        if ($response->getStatusCode() !== 200) {
            $httpResolver = new HttpResolver();
            $httpResolver($response);
        }
    }

    /**
     * We don't have anything to do here.
     *
     * @param Response $request
     * @param Response $response
     *
     * @return mixed
     */
    public function after(Request $request, Response $response)
    {
        // We don't have anything to do here.
    }
}
