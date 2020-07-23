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

namespace Shieldon\Firewall\Utils;

/*
 * A simple data wrapper giving ability to control.
 *
 * @since 1.1.0
 */
class Collection
{
    /**
     * The data collection.
     *
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
     * @param string $key The key of a data field.
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
     * @param string $key   The key of a data field.
     * @param mixed  $value The value of a data field.
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
     * @param string $key The key of a data field.
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
     * @param string $key The key of a data field.
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
