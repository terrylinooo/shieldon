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
 * Denied trait.
 */
trait DeniedTrait
{
    /**
     *   Public methods       | Desctiotion
     *  ----------------------|---------------------------------------------
     *   setDeniedItems       | Add items to the blacklist pool.
     *   setDeniedItem        | Add an item to the blacklist pool.
     *   getDeniedItems       | Get items from the blacklist pool.
     *   getDeniedItem        | Get an item from the blacklist pool.
     *   removeDeniedItem     | Remove a denied item if exists.
     *   removeDeniedItems    | Remove all denied items.
     *   hasDeniedItem        | Check if a denied item exists.
     *   getDenyWithPrefix    | Check if a denied items exist with the same prefix.
     *   removeDenyWithPrefix | Remove denied items with the same prefix.
     *   isDenied             | Check if an item is denied?
     *  ----------------------|---------------------------------------------
     */

    /**
     * Data pool for hard blacklist.
     *
     * @var array
     */
    protected $deniedList = [];

    /**
     * Add items to the blacklist pool.
     *
     * @param array $itemList String list.
     *
     * @return void
     */
    public function setDeniedItems(array $itemList): void
    {
        $this->deniedList = $itemList;
    }

    /**
     * Add an item to the blacklist pool.
     *
     * @param string|array $value The value of the data.
     * @param string       $key   The key of the data.
     *
     * @return void
     */
    public function setDeniedItem($value, string $key = ''): void
    {
        if (!empty($key)) {
            $this->deniedList[$key] = $value;
        } elseif (!in_array($value, $this->deniedList)) {
            array_push($this->deniedList, $value);
        }
    }

    /**
     * Get items from the blacklist pool.
     *
     * @return array
     */
    public function getDeniedItems(): array
    {
        return $this->deniedList;
    }

    /**
     * Get an item from the blacklist pool.
     *
     * @param string $key The key of the data field.
     *
     * @return string|array
     */
    public function getDeniedItem(string $key)
    {
        return $this->deniedList[$key] ?? '';
    }

    /**
     * Remove a denied item if exists.
     *
     * @param string $key The key of the data.
     *
     * @return string
     */
    public function removeDeniedItem(string $key): void
    {
        unset($this->deniedList[$key]);
    }

    /**
     * Remove all denied items.
     *
     * @return void
     */
    public function removeDeniedItems(): void
    {
        $this->deniedList = [];
    }

    /**
     * Check if a denied item exists.
     *
     * @param string $key The key of the data.
     *
     * @return bool
     */
    public function hasDeniedItem(string $key): bool
    {
        return isset($this->deniedList[$key]);
    }

    /**
     * Check if a denied items exist with the same prefix.
     *
     * @param string $key The key of the data.
     *
     * @return array
     */
    public function getDenyWithPrefix(string $key): array
    {
        $temp = [];
        foreach ($this->deniedList as $keyName => $value) {
            if (strpos($keyName, $key) === 0) {
                $temp[$keyName] = $value;
            }
        }
        return $temp;
    }

    /**
     * Remove denied items with the same prefix.
     *
     * @param string $key The key of the data.
     *
     * @return void
     */
    public function removeDenyWithPrefix(string $key): void
    {
        foreach (array_keys($this->deniedList) as $keyName) {
            if (strpos($keyName, $key) === 0) {
                unset($this->deniedList[$keyName]);
            }
        }
    }

    // @codeCoverageIgnoreStart

    /**
     * Is denied?
     * This method should adjust in extended class if need.
     *
     * @return bool
     */
    public function isDenied(): bool
    {
        return false;
    }

    // @codeCoverageIgnoreEnd
}
