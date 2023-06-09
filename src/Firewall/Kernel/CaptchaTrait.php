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
use Shieldon\Firewall\Captcha\CaptchaInterface;

/*
 * Captcha Trait is loaded in Kernel instance only.
 */
trait CaptchaTrait
{
    /**
     *   Public methods       | Desctiotion
     *  ----------------------|---------------------------------------------
     *   setCaptcha           | Set a captcha.
     *   captchaResponse      | Return the result from Captchas.
     *   disableCaptcha       | Mostly be used in unit testing purpose.
     *  ----------------------|---------------------------------------------
     */

    /**
     * Container for captcha addons.
     * The collection of \Shieldon\Firewall\Captcha\CaptchaInterface
     *
     * @var array
     */
    public $captcha = [];

    /**
     * Get a class name without namespace string.
     *
     * @param object $instance Class
     *
     * @return string
     */
    abstract protected function getClassName($instance): string;

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
     * Set a captcha.
     *
     * @param CaptchaInterface $instance The captcha instance.
     *
     * @return void
     */
    public function setCaptcha(CaptchaInterface $instance): void
    {
        $class = $this->getClassName($instance);
        $this->captcha[$class] = $instance;
    }

    /**
     * Return the result from Captchas.
     *
     * @return bool
     */
    public function captchaResponse(): bool
    {
        foreach ($this->captcha as $captcha) {
            if (!$captcha->response()) {
                return false;
            }
        }

        /**
         * $sessionLimit @ SessionTrait
         * sessionHandler() @ SessionTrait
         */
        if (!empty($this->sessionLimit['count'])) {
            return (bool) $this->setResultCode(
                $this->sessionHandler(Enum::RESPONSE_ALLOW)
            );
        }

        return true;
    }

    /**
     * Mostly be used in unit testing purpose.
     *
     * @return void
     */
    public function disableCaptcha(): void
    {
        $this->captcha = [];
    }
}
