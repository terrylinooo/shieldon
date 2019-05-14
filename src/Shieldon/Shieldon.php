<?php declare(strict_types=1);

/*
 * @name        Shieldon
 * @author      Terry Lin
 * @link        https://github.com/terrylinooo/shieldon
 * @license     MIT
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 */

namespace Shieldon;

class Shieldon
{
    /**
     * Singleton instance.
     * 
     * @var null
     */
    protected static $instance = null;

    /**
     * Driver for storing data.
     *
     * @var DriverInterface
     */
    protected $driver;

    /**
     * Constructor.
     */
    public function __construct()
    {
        self::getInstance();
    }

    /**
     * Keep only one instance will be initialized.
     *
     * @return void
     */
    public static function getInstance()
    {
        if (! self::$instance) {
            $instance = new Runner();
        }
    }

    /**
     * Set a driver to store data.
     *
     * @param DriverInterface $driver
     * @return self
     */
    public function setDriver(DriverInterface $driver): self
    {
        $this->driver->driver;
    }
}