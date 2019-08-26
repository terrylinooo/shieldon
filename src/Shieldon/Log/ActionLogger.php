<?php declare(strict_types=1);
/*
 * This file is part of the Shieldon package.
 *
 * (c) Terry L. <contact@terryl.in>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Shieldon\Log;

use DateInterval;
use DatePeriod;
use DateTime;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use RuntimeException;

use function date;
use function explode;
use function file_exists;
use function file_get_contents;
use function file_put_contents;
use function is_dir;
use function is_writable;
use function mkdir;
use function rmdir;
use function strtotime;
use function umask;
use function unlink;

/**
 * Action Logger only support storing log into files, I don't want to make it complex, that's it.
 */
final class ActionLogger
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
    protected $filePath = '';

    /**
     * Constructor.
     *
     * @param string $directory
     * @param string $Ymd
     */
    public function __construct(string $directory = '', $Ymd = '')
    {
        if ('' !== $directory) {
            $this->directory = $directory;
        }

        $this->checkDirectory();

        if ('' === $Ymd) {
            $Ymd = date('Ymd', time());
        }

        $this->file = $Ymd . '.' . $this->extension;
        $this->filePath = $this->directory . '/' . $this->file;
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
        if (! empty($record['session_id'])) {
            $record['session_id'] = substr($record['session_id'], 0, 4);
        }

        $data[0] = $record['ip']          ?? 'null';
        $data[1] = $record['session_id']  ?? 'null';
        $data[2] = $record['action_code'] ?? 'null';
        $data[3] = $record['timesamp']    ?? 'null';

        file_put_contents($this->filePath, implode(',', $data) . "\n", FILE_APPEND | LOCK_EX);
    }

    /**
     * Get data from log file.
     *
     * @param string $fromYmd The string in Ymd Date format.
     * @param string $toYmd   The end date.
     * @return array
     */
    public function get(string $fromYmd = '', string $toYmd = ''): array
    {
        $results = [];

        // if $fromYmd is set, overwrite the default one (today).
        if ('' !== $fromYmd) {
            $fromYmd = date('Ymd', strtotime($fromYmd));

            $this->file = $fromYmd . '.' . $this->extension;
            $this->filePath = $this->directory . '/' . $this->file;
        }

        if ('' === $toYmd) {

            if (file_exists($this->filePath)) {

                $logFile = file_get_contents($this->filePath);
                $logs = explode("\n", $logFile);
        
                foreach ($logs as $l) {
                    $data = explode(',', $l);
        
                    if (! empty($data[0])) {
                        $results[] = [
                            'ip'          => $data[0],
                            'session_id'  => $data[1],
                            'action_code' => $data[2],
                            'timesamp'    => $data[3],
                        ];
                    }
                }
            }

        } elseif ('' !== $fromYmd && '' !== $toYmd) {

            // for quering date range.
            $toYmd = date('Ymd', strtotime($toYmd));

            $begin = new DateTime($fromYmd);
            $end = new DateTime($toYmd);
            $end = $end->modify('+1 day'); 
            
            $interval = new DateInterval('P1D');
            $daterange = new DatePeriod($begin, $interval, $end);
            
            $logFile = '';
    
            foreach ($daterange as $date) {
                
                $thisDayLogFile = $this->directory . '/' . $date->format('Ymd') . '.' . $this->extension;

                if (file_exists($thisDayLogFile)) {
                    $logFile .= file_get_contents($thisDayLogFile);
                }
            }

            $logs = explode("\n", $logFile);

            foreach ($logs as $l) {
                $data = explode(',', $l);
    
                if (! empty($data[0])) {
                    $results[] = [
                        'ip'          => $data[0],
                        'session_id'  => $data[1],
                        'action_code' => $data[2],
                        'timesamp'    => $data[3],
                    ];
                }
            }
        }

        return $results;
    }

    /**
     * Create a directory for storing data files.
     *
     * @return bool
     */
    protected function checkDirectory(): bool
    {
        $result = true;

        if (! is_dir($this->directory)) {
            $originalUmask = umask(0);
            $result = @mkdir($this->directory, 0777, true);
            umask($originalUmask);
        }

        // @codeCoverageIgnoreStart
        if (! is_writable($this->directory)) {
            throw new RuntimeException('The directory usded by ActionLogger must be writable. (' . $this->directory . ')');
        }
        // @codeCoverageIgnoreEnd
    
        return $result;
    }


    /**
     * Return current log's directory.
     *
     * @return string
     */
    public function getDirectory(): string
    {
        return $this->directory;
    }

    /**
     * Purge all logs and remove log directory.
     *
     * @return void
     */
    public function purgeLogs(): void
    {
        // Remove them recursively.
        
        if (file_exists($this->directory)) {
            $it = new RecursiveDirectoryIterator($this->directory, RecursiveDirectoryIterator::SKIP_DOTS);
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

            if (is_dir($this->directory)) {
                rmdir($this->directory);
            }
        }
    }

    /**
     * Get current logger info.
     *
     * @return array
     */
    public function getCurrentLoggerInfo(): array
    {
        $data = [];

        if (file_exists($this->directory)) {
            $it = new RecursiveDirectoryIterator($this->directory, RecursiveDirectoryIterator::SKIP_DOTS);
            $files = new RecursiveIteratorIterator($it, RecursiveIteratorIterator::CHILD_FIRST);

            foreach($files as $file) {
                if ($file->isFile()) {
                    $key = $file->getBasename('.log');
                    $size = $file->getSize();

                    // Data: datetime => file size.
                    $data[$key] = $size;
                } 
            }
        }

        return $data;
    }
}
