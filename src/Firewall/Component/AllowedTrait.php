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

use function array_keys;
use function array_push;
use function in_array;
use function strpos;

/*
 * @since 2.0.0
 */
trait AllowedTrait
{
    /**
     * Data pool for hard whitelist.
     *
     * @var array
     */
    protected $allowedList = [];

    /**
     * Add items to the whitelist pool.
     *
     * @param array $itemList String list.
     *
     * @return void
     */
    public function setAllowedItems(array $itemList): void
    {
        $this->allowedList = $itemList;
    }

    /**
     * Add an item to the whitelist pool.
     *
     * @param string|array $value The value of the data.
     * @param string       $key   The key of the data.
     *
     * @return void
     */
    public function setAllowedItem($value, string $key = ''): void
    {
        if (!empty($key)) {
            $this->allowedList[$key] = $value;

        } elseif (!in_array($value, $this->allowedList)) {
            array_push($this->allowedList, $value);
        }
    }

    /**
     * Get items from the whitelist pool.
     *
     * @return array
     */
    public function getAllowedItems(): array
    {
        return $this->allowedList;
    }

    /**
     * Get an item from the whitelist pool.
     *
     * @return string|array
     */
    public function getAllowedItem(string $key)
    {
        return $this->allowedList[$key] ?? '';
    }

    /**
     * Return the allowed item if exists.
     *
     * @param string $key The key of the data.
     *
     * @return string
     */
    public function removeAllowedItem(string $key): void
    {
        unset($this->allowedList[$key]);
    }

    /**
     * Remove all items.
     *
     * @return void
     */
    public function removeAllowedItems(): void
    {
        $this->allowedList = [];
    }

    /**
     * Check if a allowed item exists.
     *
     * @param string $key The key of the data.
     *
     * @return bool
     */
    function hasAllowedItem(string $key): bool
    {
        return isset($this->allowedList[$key]);
    }

    /**
     * Check if a allowed item exists have the same prefix.
     *
     * @param string $key The key of the data.
     *
     * @return array
     */
    function getAllowedItemsWithPrefix(string $key): array
    {
        $temp = [];
        foreach ($this->allowedList as $keyName => $value) {
            if (strpos($keyName, $key) === 0) {
                $temp[$keyName] = $value;
            }
        }
        return $temp;
    }

    /**
     * Remove allowed items with the same prefix.
     *
     * @param string $key The key of the data.
     *
     * @return void
     */
    public function removeAllowedItemsWithPrefix(string $key): void
    {
        foreach (array_keys($this->allowedList) as $keyName) {
            if (strpos($keyName, $key) === 0) {
                unset($this->allowedList[$keyName]);
            }
        }
    }

    /**
     * Is allowed?
     * This method should adjust in extended class if need.
     *
     * @return bool
     */
    public function isAllowed(): bool
    {
        return false;
    }
}
