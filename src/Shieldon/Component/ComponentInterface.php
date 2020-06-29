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

namespace Shieldon\Component;

/**
 * ComponentInterface
 */
interface ComponentInterface
{
    /**
     * Set denied item list. 
     *
     * @param array $stringList String list.
     *
     * @return void
     */
    function setDeniedList(array $stringList): void;

    /**
     * Set denied item.
     *
     * @param string $string
     *
     * @return void
     */
    function setDeniedItem(string $string): void;

    /**
     * Return current denied list.
     *
     * @return array
     */
    function getDeniedList(): array;

    /**
     * Remove a denied item.
     *
     * @param string $string
     *
     * @return void
     */
    function removeItem(string $string): void;

    /**
     * Unique deny status code.
     *
     * @return int
     */
    function getDenyStatusCode(): int;
}