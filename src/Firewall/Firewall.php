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

namespace Shieldon\Firewall;

use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Server\MiddlewareInterface;
use Shieldon\Firewall\Kernel;
use Shieldon\Firewall\Utils\Container;
use Shieldon\Firewall\FirewallTrait;
use Shieldon\Firewall\MainTrait;
use Shieldon\Firewall\MessengerTrait;
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
    use FirewallTrait;
    use MainTrait;
    use XssProtectionTrait;
    use MessengerTrait;

    /**
     * Collection of PSR-7 or PSR-15 middlewares.
     *
     * @var array
     */
    protected $middlewares = [];

    /**
     * Constructor.
     */
    public function __construct(?ServerRequestInterface $request = null, ?ResponseInterface $response = null)
    {
        Container::set('firewall', $this);

        $this->kernel = new Kernel($request, $response);
    }

    /**
     * Setup everything we need.
     *
     * @return void
     */
    public function setup(): void
    {
        $this->status = $this->getOption('daemon');

        $this->setDriver();

        $this->setChannel();

        $this->setIpSource();

        $this->setLogger();

        $this->setSessionLimit();

        $this->setCronJob();

        $this->setExcludedUrls();

        $this->setXssProtection();

        $this->setPageAuthentication();

        $this->setDialogUserInterface();

        $this->setMessengers();

        $this->setMessageEvents();

        $this->setDenyTooManyAttempts();

        $this->setIptablesBridgeDirectory();
    }

    /**
     * Set up the path of the configuration file.
     *
     * @param string $source The path.
     * @param string $type   The type.
     * 
     * @return void
     */
    public function configure(string $source, string $type = 'json')
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
            $this->configuration = require $source;
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
            if ($response->getStatusCode() !== 200) {
                return $response;
            }

            $result = $this->kernel->run();

            if ($result !== $this->kernel::RESPONSE_ALLOW) {

                if ($this->kernel->captchaResponse()) {
                    $this->kernel->unban();

                    $response = $response->withHeader('Location', $this->kernel->getCurrentUrl());
                    $response = $response->withStatus(303);

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
    public function add(MiddlewareInterface $middleware)
    {
        $this->middlewares[] = $middleware;
    }
}
