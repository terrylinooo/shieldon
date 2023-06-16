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

namespace Shieldon\Firewall\Driver;

use RuntimeException;
use function is_dir;
use function mkdir;
use function str_replace;
use function umask;

/**
 * SQL Driver Trait
 */
trait FileDriverTrait
{
    /**
     * Create a directory for storing data files.
     *
     * @return bool
     */
    protected function createDirectory(): bool
    {
        $conA = $conB = $conC = false;

        $originalUmask = umask(0);

        if (!is_dir($this->getDirectory('filter'))) {
            $conA = mkdir($this->getDirectory('filter'), 0777, true);
        }

        if (!is_dir($this->getDirectory('rule'))) {
            $conB = mkdir($this->getDirectory('rule'), 0777, true);
        }

        if (!is_dir($this->getDirectory('session'))) {
            $conC = mkdir($this->getDirectory('session'), 0777, true);
        }

        umask($originalUmask);

        if ($conA && $conB && $conC) {
            return true;
        }

        return false;
    }

    /**
     * Check the directory if is writable.
     *
     * Not real use in Kernel. only use it in unit tests.
     *
     * @return bool
     */
    protected function checkDirectory(): bool
    {
        if (!is_dir($this->directory) ||
            !is_writable($this->directory)
        ) {
            throw new RuntimeException(
                'The directory defined by File Driver must be writable. (' . $this->directory . ')'
            );
        }

        return true;
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

        $path['filter']  = $this->directory . '/' . $this->tableFilterLogs . '/' . $ip . '.' . $this->extension;
        $path['session'] = $this->directory . '/' . $this->tableSessions   . '/' . $ip . '.' . $this->extension;
        $path['rule']    = $this->directory . '/' . $this->tableRuleList   . '/' . $ip . '.' . $this->extension;

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

        $path['filter']  = $this->directory . '/' . $this->tableFilterLogs;
        $path['session'] = $this->directory . '/' . $this->tableSessions;
        $path['rule']    = $this->directory . '/' . $this->tableRuleList;

        return $path[$type] ?? '';
    }
}
