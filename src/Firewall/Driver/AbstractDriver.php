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

use Shieldon\Firewall\Driver\DriverInterface;

/**
 * Abstract Driver.
 */
abstract class AbstractDriver implements DriverInterface
{
    /**
     * {@inheritDoc}
     *
     * @param string $ip   The data id of the entry to fetch.
     * @param string $type The type of data table. accepts: filter | session | rule
     *
     * @return array
     */
    public function get(string $ip, string $type = 'filter'): array
    {
        return $this->doFetch($ip, $type);
    }

    /**
     * {@inheritDoc}
     *
     * @param string $type The type of data table. accepts: filter | session | rule
     *
     * @return array
     */
    public function getAll(string $type = 'filter'): array
    {
        return $this->doFetchAll($type);
    }

    /**
     * {@inheritDoc}
     *
     * @param string $ip   The IP address as well as the data id.
     * @param string $type The type of data table. accepts: filter | session | rule
     *
     * @return bool
     */
    public function has(string $ip, string $type = 'filter'): bool
    {
        return $this->checkExist($ip, $type);
    }

    /**
     * {@inheritDoc}
     *
     * @param string $ip     The IP address as well as the data id.
     * @param array  $data   The data.
     * @param string $type   The type of data table. accepts: filter | session | rule
     * @param int    $expire The data will be deleted after expiring.
     *
     * @return bool
     */
    public function save(string $ip, array $data, string $type = 'filter', int $expire = 0): bool
    {
        return $this->doSave($ip, $data, $type, $expire);
    }

    /**
     * {@inheritDoc}
     *
     * @param string $ip   The IP address as well as the data id.
     * @param string $type The type of data table. accepts: filter | session | rule
     *
     * @return bool true if the data entry is deleted successfully.
     *              deleting a non-existing entry is considered successful.
     *              return false overwise.
     */
    public function delete(string $ip, string $type = 'filter'): bool
    {
        return $this->doDelete($ip, $type);
    }

    /**
     * {@inheritDoc}
     *
     * @return bool
     */
    public function rebuild(): bool
    {
        return $this->doRebuild();
    }

    /**
     * {@inheritDoc}
     *
     * @param bool $dbCheck This is for creating data tables automatically
     *
     * @return void
     */
    public function init(bool $dbCheck = true): void
    {
        $this->doInitialize($dbCheck);
    }

    /**
     * Implement fetch.
     *
     * @param string $ip   The data id of the entry to fetch.
     * @param string $type The type of the data table.
     *
     * @return array The data or an empty array.
     */
    abstract protected function doFetch(string $ip, string $type = 'filter'): array;

    /**
     * Implement fetch all.
     *
     * @param string $type The data type.
     *
     * @return array The data or an empty array.
     */
    abstract protected function doFetchAll(string $type = 'filter'): array;

    /**
     * Implement has.
     *
     * @param string $ip   The data id of the entry to check for.
     * @param string $type The type of the data table.
     *
     * @return bool
     */
    abstract protected function checkExist(string $ip, string $type = 'filter'): bool;

    /**
     * Implement save.
     *
     * @param string $ip     The data id.
     * @param array  $data   The data.
     * @param string $type   The type of the data table.
     * @param int    $expire The data will be deleted after expiring.
     *
     * @return bool
     */
    abstract protected function doSave(string $ip, array $data, string $type = 'filter', $expire = 0): bool;

    /**
     * Implement delete.
     *
     * @param string $ip   The data id.
     * @param string $type The type of the data table.
     *
     * @return bool
     */
    abstract protected function doDelete(string $ip, string $type = 'filter'): bool;

    /**
     * Rebuild data tables.
     *
     * @return bool
     */
    abstract protected function doRebuild(): bool;

    /**
     * Initial data tables.
     *
     * @param bool $dbCheck This is for creating data tables automatically
     *                      Turn it off, if you don't want to check data tables every pageview.
     *
     * @return void
     */
    abstract protected function doInitialize(bool $dbCheck = true): void;
}
