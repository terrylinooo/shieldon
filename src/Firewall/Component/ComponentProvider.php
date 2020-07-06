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

namespace Shieldon\Firewall\Component;

use Shieldon\Firewall\Component\ComponentInterface;

use function array_keys;
use function array_push;
use function in_array;
use function strpos;

/**
 * ComponentPrivider
 */
abstract class ComponentProvider implements ComponentInterface
{
    /**
     * It is really strict.
     *
     * @var bool
     */
    protected $strictMode = false;

    /**
     * Data pool for Blacklist.
     *
     * @var array
     */
    protected $deniedList = [];

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
     * Set denied item list. 
     *
     * @param array $itemList An array contains string items.
     *
     * @return void
     */
    public function setDeniedItems(array $itemList): void
    {
        $this->deniedList = $itemList;
    }

    /**
     * Set denied item.
     *
     * @param string|array $value The value of a item.
     * @param string       $key   The key of a item.
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
     * Return the denied item if exists.
     *
     * @return string
     */
    public function getDeniedItem(string $key): string
    {
        return $this->deniedList[$key] ?? '';
    }

    /**
     * Return current denied list.
     *
     * @return array
     */
    public function getDeniedItems(): array
    {
        return $this->deniedList;
    }

    /**
     * Return the denied item if exists.
     *
     * @return string
     */
    public function removeDeniedItem(string $key): void
    {
        unset($this->deniedList[$key]);
    }

    /**
     * Remove all items.
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
     * @param string $key
     *
     * @return bool
     */
    function hasDeniedItem(string $key): bool
    {
        return isset($this->deniedList[$key]);
    }

    /**
     * Check if a denied item exists have the same prefix.
     *
     * @param string $key
     *
     * @return bool
     */
    function getDeniedItemsWithPrefix(string $key): array
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
     * @param string $key
     *
     * @return void
     */
    function removeDeniedItemsWithPrefix(string $key): void
    {
        foreach (array_keys($this->deniedList) as $keyName) {
            if (strpos($keyName, $key) === 0) {
                unset($this->deniedList[$keyName]);
            }
        }
    }

    /**
     * Is denied?
     *
     * @return bool
     */
    abstract function isDenied(): bool;

    /**
     * Unique deny status code.
     *
     * @return int
     */
    abstract function getDenyStatusCode(): int;
}