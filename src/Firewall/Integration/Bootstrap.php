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

use Shieldon\Firewall\Container;
use Shieldon\Firewall\Firewall;
use Shieldon\Firewall\Panel;
use Shieldon\Firewall\HttpResolver;
use function dirname;
use function strpos;

/**
 * The easist way to implement Shieldon Firewall in your PHP project.
 *
 * [How to use]
 *
 * This class is supposed to be used in a very early stage of your code.
 * The position is right after Composer autoloader.
 *
 * [Example]
 *
 *     require_once '../vendor/autoload.php';
 *
 *     $shieldon = new \Shieldon\Firewall\Intergration\Bootstrap();
 *     $shieldon->run();
 *
 * [Note]
 *
 * If you use this approach on a PHP framework, make sure that the route
 * supports POST method, otherwise the CAPTCHA form will not work.
 */
class Bootstrap
{
    /**
     * Constuctor.
     *
     * @param string $storage    The absolute path of the storage where stores
     *                           Shieldon generated data.
     * @param string $requestUri The entry URL of Firewall Panel.
     *
     * @return void
     */
    public function __construct(string $storage = '', string $requestUri = '')
    {
        // Prevent possible issues occur in CLI command line.
        if (isset($_SERVER['REQUEST_URI'])) {
            $serverRequestUri = $_SERVER['REQUEST_URI'];
            $scriptFilename = $_SERVER['SCRIPT_FILENAME'];

            if ('' === $storage) {
                // The storage folder should be placed above www-root for best security,
                // this folder must be writable.
                $storage = dirname($scriptFilename) . '/../shieldon_firewall';
            }

            if ('' === $requestUri) {
                $requestUri = '/firewall/panel/';
            }

            $firewall = new Firewall();
            $firewall->configure($storage);
            $firewall->controlPanel($requestUri);

            if ($requestUri !== '' &&
                strpos($serverRequestUri, $requestUri) === 0
            ) {
                // Get into the Firewall Panel.
                $panel = new Panel();
                $panel->entry();
            }
        }
    }

    /**
     * Start protecting your site.
     *
     * @return void
     */
    public function run(): void
    {
        $firewall = Container::get('firewall');

        $response = $firewall->run();

        if ($response->getStatusCode() !== 200) {
            $httpResolver = new HttpResolver();
            $httpResolver($response);
        }
    }
}
