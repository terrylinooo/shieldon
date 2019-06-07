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

use RuntimeException;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
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
    protected $directory = '/tmp/';


    /**
     * The file's extension name'.
     *
     * @var string
     */
    protected $extension = 'json';


    /**
     * A file that confirms the required dictories have been created.
     *
     * @var string
     */
    private $checkPoint = 'shieldon_check_exist.txt';

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
    }

    /**
     * Initialize data tables.
     *
     * @param bool $dbCheck This is for creating data tables automatically
     *                      Turn it off, if you don't want to check data tables every pageview.
     *
     * @return void
     */
    public function DoInitialize($dbCheck = true): void
    {
        if (! $this->isInitialized) {
            if (! empty($this->channel)) {
                $this->setChannel($this->channel);
            }

            // Check the directory where data files write into.
            if ($this->checkDirectory()) {
                $this->createDirectory();
            }
        }

        $this->isInitialized = true;
    }

    /**
     * {@inheritDoc}
     */
    protected function doFetchAll(string $type = 'log'): array
    {
        switch ($type) {

            case 'rule':
            case 'log':
            case 'session':

                $dir = $this->getDirectory($type);

                $it = new RecursiveDirectoryIterator($dir, RecursiveDirectoryIterator::SKIP_DOTS);
                $files = new RecursiveIteratorIterator($it, RecursiveIteratorIterator::CHILD_FIRST);

                $results = [];

                foreach($files as $file) {
                    if ($file->isFile()) {

                        $content = json_decode(file_get_contents($file->getPath() . '/' . $file->getFilename()), true);

                        if ($type === 'session') {
                            $sort = $content['microtimesamp'] . '.' . $file->getFilename(); 
                        } else {
                            $sort = $file->getMTime() . '.' . $file->getFilename();
                        }
                        $results[$sort] = $content;
                    }
                }
                unset($it, $files);

                // Sort by ascending timesamp (microtimesamp).
                ksort($results);

                return $results;
        }
    }

    /**
     * {@inheritDoc}
     */
    protected function doFetch(string $ip, string $type = 'log'): array
    {
        if (! file_exists($this->getFilename($ip, $type))) {
            return [];
        }

        switch ($type) {

            case 'rule':
            case 'session':
                $fileContent = file_get_contents($this->getFilename($ip, $type));
                $result = json_decode($fileContent, true);

                if (is_array($result)) {
                    return $result;
                }
                break;

            case 'log':
                $fileContent = file_get_contents($this->getFilename($ip, $type));
                $result = json_decode($fileContent, true);

                if (! empty($result['log_data'])) {
                    return $result['log_data']; 
                }
                break;
        }

        return [];
    }

    /**
     * {@inheritDoc}
     */
    protected function checkExist(string $ip, string $type = 'log'): bool
    {
        if (file_exists($this->getFilename($ip, $type))) {
            return true;
        }

        return false;
    }

    /**
     * {@inheritDoc}
     */
    protected function doSave(string $ip, array $data, string $type = 'log', $expire = 0): bool
    {
        switch ($type) {

            case 'rule':
                $logData = $data;
                $logData['log_ip'] = $ip;
                break;

            case 'log':
                $logData['log_ip'] = $ip;
                $logData['log_data'] = $data;
                break;

            case 'session':
                $logData = $data;
                break;
        }

        $result = file_put_contents($this->getFilename($ip, $type), json_encode($logData));

        // Update file time.
        touch($this->getFilename($ip, $type), time());

        return ($result > 0) ? true : false;
    }

    /**
     * {@inheritDoc}
     */
    protected function doDelete(string $ip, string $type = 'log'): bool
    {
        switch ($type) {
            case 'rule':
            case 'log':
            case 'session':
                return $this->remove($this->getFilename($ip, $type));
        }
    }

    /**
     * {@inheritDoc}
     */
    protected function doRebuild(): bool
    {
        // Those are Shieldon logs directories.
        $removeDirs = [
            $this->getDirectory('log'),
            $this->getDirectory('rule'),
            $this->getDirectory('session'),
        ];
        
        // Remove them recursively.
        foreach ($removeDirs as $dir) {
            if (file_exists($dir)) {
                $it = new RecursiveDirectoryIterator($dir, RecursiveDirectoryIterator::SKIP_DOTS);
                $files = new RecursiveIteratorIterator($it, RecursiveIteratorIterator::CHILD_FIRST);
    
                foreach($files as $file) {
                    if ($file->isDir()){
                        rmdir($file->getRealPath());
                    } else {
                        unlink($file->getRealPath());
                    }
                }
                unset($it, $files);
            }
        }

        // Check if are Shieldon directories removed or not.
        if (
            ! is_dir($this->getDirectory('log'))     && 
            ! is_dir($this->getDirectory('rule'))    && 
            ! is_dir($this->getDirectory('session'))
        ) {
            return true;
        }

        return false;
    }

    /**
     * Create a directory for storing data files.
     *
     * @return bool
     */
    protected function createDirectory(): bool
    {
        $result = false;

        $checkingFile = $this->directory . '/' . $this->channel . '_' . $this->checkPoint;

        if (! file_exists($checkingFile)) {
            $originalUmask = umask(0);

            $resultA = $resultB = $resultC = false;

            if (! is_dir($this->getDirectory('log'))) {
                $resultA = @mkdir($this->getDirectory('log'), 0777, true);
            } else {
                $resultA = true;
            }
    
            if (! is_dir($this->getDirectory('rule'))) {
                $resultB = @mkdir($this->getDirectory('rule'), 0777, true);
            } else {
                $resultB = true;
            }
    
            if (! is_dir($this->getDirectory('session'))) {
                $resultC = @mkdir($this->getDirectory('session'), 0777, true);
            } else {
                $resultC = true;
            }

            if ($resultA && $resultB && $resultC) {
                file_put_contents($checkingFile, ' ');
            }
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
        if (is_dir($this->directory) && ! is_writable($this->directory)) {
            throw new RuntimeException('The directory defined by File Driver must be writable. (' . $this->directory . ')');
            return false;
        }

        return true;
    }

    /**
     * Remove a Shieldon log file.
     *
     * @return bool
     */
    private function remove(string $logFilePath): bool
    {
        if (file_exists($logFilePath)) {
            return unlink($logFilePath);
        }
        return false;
    }

    /**
     * Get filename.
     *
     * @param string $ip
     * @param string $type
     *
     * @return string
     */
    private function getFilename(string $ip, string $type = 'log'): string
    {
        switch ($type) {
            case 'log'    : return $this->directory . '/' . $this->tableLogs       . '/' . $ip . '.' . $this->extension;
            case 'session': return $this->directory . '/' . $this->tableSessions   . '/' . $ip . '.' . $this->extension;
            case 'rule'   : return $this->directory . '/' . $this->tableRuleList . '/' . $ip . '.' . $this->extension;
        }
    }

    /**
     * Get directory.
     *
     * @param string $type
     * @return string
     */
    private function getDirectory(string $type = 'log'): string
    {
        switch ($type) {
            case 'log'    : return $this->directory . '/' . $this->tableLogs;
            case 'session': return $this->directory . '/' . $this->tableSessions;
            case 'rule'   : return $this->directory . '/' . $this->tableRuleList;
        }
    }
}

