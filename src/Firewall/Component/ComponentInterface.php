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
 * ComponentInterface
 */
interface ComponentInterface
{
    /**
     * Set denied item list. 
     *
     * @param array $itemList String list.
     *
     * @return void
     */
    function setDeniedItems(array $itemList): void;

    /**
     * Set denied item.
     *
     * @param string|array $value The value of the data.
     * @param string       $key   The key of the data.
     *
     * @return void
     */
    function setDeniedItem($value, string $key): void;

    /**
     * Return current denied list.
     *
     * @return array
     */
    function getDeniedItems(): array;

    /**
     * Remove a denied item.
     *
     * @param string $key The key of the data.
     *
     * @return void
     */
    function removeDeniedItem(string $key): void;

    /**
     * Remove all items.
     *
     * @return void
     */
    function removeDeniedItems(): void;

    /**
     * Check if a denied item exists.
     *
     * @param string $key The key of the data.
     *
     * @return bool
     */
    function hasDeniedItem(string $key): bool;

    /**
     * Get denied items have the same prefix.
     *
     * @param string $key The key of the data.
     *
     * @return bool
     */
    function getDeniedItemsWithPrefix(string $key): array;

    /**
     * Remove denied items with the same prefix.
     *
     * @param string $key The key of the data.
     *
     * @return bool
     */
    function removeDeniedItemsWithPrefix(string $key): void;

    /**
     * Unique deny status code.
     *
     * @return int
     */
    function getDenyStatusCode(): int;
}
