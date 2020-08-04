<?php
/**
 * This file is part of the Shieldon package.
 *
 * (c) Terry L. <contact@terryl.in>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * php version 7.1.0
 *
 * @category  Web-security
 * @package   Shieldon
 * @author    Terry Lin <contact@terryl.in>
 * @copyright 2019 terrylinooo
 * @license   https://github.com/terrylinooo/shieldon/blob/2.x/LICENSE MIT
 * @link      https://github.com/terrylinooo/shieldon
 * @see       https://shieldon.io
 */

declare(strict_types=1);

namespace Shieldon\Firewall;

use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Server\MiddlewareInterface;
use Shieldon\Firewall\Kernel;
use Shieldon\Firewall\Utils\Container;
use Shieldon\Firewall\FirewallTrait;
use Shieldon\Firewall\Firewall\SetupTrait;
use Shieldon\Firewall\Firewall\Messenger\MessengerTrait;
use Shieldon\Firewall\Firewall\XssProtectionTrait;
use Shieldon\Psr15\RequestHandler;
use function Shieldon\Firewall\get_request;
use function defined;
use function file_exists;
use function file_get_contents;
use function json_decode;
use function rtrim;

/**
 * Managed Firewall.
 */
class Firewall
{
    /**
     *   Public methods       | Desctiotion
     *  ----------------------|---------------------------------------------
     *   setup                | Apply all setup proccesses.
     *   configure            | The absolute path of a dictionary for storing data.
     *   run                  | Execute the firewall.
     *   add                  | Add a PRS-15 middleware used before firewall.
     *   controlPanel         | Set the base URL of the control panel.
     *  ----------------------|---------------------------------------------
     */

    /**
     *   Public methods       | Desctiotion
     *  ----------------------|---------------------------------------------
     *   getKernel            | Get the Shieldon Kernel instance.
     *   getConfiguration     | Get the configuration data.
     *   getDirectory         | Get the dictionary where the data is stored.
     *   getFileName          | Get the path of the configuration file.
     *   getConfig            | Get the value by identification string.
     *   setConfig            | Set the value by identification string.
     *  ----------------------|---------------------------------------------
     */
    use FirewallTrait;

    /**
     *   Public methods       | Desctiotion
     *  ----------------------|---------------------------------------------
     *                        | No public methods.
     *  ----------------------|---------------------------------------------
     */
    use SetupTrait;

    /**
     *   Public methods       | Desctiotion
     *  ----------------------|---------------------------------------------
     *                        | No public methods.
     *  ----------------------|---------------------------------------------
     */
    use XssProtectionTrait;

    /**
     *   Public methods       | Desctiotion
     *  ----------------------|---------------------------------------------
     *                        | No public methods.
     *  ----------------------|---------------------------------------------
     */
    use MessengerTrait;

    /**
     * Collection of PSR-7 or PSR-15 middlewares.
     *
     * @var array
     */
    protected $middlewares = [];

    /**
     * The URI of the control panel.
     *
     * @var string
     */
    protected $controlPanelUri = '';

    /**
     * Constructor.
     * 
     * @param ServerRequestInterface|null $request  A PSR-7 server request.
     * @param ResponseInterface|null      $response A PSR-7 server response.
     */
    public function __construct(?ServerRequestInterface $request = null, ?ResponseInterface $response = null)
    {
        Container::set('firewall', $this);

        $this->kernel = new Kernel($request, $response);
    }

    /**
     * Set up everything we need.
     *
     * @return void
     */
    public function setup(): void
    {
        $this->status = $this->getOption('daemon');

        $setupFunctions = [
            'Driver',
            'Channel',
            'Filters',
            'Components',
            'IpSource',
            'Logger',
            'LimitSession',
            'CronJob',
            'ExcludedUrls',
            'XssProtection',
            'PageAuthentication',
            'DialogUserInterface',
            'Messengers',
            'Captchas',
            'MessageEvents',
            'DenyTooManyAttempts',
            'IptablesBridgeDirectory',
        ];

        foreach ($setupFunctions as $func) {
            $function = 'setup' . $func;

            $this->{$function}();
        }
    }

    /**
     * Set up the path of the configuration file.
     *
     * @param string $source The path.
     * @param string $type   The type.
     * 
     * @return void
     */
    public function configure(string $source, string $type = 'json'): void
    {
        if ($type === 'json') {
            $this->directory = rtrim($source, '\\/');
            $configFilePath = $this->directory . '/' . $this->filename;

            if (file_exists($configFilePath)) {
                $jsonString = file_get_contents($configFilePath);

            } else {
                $jsonString = file_get_contents(__DIR__ . '/../../config.json');

                if (defined('PHP_UNIT_TEST')) {
                    $jsonString = file_get_contents(__DIR__ . '/../../tests/config.json');
                }
            }

            $this->configuration = json_decode($jsonString, true);
            $this->kernel->managedBy('managed');

        } elseif ($type === 'php') {
            $this->configuration = include $source;
            $this->kernel->managedBy('config');
        }

        $this->setup();
    }

    /**
     * Just, run!
     *
     * @return ResponseInterface
     */
    public function run(): ResponseInterface
    {
        // If settings are ready, let's start monitoring requests.
        if ($this->status) {

            $response = get_request();

            // PSR-15 request handler.
            $requestHandler = new RequestHandler();

            foreach ($this->middlewares as $middleware) {
                $requestHandler->add($middleware);
            }

            $response = $requestHandler->handle($response);

            // Something is detected by Middlewares, return.
            if ($response->getStatusCode() !== $this->kernel::HTTP_STATUS_OK) {
                return $response;
            }

            $result = $this->kernel->run();

            if ($result !== $this->kernel::RESPONSE_ALLOW) {

                if ($this->kernel->captchaResponse()) {
                    $this->kernel->unban();

                    $response = $response->withHeader('Location', $this->kernel->getCurrentUrl());
                    $response = $response->withStatus($this->kernel::HTTP_STATUS_SEE_OTHER);

                    return $response;
                }
            }
        }

        return $this->kernel->respond();
    }

    /**
     * Add middlewares and use them before going into Shieldon kernal.
     *
     * @param MiddlewareInterface $middleware A PSR-15 middlewares.
     *
     * @return void
     */
    public function add(MiddlewareInterface $middleware): void
    {
        $this->middlewares[] = $middleware;
    }

    /**
     * The base URL for control panel.
     *
     * @param string $uri The path component of a URI
     *
     * @return string
     */
    public function controlPanel(string $uri = ''): string
    {
        if (!empty($uri)) {
            $uri = '/' . trim($uri, '/');
            $this->controlPanelUri = $uri;
            $this->getKernel()->exclude($this->controlPanelUri);
        }

        return $this->controlPanelUri;
    }

    /**
     * Set the channel ID.
     *
     * @return void
     */
    protected function setupChannel(): void
    {
        $channelId = $this->getOption('channel_id');

        if ($channelId) {
            $this->kernel->setChannel($channelId);
            $this->channel = $channelId;
        }
    }
}
