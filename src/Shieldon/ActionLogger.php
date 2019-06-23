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

use RuntimeException;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use function mkdir;
use function is_string;

/**
 * Action Logger only support storing log into files, I don't want to make it complex, that's it.
 */
class ActionLogger
{
    /**
     * The directory that data files stored to.
     *
     * @var string
     */
    protected $directory = '/tmp/';

    /**
     * The file's extension name'.
     *
     * @var string
     */
    protected $extension = 'log';

    /**
     * The file name.
     *
     * @var string
     */
    protected $file = '';

    /**
     * The file path.
     *
     * @var string
     */
    protected $file_path = '';

    /**
     * Constructor.
     *
     * @param string $directory
     */
    public function __construct(string $directory = '')
    {
        if ('' !== $directory) {
            $this->directory = $directory;
        }

        if (! $this->createDirectory()) {
            $this->checkDirectory();
        }

        $this->file = date('y-m-d') . '.' . $this->extension;
        $this->file_path = $this->directory . '/' . $this->file;
    }

    /**
     * Append data to the file.
     *
     * @param array $record The log data.
     *
     * @return void
     */
    public function add(array $record): void
    {
        /**
         * ip
         * session_id
         * action_code
         * reason_code
         * timesamp
         */
        $data[0] = $record['ip']          ?? 'null';
        $data[1] = $record['session_id']  ?? 'null';
        $data[2] = $record['action_code'] ?? 'null';
        $data[3] = $record['reason_code'] ?? 'null';
        $data[4] = $record['timesamp']    ?? 'null';

        file_put_contents($this->file_path, implode(',', $data), FILE_APPEND | LOCK_EX);
    }

    /**
     * Get data from log file.
     *
     * @return array
     */
    public function get(): array
    {
        $logString = file_get_contents($this->file_path);

        $data = explode(',', $logString);;

        return [
            'ip'          => $data[0],
            'session_id'  => $data[1],
            'action_code' => $data[2],
            'reason_code' => $data[3],
            'timesamp'    => $data[4],
        ];
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
        if (! is_dir($this->directory) || ! is_writable($this->directory)) {
            throw new RuntimeException('The directory usded by ActionLogger must be writable. (' . $this->directory . ')');
        }

        return true;
    }
}

