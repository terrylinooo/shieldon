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

namespace Shieldon\Firewall\Kernel;

use Shieldon\Firewall\Driver\DriverProvider;
use Shieldon\Event\Event;
use RuntimeException;
use function php_sapi_name;

/*
 * Messenger Trait is loaded in Kernel instance only.
 */
trait DriverTrait
{
    /**
     *   Public methods       | Desctiotion
     *  ----------------------|---------------------------------------------
     *   setDriver            | Set a data driver.
     *   setChannel           | Set a data channel.
     *   disableDbBuilder     | disable creating data tables.
     *  ----------------------|---------------------------------------------
     */

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
    protected $isCreateDatabase = true;

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

        /**
         * [Hook] `set_channel` - After initializing data driver.
         */
        Event::doDispatch('set_driver', [
            'driver' => $this->driver,
        ]);

        $this->driver->init($this->isCreateDatabase);

        $period = $this->sessionLimit['period'] ?: 300;

        /**
         * [Hook] `set_driver` - After initializing data driver.
         */
        Event::doDispatch('set_session_driver', [
            'driver'         => $this->driver,
            'gc_expires'     => $period,
            'gc_probability' => 1,
            'gc_divisor'     => 100,
            'psr7'           => $this->psr7,
        ]);
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
    public function setChannel(string $channel): void
    {
        if (!is_null($this->driver)) {
            $this->driver->setChannel($channel);
        } else {
            Event::AddListener(
                'set_driver',
                function ($args) use ($channel) {
                    $args['driver']->setChannel($channel);
                }
            );
        }
    }

    /**
     * Shieldon creating data tables automatically.
     * Turning it off when the data tables exist overwise checling
     * every pageview.
     *
     * @return void
     */
    public function disableDbBuilder(): void
    {
        $this->isCreateDatabase = false;

        if (php_sapi_name() === 'cli') {
            // Unit testing needs.
            $this->isCreateDatabase = true;
        }
    }

    /**
     * Check the data driver, throw an exception if not set.
     *
     * @return void
     */
    protected function assertDriver(): void
    {
        if (!isset($this->driver)) {
            throw new RuntimeException(
                'Data driver must be set.'
            );
        }
    }
}
