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

namespace Shieldon\Firewall\Kernel;

use Shieldon\Firewall\Driver\DriverProvider;
use LogicException;

/*
 * Messenger Trait is loaded in Kernel instance only.
 */
trait DriverTrait
{
    /**
     * Driver for storing data.
     *
     * @var \Shieldon\Firewall\Driver\DriverProvider
     */
    public $driver;

    /**
     * This is for creating data tables automatically
     * Turn it off, if you don't want to check data tables every connection.
     *
     * @var bool
     */
    protected $autoCreateDatabase = true;

    /**
     * Set a data driver.
     *
     * @param DriverProvider $driver Query data from the driver you choose to use.
     *
     * @return void
     */
    public function setDriver(DriverProvider $driver): void
    {
        $this->driver = $driver;
    }

    /**
     * Set a data channel.
     *
     * This will create databases for the channel.
     *
     * @param string $channel Specify a channel.
     *
     * @return void
     */
    public function setChannel(string $channel)
    {
        if (!$this->driver instanceof DriverProvider) {
            throw new LogicException('setChannel method requires setDriver set first.');
        } else {
            $this->driver->setChannel($channel);
        }
    }

    /**
     * For first time installation only. This is for creating data tables automatically.
     * Turning it on will check the data tables exist or not at every single pageview, 
     * it's not good for high traffic websites.
     *
     * @param bool $bool
     * 
     * @return void
     */
    public function createDatabase(bool $bool)
    {
        $this->autoCreateDatabase = $bool;
    }
}
