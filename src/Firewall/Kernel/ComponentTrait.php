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

namespace Shieldon\Firewall\Kernel;

use Shieldon\Firewall\Kernel\Enum;
use Shieldon\Firewall\Component\ComponentProvider;
use Shieldon\Firewall\Component\TrustedBot;
use Shieldon\Firewall\Component\Ip;

/*
 * @since 1.0.0
 */
trait ComponentTrait
{
    /**
     *   Public methods       | Desctiotion
     *  ----------------------|---------------------------------------------
     *   setComponent         | Set a commponent.
     *   getComponent         | Get a component instance from component's container.
     *   disableComponents    | Disable all components.
     *  ----------------------|---------------------------------------------
     */

    /**
     * Get a class name without namespace string.
     *
     * @param object $instance Class
     *
     * @return string
     */
    abstract protected function getClassName($instance): string;

    /**
     * Start an action for this IP address, allow or deny, and give a reason for it.
     *
     * @param int    $actionCode The action code. - 0: deny, 1: allow, 9: unban.
     * @param string $reasonCode The response code.
     * @param string $assignIp   The IP address.
     *
     * @return void
     */
    abstract protected function action(int $actionCode, int $reasonCode, string $assignIp = ''): void;

    /**
     * Deal with online sessions.
     *
     * @param int $statusCode The response code.
     *
     * @return int The response code.
     */
    abstract protected function sessionHandler($statusCode): int;

    /**
     * Save and return the result identifier.
     * This method is for passing value from traits.
     *
     * @param int $resultCode The result identifier.
     *
     * @return int
     */
    abstract protected function setResultCode(int $resultCode): int;

    /**
     * Container for Shieldon components.
     *
     * @var array
     */
    public $component = [];

    /**
     * Set a commponent.
     *
     * @param ComponentProvider $instance The component instance.
     *
     * @return void
     */
    public function setComponent(ComponentProvider $instance): void
    {
        $class = $this->getClassName($instance);
        $this->component[$class] = $instance;
    }

    /**
     * Get a component instance from component's container.
     *
     * @param string $name The component's class name.
     *
     * @return ComponentProvider|null
     */
    public function getComponent(string $name)
    {
        if (!isset($this->component[$name])) {
            return null;
        }

        return $this->component[$name];
    }

    /**
     * Disable all components.
     *
     * @return void
     */
    public function disableComponents(): void
    {
        $this->component = [];
    }

    /*
    |--------------------------------------------------------------------------
    | Stage in Kernel
    |--------------------------------------------------------------------------
    | The below methods are used in "process" method in Kernel.
    */

    /**
     * Initialize components.
     *
     * @return void
     */
    protected function initComponents()
    {
        foreach (array_keys($this->component) as $name) {
            $this->component[$name]->setIp($this->ip);
            $this->component[$name]->setRdns($this->rdns);

            // Apply global strict mode to all components by `strictMode()` if nesscessary.
            if (isset($this->strictMode)) {
                $this->component[$name]->setStrict($this->strictMode);
            }
        }
    }

    /**
     * Check if current IP is trusted or not.
     *
     * @return bool
     */
    protected function isTrustedBot()
    {
        $trustedBot = $this->getComponent('TrustedBot');

        if ($trustedBot instanceof TrustedBot) {
            // We want to put all the allowed robot into the rule list, so that the checking of IP's resolved hostname
            // is no more needed for that IP.
            if ($trustedBot->isAllowed()) {
                if ($trustedBot->isGoogle()) {
                    // Add current IP into allowed list, because it is from real Google domain.
                    $this->action(
                        Enum::ACTION_ALLOW,
                        Enum::REASON_IS_GOOGLE_ALLOWED
                    );
                } elseif ($trustedBot->isBing()) {
                    // Add current IP into allowed list, because it is from real Bing domain.
                    $this->action(
                        Enum::ACTION_ALLOW,
                        Enum::REASON_IS_BING_ALLOWED
                    );
                } elseif ($trustedBot->isYahoo()) {
                    // Add current IP into allowed list, because it is from real Yahoo domain.
                    $this->action(
                        Enum::ACTION_ALLOW,
                        Enum::REASON_IS_YAHOO_ALLOWED
                    );
                } else {
                    // Add current IP into allowed list, because you trust it.
                    // You have already defined it in the settings.
                    $this->action(
                        Enum::ACTION_ALLOW,
                        Enum::REASON_IS_SEARCH_ENGINE_ALLOWED
                    );
                }
                // Allowed robots not join to our traffic handler.
                $this->setResultCode(Enum::RESPONSE_ALLOW);
                return true;
            }
        }
        return false;
    }

    /**
     * Check whether the IP is fake search engine or not.
     * The method "isTrustedBot()" must be executed before this method.
     *
     * @return bool
     */
    protected function isFakeRobot(): bool
    {
        $trustedBot = $this->getComponent('TrustedBot');

        if ($trustedBot instanceof TrustedBot) {
            if ($trustedBot->isFakeRobot()) {
                $this->action(
                    Enum::ACTION_DENY,
                    Enum::REASON_COMPONENT_TRUSTED_ROBOT_DENIED
                );
                $this->setResultCode(Enum::RESPONSE_DENY);
                return true;
            }
        }
        return false;
    }

    /**
     * Check whether the IP is handled by IP compoent or not.
     *
     * @return bool
     */
    protected function isIpComponent(): bool
    {
        $ipComponent = $this->getComponent('Ip');

        if ($ipComponent instanceof Ip) {
            $result = $ipComponent->check($this->ip);

            if (!empty($result)) {
                switch ($result['status']) {
                    case 'allow':
                        $actionCode = Enum::ACTION_ALLOW;
                        $reasonCode = $result['code'];
                        break;
                    case 'deny':
                    default:
                        $actionCode = Enum::ACTION_DENY;
                        $reasonCode = $result['code'];
                        break;
                }

                $this->action($actionCode, $reasonCode);

                // $resultCode = $actionCode
                $this->setResultCode($this->sessionHandler($actionCode));
                return true;
            }
        }
        return false;
    }

    /**
     * Check other compoents.
     *
     * @return bool
     */
    protected function isComponents()
    {
        foreach ($this->component as $component) {
            if ($component->isDenied()) {
                $this->action(
                    Enum::ACTION_DENY,
                    $component->getDenyStatusCode()
                );

                $this->setResultCode(Enum::RESPONSE_DENY);
                return true;
            }
        }
        return false;
    }
}
