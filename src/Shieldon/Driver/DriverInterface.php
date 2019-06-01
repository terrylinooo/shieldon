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

interface DriverInterface
{
    /**
     * Get an entry from the driver provider.
     *
     * @param string $ip   The data id of the entry to fetch.
     * @param string $type The data type.
     *
     * @return array The data or an empty array.
     */
    public function get(string $ip, string $type = 'log'): array;

    /**
     * Get all entries from the driver provider.
     *
     * @param string $ip   The data id of the entry to fetch.
     * @param string $type The data type.
     *
     * @return array The data or an empty array.
     */
    public function getAll(string $type = 'log'): array;

    /**
     * Tests if an entry exists in the data.
     *
     * @param string $ip The data id of the entry to check for.
     * @param string $type The data type.
     *
     * @return bool
     */
    public function has(string $ip, string $type = 'log'): bool;

    /**
     * Save data or replace old data to the new.
     *
     * @param string $ip     The data id.
     * @param array  $data   The data.
     * @param string $type The data type.
     *
     * @param int    $expire The data will be deleted after expiring.
     *
     * @return bool
     */
    public function save(string $ip, array $data, string $type = 'log', int $expire = 0): bool;

    /**
     * Delete a data entry.
     *
     * @param string $ip The data id.
     * @param string $type The data type.
     *
     * @return bool true if the data entry is deleted successfully.
     *              deleting a non-existing entry is considered successful.
     *              return false overwise.
     */
    public function delete(string $ip, string $type = 'log'): bool;


    /**
     * Rebuild data table.
     *
     * @return bool
     */
    public function rebuild(): bool;
}


