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

namespace Shieldon\Firewall\Captcha;

/**
 * CaptchaInterface
 */
interface CaptchaInterface
{
    /**
     * Response the result.
     *
     * @return bool
     */
    public function response(): bool;

    /**
     * Output a required HTML.
     *
     * @return string
     */
    public function form(): string;
}
