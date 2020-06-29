<?php declare(strict_types=1);
/*
 * This file is part of the Shieldon package.
 *
 * (c) Terry L. <contact@terryl.in>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Shieldon\Captcha;

use Shieldon\Utils\Container;

/**
 * ComponentPrivider
 */
abstract class CaptchaProvider implements CaptchaInterface
{
    /**
     * Constroctor.
     */
    public function __construct()
    {

    }

    /**
     * Is denied?
     *
     * @return bool
     */
    abstract function response(): bool;

    /**
     * Unique deny status code.
     *
     * @return string
     */
    abstract function form(): string;
}