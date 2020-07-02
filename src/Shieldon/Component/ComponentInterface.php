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
     * @param array $itemList String list.
     *
     * @return void
     */
    function setDeniedItems(array $itemList): void;

    /**
     * Set denied item.
     *
     * @param string|array $value
     * @param string       $key
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
     * @param string $key
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
     * @param string $key
     *
     * @return bool
     */
    function hasDeniedItem(string $key): bool;

    /**
     * Get denied items have the same prefix.
     *
     * @param string $key
     *
     * @return bool
     */
    function getDeniedItemsWithPrefix(string $key): array;

    /**
     * Remove denied items with the same prefix.
     *
     * @param string $key
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