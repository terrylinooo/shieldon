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

namespace Shieldon\Firewall;

use function count;
use function date;
use function explode;
use function file_exists;
use function file_put_contents;
use function is_dir;
use function json_encode;
use function mkdir;
use function umask;
use const JSON_PRETTY_PRINT;

/*
 * FirewallTrait
 */
trait FirewallTrait
{
    /**
     *   Public methods       | Desctiotion
     *  ----------------------|---------------------------------------------
     *   getKernel            | Get the Shieldon Kernel instance.
     *   getConfiguration     | Get the configuration data.
     *   getDirectory         | Get the dictionary where the data is stored.
     *   getFileName          | Get the path of the configuration file.
     *   getConfig            | Get the value by identification string.
     *   setConfig            | Set the value by identification string.
     *  ----------------------|---------------------------------------------
     */

    /**
     * Shieldon instance.
     *
     * @var object
     */
    protected $kernel;

    /**
     * Configuration data of Shieldon.
     *
     * @var array
     */
    protected $configuration = [];

    /**
     * The configuation file's path.
     *
     * @var string
     */
    protected $directory = '/tmp';

    /**
     * The filename of the configuration file.
     *
     * @var string
     */
    protected $filename = 'config.firewall.json';

    /**
     * A file that confirms the required dictories or database tables
     * have been created.
     *
     * @var string
     */
    protected $checkpoint = 'driver_initialized.txt';
    
    /**
     * The prefix of the database tables, or the name of file directory.
     *
     * @var string
     */
    protected $channel = '';

    /**
     * Version number.
     *
     * @var string
     */
    protected $version = '2.0.1';

    /**
     * Get the Shieldon instance.
     *
     * @return object
     */
    public function getKernel()
    {
        return $this->kernel;
    }

    /**
     * Get the configuation settings.
     *
     * @return array
     */
    public function getConfiguration(): array
    {
        return $this->configuration;
    }

    /**
     * Get the directory where the data stores.
     *
     * @return string
     */
    public function getDirectory(): string
    {
        return $this->directory;
    }

    /**
     * Get the filename where the configuration saves.
     *
     * @return string
     */
    public function getFileName(): string
    {
        return $this->filename;
    }

    /**
     * Get a variable from configuration.
     *
     * @param string $field The field of the configuration.
     *
     * @return mixed
     */
    public function getConfig(string $field)
    {
        $v = explode('.', $field);
        $c = count($v);

        switch ($c) {
            case 1:
                return $this->configuration[$v[0]] ?? '';

            case 2:
                return $this->configuration[$v[0]][$v[1]] ?? '';

            case 3:
                return $this->configuration[$v[0]][$v[1]][$v[2]] ?? '';

            case 4:
                return $this->configuration[$v[0]][$v[1]][$v[2]][$v[3]] ?? '';

            case 5:
                return $this->configuration[$v[0]][$v[1]][$v[2]][$v[3]][$v[4]] ?? '';
        }
        return '';
    }

    /**
     * Set a variable to the configuration.
     *
     * @param string $field The field of the configuration.
     * @param mixed  $value The vale of a field in the configuration.
     *
     * @return void
     */
    public function setConfig(string $field, $value): void
    {
        $v = explode('.', $field);
        $c = count($v);

        switch ($c) {
            case 1:
                $this->configuration[$v[0]] = $value;
                break;

            case 2:
                $this->configuration[$v[0]][$v[1]] = $value;
                break;

            case 3:
                $this->configuration[$v[0]][$v[1]][$v[2]] = $value;
                break;

            case 4:
                $this->configuration[$v[0]][$v[1]][$v[2]][$v[3]] = $value;
                break;

            case 5:
                $this->configuration[$v[0]][$v[1]][$v[2]][$v[3]][$v[4]] = $value;
                break;
        }
    }

    /**
     * Get options from the configuration file.
     * This method is same as `$this->getConfig()` but returning value from array directly.
     *
     * @param string $option  The option of the section in the the configuration.
     * @param string $section The section in the configuration.
     *
     * @return mixed
     */
    protected function getOption(string $option, string $section = '')
    {
        if (!empty($this->configuration[$section][$option])) {
            return $this->configuration[$section][$option];
        }

        if (!empty($this->configuration[$option]) && $section === '') {
            return $this->configuration[$option];
        }

        return false;
    }

    /**
     * Update configuration file.
     *
     * @return void
     */
    protected function updateConfig(): void
    {
        $configFilePath = $this->directory . '/' . $this->filename;

        if (!file_exists($configFilePath)) {
            if (!is_dir($this->directory)) {
                // @codeCoverageIgnoreStart

                $originalUmask = umask(0);
                mkdir($this->directory, 0777, true);
                umask($originalUmask);

                // @codeCoverageIgnoreEnd
            }
        }

        file_put_contents(
            $configFilePath,
            json_encode($this->configuration, JSON_PRETTY_PRINT)
        );
    }

    /**
     * Get the filename of the checkpoint file.
     *
     * @return string
     */
    protected function getCheckpoint(): string
    {
        $driverType = (string) $this->getOption('driver_type');

        $channel = '';

        if (!empty($this->channel)) {
            $channel = '_' . $this->channel;
        }

        return $this->directory . '/' . $this->version . $channel .  '_' . $driverType . '_' . $this->checkpoint;
    }

    /**
     * Are database tables created?
     *
     * @return bool
     */
    protected function hasCheckpoint(): bool
    {
        if (file_exists($this->getCheckpoint())) {
            return true;
        }

        return false;
    }

    /**
     * Are database tables created?
     *
     * @param bool $create Is create the checkpoint file, or not.
     *
     * @return void
     */
    protected function setCheckpoint(bool $create = true): void
    {
        if ($create) {
            file_put_contents($this->getCheckpoint(), date('Y-m-d H:i:s'));
        }
    }
}
