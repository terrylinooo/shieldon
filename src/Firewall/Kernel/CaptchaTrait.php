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

use Shieldon\Firewall\Captcha\CaptchaInterface;

/*
 * Captcha Trait is loaded in Kernel instance only.
 */
trait CaptchaTrait
{
    /**
     * Container for captcha addons.
     * The collection of \Shieldon\Firewall\Captcha\CaptchaInterface
     *
     * @var array
     */
    public $captcha = [];

    /**
     * Set a captcha.
     *
     * @param CaptchaInterface $instance
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
            $this->result = $this->sessionHandler(self::RESPONSE_ALLOW);
        }

        return true;
    }
}
