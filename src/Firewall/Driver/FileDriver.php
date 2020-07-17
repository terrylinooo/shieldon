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

namespace Shieldon\Firewall\Driver;

use Shieldon\Firewall\Driver\DriverProvider;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use RuntimeException;

use function file_exists;
use function file_get_contents;
use function file_put_contents;
use function in_array;
use function is_dir;
use function json_decode;
use function ksort;
use function mkdir;
use function rmdir;
use function str_replace;
use function touch;
use function umask;
use function unlink;

/**
 * File Driver.
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
    protected function doInitialize(bool $dbCheck = true): void
    {
        if (!$this->isInitialized) {
            if (!empty($this->channel)) {
                $this->setChannel($this->channel);
            }

            // Check the directory where data files write into.
            $this->createDirectory();
        }

        $this->isInitialized = true;
    }

    /**
     * {@inheritDoc}
     */
    protected function doFetchAll(string $type = 'filter'): array
    {
        $results = [];

        if (!in_array($type, $this->tableTypes)) {
            return $results;
        }

        $dir = $this->getDirectory($type);

        if (is_dir($dir)) {
            $it = new RecursiveDirectoryIterator($dir, RecursiveDirectoryIterator::SKIP_DOTS);
            $files = new RecursiveIteratorIterator($it, RecursiveIteratorIterator::CHILD_FIRST);

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
        }

        return $results;
    }

    /**
     * {@inheritDoc}
     */
    protected function doFetch(string $ip, string $type = 'filter'): array
    {
        $results = [];

        if (
            !file_exists($this->getFilename($ip, $type)) || 
            !in_array($type, $this->tableTypes)
        ) {
            return $results;
        }

        $fileContent = file_get_contents($this->getFilename($ip, $type));
        $resultData = json_decode($fileContent, true);

        // rule | session
        if (is_array($resultData)) {
            $results = $resultData;
        }

        // filter 
        if (!empty($resultData['log_data'])) {
            return $resultData['log_data']; 
        }

        return $results;
    }

    /**
     * {@inheritDoc}
     */
    protected function checkExist(string $ip, string $type = 'filter'): bool
    {
        if (file_exists($this->getFilename($ip, $type))) {
            return true;
        }

        return false;
    }

    /**
     * {@inheritDoc}
     */
    protected function doSave(string $ip, array $data, string $type = 'filter', $expire = 0): bool
    {
        switch ($type) {

            case 'rule':
                $logData = $data;
                $logData['log_ip'] = $ip;
                break;

            case 'filter':
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
    protected function doDelete(string $ip, string $type = 'filter'): bool
    {
        switch ($type) {
            case 'rule':
                // no break
            case 'filter':
                // no break
            case 'session':
                return $this->remove($this->getFilename($ip, $type));
        }

        return false;
    }

    /**
     * {@inheritDoc}
     */
    protected function doRebuild(): bool
    {
        // Those are Shieldon logs directories.
        $removeDirs = [
            $this->getDirectory('filter'),
            $this->getDirectory('rule'),
            $this->getDirectory('session'),
        ];
        
        // Remove them recursively.
        foreach ($removeDirs as $dir) {
            if (file_exists($dir)) {
                $it = new RecursiveDirectoryIterator($dir, RecursiveDirectoryIterator::SKIP_DOTS);
                $files = new RecursiveIteratorIterator($it, RecursiveIteratorIterator::CHILD_FIRST);
    
                foreach($files as $file) {
                    if ($file->isDir()) {
                        // @codeCoverageIgnoreStart
                        rmdir($file->getRealPath());
                        // @codeCoverageIgnoreEnd
                    } else {
                        unlink($file->getRealPath());
                    }
                }
                unset($it, $files);

                if (is_dir($dir)) {
                    rmdir($dir);
                }
            }
        }

        $checkingFile = $this->directory . '/' . $this->channel . '_' . $this->checkPoint;

        if (file_exists($checkingFile)) {
            unlink($checkingFile);
        }

        $conA = !is_dir($this->getDirectory('filter'));
        $conB = !is_dir($this->getDirectory('rule'));
        $conC = !is_dir($this->getDirectory('session'));

        // Check if are Shieldon directories removed or not.
        $result = ($conA && $conB && $conC);

        $this->createDirectory();

        return $result;
    }

    /**
     * Create a directory for storing data files.
     *
     * @return bool
     */
    protected function createDirectory(): bool
    {
        $conA = $resultB = $resultC = false;

        $checkingFile = $this->directory . '/' . $this->channel . '_' . $this->checkPoint;

        if (!file_exists($checkingFile)) {
            $originalUmask = umask(0);

            if (!is_dir($this->getDirectory('filter'))) {
                $conA = @mkdir($this->getDirectory('filter'), 0777, true);
            }
    
            if (!is_dir($this->getDirectory('rule'))) {
                $conB = @mkdir($this->getDirectory('rule'), 0777, true);
            }
    
            if (!is_dir($this->getDirectory('session'))) {
                $conC = @mkdir($this->getDirectory('session'), 0777, true);
            }

            if (!($conA && $conB && $conC)) {
                return false;
            }

            file_put_contents($checkingFile, ' ');
            umask($originalUmask);
        }

        return true;
    }

    /**
     * Check the directory if is writable.
     *
     * @return bool
     */
    protected function checkDirectory(): bool
    {
        if (!is_dir($this->directory) || !is_writable($this->directory)) {
            throw new RuntimeException(
                'The directory defined by File Driver must be writable. (' . $this->directory . ')'
            );
        }

        return true;
    }

    /**
     * Remove a Shieldon log file.
     * 
     * @param $logFilePath The absolute path of the log file.
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
     * @param string $ip   IP address.
     * @param string $type The table name of the data cycle.
     *
     * @return string
     */
    private function getFilename(string $ip, string $type = 'filter'): string
    {
        $ip = str_replace(':', '-', $ip);
        $path = [];

        $path['filter'] = $this->directory . '/' . $this->tableFilterLogs . '/' . $ip . '.' . $this->extension;
        $path['session'] = $this->directory . '/' . $this->tableSessions   . '/' . $ip . '.' . $this->extension;
        $path['rule'] = $this->directory . '/' . $this->tableRuleList   . '/' . $ip . '.' . $this->extension;

        return $path[$type] ?? '';
    }

    /**
     * Get directory.
     *
     * @param string $type The table name of the data cycle.
     *
     * @return string
     */
    private function getDirectory(string $type = 'filter'): string
    {
        $path = [];

        $path['filter'] = $this->directory . '/' . $this->tableFilterLogs;
        $path['session'] = $this->directory . '/' . $this->tableSessions;
        $path['rule'] = $this->directory . '/' . $this->tableRuleList;

        return $path[$type] ?? '';
    }
}

