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

namespace Shieldon\Firewall\Component;

/**
 * ComponentPrivider
 */
abstract class ComponentProvider
{
    /**
     * It is really strict.
     *
     * @var bool
     */
    protected $strictMode = false;

    /**
     * Enable strict mode.
     *
     * @param bool $bool Set true to enble strict mode, false to disable it overwise.
     *
     * @return void
     */
    public function setStrict(bool $bool): void
    {
        $this->strictMode = $bool;
    }

    /**
     * Unique deny status code.
     *
     * @return int
     */
    abstract public function getDenyStatusCode(): int;
}
