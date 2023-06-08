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

/**
 * Interface for Data drivers.
 */
interface DriverInterface
{
    /**
     * Get an entry from the driver provider.
     *
     * @param string $ip   The data id of the entry to fetch.
     * @param string $type The type of data table. accepts: filter | session | rule
     *
     * @return array The data or an empty array.
     */
    public function get(string $ip, string $type = 'filter'): array;

    /**
     * Get all entries from the driver provider.
     *
     * @param string $type The type of data table. accepts: filter | session | rule
     *
     * @return array The data or an empty array.
     */
    public function getAll(string $type = 'filter'): array;

    /**
     * Tests if an entry exists in the data.
     *
     * @param string $ip   The IP address as well as the data id.
     * @param string $type The type of data table. accepts: filter | session | rule
     *
     * @return bool
     */
    public function has(string $ip, string $type = 'filter'): bool;

    /**
     * Save data or replace old data to the new.
     *
     * @param string $ip     The IP address as well as the data id.
     * @param array  $data   The data.
     * @param string $type   The type of data table. accepts: filter | session | rule
     * @param int    $expire The data will be deleted after expiring.
     *
     * @return bool
     */
    public function save(string $ip, array $data, string $type = 'filter', int $expire = 0): bool;

    /**
     * Delete a data entry.
     *
     * @param string $ip   The IP address as well as the data id.
     * @param string $type The type of data table. accepts: filter | session | rule
     *
     * @return bool true if the data entry is deleted successfully.
     *              deleting a non-existing entry is considered successful.
     *              return false overwise.
     */
    public function delete(string $ip, string $type = 'filter'): bool;

    /**
     * Rebuild data table.
     *
     * @return bool
     */
    public function rebuild(): bool;

    /**
     * Initial data tables.
     *
     * @param bool $dbCheck This is for creating data tables automatically
     *
     * @return void
     */
    public function init(bool $dbCheck = true): void;
}
