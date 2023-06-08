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

use function is_array;
use function is_bool;
use function json_decode;

/**
 * SQL Driver Trait
 */
trait SqlDriverTrait
{
    /**
     * Fetch data from filter table.
     *
     * @param string $ip An IP address.
     *
     * @return array
     */
    protected function doFetchFromFilterTable(string $ip): array
    {
        $results = [];

        $sql = 'SELECT log_ip, log_data FROM ' . $this->tableFilterLogs . '
            WHERE log_ip = :log_ip
            LIMIT 1';

        $query = $this->db->prepare($sql);

        $this->assertPrepare($query);

        $query->bindValue(':log_ip', $ip, $this->db::PARAM_STR);
        $query->execute();
        $resultData = $query->fetch($this->db::FETCH_ASSOC);

        // No data found.
        if (is_bool($resultData) && !$resultData) {
            $resultData = [];
        }

        if (!empty($resultData['log_data'])) {
            $results = json_decode($resultData['log_data'], true);
        }

        return $results;
    }

    /**
     * Fetch data from rule table.
     *
     * @param string $ip An IP address.
     *
     * @return array
     */
    protected function doFetchFromRuleTable(string $ip): array
    {
        $results = [];

        $sql = 'SELECT * FROM ' . $this->tableRuleList . '
            WHERE log_ip = :log_ip
            LIMIT 1';

        $query = $this->db->prepare($sql);

        $this->assertPrepare($query);
        
        $query->bindValue(':log_ip', $ip, $this->db::PARAM_STR);
        $query->execute();
        $resultData = $query->fetch($this->db::FETCH_ASSOC);

        // No data found.
        if (is_bool($resultData) && !$resultData) {
            $resultData = [];
        }

        if (is_array($resultData)) {
            $results = $resultData;
        }

        return $results;
    }

    /**
     * Fetch data from session table.
     *
     * @param string $id A session ID.
     *
     * @return array
     */
    protected function doFetchFromSessionTable(string $id): array
    {
        $results = [];

        $sql = 'SELECT * FROM ' . $this->tableSessions . '
            WHERE id = :id
            LIMIT 1';

      
        $query = $this->db->prepare($sql);

        $this->assertPrepare($query);

        $query->bindValue(':id', $id, $this->db::PARAM_STR);
        $query->execute();
        $resultData = $query->fetch($this->db::FETCH_ASSOC);

        // No data found.
        if (is_bool($resultData) && !$resultData) {
            $resultData = [];
        }

        if (is_array($resultData)) {
            $results = $resultData;
        }

        return $results;
    }

    /**
     * Fetch all data from filter table.
     *
     * @return array
     */
    protected function doFetchAllFromFilterTable(): array
    {
        $results = [];

        $sql = 'SELECT log_ip, log_data FROM ' . $this->tableFilterLogs;

        $query = $this->db->prepare($sql);

        $this->assertPrepare($query);

        $query->execute();
        $resultData = $query->fetchAll($this->db::FETCH_ASSOC);

        if (is_array($resultData)) {
            $results = $resultData;
        }

        return $results;
    }

    /**
     * Fetch all data from filter table.
     *
     * @return array
     */
    protected function doFetchAllFromRuleTable(): array
    {
        $results = [];

        $sql = 'SELECT * FROM ' . $this->tableRuleList;

        $query = $this->db->prepare($sql);

        $this->assertPrepare($query);

        $query->execute();
        $resultData = $query->fetchAll($this->db::FETCH_ASSOC);

        if (is_array($resultData)) {
            $results = $resultData;
        }

        return $results;
    }

    /**
     * Fetch all data from session table.
     *
     * @return array
     */
    protected function doFetchAllFromSessionTable(): array
    {
        $results = [];

        $sql = 'SELECT * FROM ' . $this->tableSessions . ' ORDER BY microtimestamp ASC';

        $query = $this->db->prepare($sql);

        $this->assertPrepare($query);

        $query->execute();
        $resultData = $query->fetchAll($this->db::FETCH_ASSOC);

        if (is_array($resultData)) {
            $results = $resultData;
        }

        return $results;
    }

    /**
     * Check the prepare statement status.
     *
     * @param object|bool $status Return false if failed.
     *
     * @return void
     */
    protected function assertPrepare($status): void
    {
        if (!$status) {
            throw new RuntimeException(
                json_encode($this->db->errorInfo())
            );
        }
    }
}
