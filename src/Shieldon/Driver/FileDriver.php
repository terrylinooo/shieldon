<?php declare(strict_types=1);
/*
 * This file is part of the Shieldon package.
 *
 * (c) Terry L. <contact@terryl.in>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Shieldon\Driver;

use InvalidArgumentException;
use function mkdir;
use function is_string;

/**
 * File Driver
 */
class FileDriver extends DriverProvider
{
    /**
     * The directory that data files stored to.
     *
     * @var string
     */
    protected $directory = '';

    /**
     * The filename. Should be IP address.
     *
     * @var string
     */
    protected $filename = '';

    /**
     * The file's extension name'.
     *
     * @var string
     */
    protected $extension = 'json';

    /**
     * Constructor.
     *
     * @param array $config
     */
    public function __construct(array $config)
    {
        $parameters = [
            'directory',
            'filename',
            'extension',
        ];

        foreach($parameters as $parameter) {

            if (! empty($config[$parameter])) {
                $this->{$parameter} = $config[$parameter];
            }
        }

        // Check the directory where data files write into.
        $this->createDirectory();
        $this->checkDirectory();
    }

    /**
     * {@inheritDoc}
     */
    protected function doFetch(string $ip): array
    {

    }

    /**
     * {@inheritDoc}
     */
    protected function checkExist(string $ip): bool
    {

    }

    /**
     * {@inheritDoc}
     */
    protected function doSave(string $ip, array $data, $expire = 0): bool
    {

    }

    /**
     * {@inheritDoc}
     */
    protected function doDelete(string $ip): bool
    {

    }

    /**
     * Create a directory for storing data files.
     *
     * @return bool
     */
    protected function createDirectory(): bool
    {
        $result = false;

        if (! is_dir($this->directory)) {
            $originalUmask = umask(0);
            $result = @mkdir($this->directory, 0777, true);
            umask($originalUmask);
        }

        return $result;
    }

    /**
     * Check the directory if is writable.
     *
     * @return bool
     */
    protected function checkDirectory(): bool
    {
        if (! is_writable($this->directory)) {
            throw new RuntimeException('The directory defined by File Driver must be writable. (' . $this->directory . ')');
        }
    }

    /**
     * Get the filename of the target data file.
     *
     * @return string
     */
    protected function getFilename()
    {
       return $this->filename . '.' . $this->extension;
    }
}

