<?php declare(strict_types=1);
/*
 * This file is part of the Shieldon package.
 *
 * (c) Terry L. <contact@terryl.in>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Shieldon;

/*
 * A simple data wrapper.
 *
 * @since 1.1.0
 */
class Collection
{
    /**
     * @var array
     */
    protected $data;

    /**
     * Constructor.
     * 
     * @param array $data Initialized data.
     *
     * @return void
     */
    public function __construct(array &$data = [])
    {
        $this->data =& $data;
    }

    /**
     * Get specific value from collection by key.
     *
     * @param string $key
     *
     * @return mixed
     */
    public function get(string $key)
    {
        return $this->data[$key] ?? '';
    }

    /**
     * To store data in the collection.
     *
     * @param string $key
     *
     * @return void
     */
    public function set(string $key, $value)
    {
        $this->data[$key] = $value;
    }

    /**
     * To delete data from the collection.
     *
     * @param string $key
     *
     * @return void
     */
    public function remove(string $key)
    {
        if (isset($this->data[$key])) {
            unset($this->data[$key]);
        }
    }

    /**
     * To determine if an item is present in the collection.
     *
     * @param string $key
     *
     * @return bool
     */
    public function has($key)
    {
        return isset($this->data[$key]);
    }

    /**
     * Clear all data in the collection array.
     *
     * @return void
     */
    public function clear()
    {
        $this->data = [];
    }
}
