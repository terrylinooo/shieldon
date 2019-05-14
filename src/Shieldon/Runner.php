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

class Runner
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