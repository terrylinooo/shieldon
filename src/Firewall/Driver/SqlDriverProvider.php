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

use Shieldon\Firewall\Driver\DriverProvider;
use Shieldon\Firewall\Driver\SqlDriverTrait;
use Exception;
use PDO;

use function is_bool;
use function is_null;

/**
 * SQL Driver provider.
 */
class SqlDriverProvider extends DriverProvider
{
    use SqlDriverTrait;

    /**
     * Data engine will be used on.
     *
     * @var string
     */
    protected $tableDbEngine = 'innodb';

    /**
     * PDO instance.
     * 
     * @var object
     */
    protected $db;

    /**
     * Constructor.
     *
     * @param PDO $pdo
     * @param bool $debug
     */
    public function __construct(PDO $pdo, bool $debug = false)
    {
        $this->db = $pdo;

        if ($debug) {
            $this->db->setAttribute($this->db::ATTR_ERRMODE, $this->db::ERRMODE_EXCEPTION);
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

            if ($dbCheck && !$this->checkTableExists()) {
                $this->installSql();
            }
        }

        $this->isInitialized = true;
    }

    /**
     * {@inheritDoc}
     */
    protected function doFetch(string $ip, string $type = 'filter'): array
    {
        $tables = [
            'rule' => 'doFetchFromRuleTable',
            'filter' => 'doFetchFromFilterTable',
            'session' => 'doFetchFromSessionTable',
        ];

        $method = $tables[$type];

        // Fetch from SqlDriverTrait.
        return $this->{$method}($ip);
    }

   /**
     * {@inheritDoc}
     */
    protected function doFetchAll(string $type = 'filter'): array
    {
        $tables = [
            'rule' => 'doFetchAllFromRuleTable',
            'filter' => 'doFetchAllFromFilterTable',
            'session' => 'doFetchAllFromSessionTable',
        ];
 
        $method = $tables[$type];

        // Fetch from SqlDriverTrait.
        return $this->{$method}();
    }

    /**
     * {@inheritDoc}
     */
    protected function checkExist(string $ip, string $type = 'filter'): bool
    {
        $tables = [
            'rule' => [
                'table' => $this->tableRuleList,
                'field' => 'log_ip',
            ],
            'filter' => [
                'table' => $this->tableFilterLogs,
                'field' => 'log_ip',
            ],
            'session' => [
                'table' => $this->tableSessions,
                'field' => 'id',
            ],
        ];

        $tableName = $tables[$type]['table'];
        $field = $tables[$type]['field'];

        $sql = 'SELECT ' . $field . ' FROM ' . $tableName . '
            WHERE ' . $field . ' = :' . $field . '
            LIMIT 1';

        $query = $this->db->prepare($sql);
        $query->bindValue(':' . $field, $ip);

        $query->execute();
        $result = $query->fetch();

        if (!empty($result[$field])) {
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
                $tableName = $this->tableRuleList;
                $logWhere['log_ip'] = $ip;
                $logData = $data;
                $logData['log_ip'] = $ip;
                break;

            case 'filter':
                $tableName = $this->tableFilterLogs;
                $logWhere['log_ip'] = $ip;
                $logData['log_ip'] = $ip;
                $logData['log_data'] = json_encode($data);
                break;

            case 'session':
                $tableName = $this->tableSessions;
                $logWhere['id'] = $data['id'];
                $logData = $data;
                break;
        }

        if ($this->checkExist($ip, $type)) {
            return $this->update($tableName, $logData, $logWhere);
        }

        return (bool) $this->insert($tableName, $logData);
    }

    /**
     * {@inheritDoc}
     */
    protected function doDelete(string $ip, string $type = 'filter'): bool
    {
        $tables = [
            'rule' => [
                'table' => $this->tableRuleList,
                'field' => 'log_ip',
                'value' => $ip,
            ],
            'filter' => [
                'table' => $this->tableFilterLogs,
                'field' => 'log_ip',
                'value' => $ip,
            ],
            'session' => [
                'table' => $this->tableSessions,
                'field' => 'id',
                'value' => $ip,
            ],
        ];

        if (empty($tables[$type])) {
            return false;
        }

        $tableName = $tables[$type]['table'];
        $field = $tables[$type]['field'];
        $value = $tables[$type]['value'];

        return $this->remove($tableName, [$field => $value]);
    }

    /**
     * {@inheritDoc}
     */
    protected function doRebuild(): bool
    {
        return $this->rebuildSql();
    }

    /**
     * Update database table.
     *
     * @param string $table
     * @param array  $data
     * @param array  $where
     *
     * @return bool
     */
    protected function update(string $table, array $data, array $where)
    {
        $placeholder = [];
        foreach ($data as $k => $v) {
            $placeholder[] = "$k = :$k";
        }

        $dataPlaceholder = implode(', ', $placeholder);

        $placeholder = [];
        foreach ($where as $k => $v) {
            $placeholder[] = "$k = :$k";
        }

        $wherePlaceholder = implode(' AND ', $placeholder);

        try {
            $sql = 'UPDATE ' . $table . ' SET ' . $dataPlaceholder . ' WHERE ' . $wherePlaceholder;
            $query = $this->db->prepare($sql);

            $bind = array_merge($data, $where);
    
            foreach ($bind as $k => $v) {

                // @codeCoverageIgnoreStart

                if (is_numeric($v)) {
                    $pdoParam = $this->db::PARAM_INT;

                    // Solve problem with bigint.
                    if ($v >= 2147483647) {
                        $pdoParam = $this->db::PARAM_STR;
                    } 
                } elseif (is_bool($v)) {
                    $pdoParam = $this->db::PARAM_BOOL;
                } elseif (is_null($v)) {
                    $pdoParam = $this->db::PARAM_NULL;
                } else {
                    $pdoParam = $this->db::PARAM_STR;
                }

                // @codeCoverageIgnoreEnd

                $query->bindValue(":$k", $bind[$k], $pdoParam);
            }

            return $query->execute();

        // @codeCoverageIgnoreStart
        
        } catch(Exception $e) {
            return false;
        }

        // @codeCoverageIgnoreEnd 
    }

    /**
     * Insert database table.
     *
     * @param string $table
     * @param array  $data
     *
     * @return bool
     */
    protected function insert(string $table, array $data)
    {
        $placeholderField = [];
        $placeholderValue = [];
        foreach ($data as $k => $v) {
            $placeholderField[] = "`$k`";
            $placeholderValue[] = ":$k";
        }

        $dataPlaceholderField = implode(', ', $placeholderField);
        $dataPlaceholderValue = implode(', ', $placeholderValue);

        try {
            $sql = 'INSERT INTO ' . $table . ' (' . $dataPlaceholderField . ') VALUES (' . $dataPlaceholderValue . ')';
            $query = $this->db->prepare($sql);

            foreach ($data as $k => $v) {

                // @codeCoverageIgnoreStart

                if (is_numeric($v)) {
                    $pdoParam = $this->db::PARAM_INT;

                    // Solve problem with bigint.
                    if ($v >= 2147483647) {
                        $pdoParam = $this->db::PARAM_STR;
                    }
                } elseif (is_bool($v)) {
                    $pdoParam = $this->db::PARAM_BOOL;
                } elseif (is_null($v)) {
                    $pdoParam = $this->db::PARAM_NULL;
                } else {
                    $pdoParam = $this->db::PARAM_STR;
                }

                // @codeCoverageIgnoreEnd

                $query->bindValue(":$k", $data[$k], $pdoParam);
            }

            return $query->execute();

        // @codeCoverageIgnoreStart

        } catch(Exception $e) {
            return false;
        }

        // @codeCoverageIgnoreEnd
    }

    /**
     * Remove a row from a table.
     *
     * @param string $table
     * @param array $where
     *
     * @return bool
     */
    protected function remove(string $table, array $where): bool
    {

        $placeholder = [];
        foreach ($where as $k => $v) {
            $placeholder[] = "`$k` = :$k";
        }

        $dataPlaceholder = implode(' AND ', $placeholder);

        try {

            $sql = 'DELETE FROM ' . $table . ' WHERE ' . $dataPlaceholder;
            $query = $this->db->prepare($sql);

            foreach ($where as $k => $v) {

                // @codeCoverageIgnoreStart

                if (is_numeric($v)) {
                    $pdoParam = $this->db::PARAM_INT;
                } elseif (is_bool($v)) {
                    $pdoParam = $this->db::PARAM_BOOL;
                } elseif (is_null($v)) {
                    $pdoParam = $this->db::PARAM_NULL;
                } else {
                    $pdoParam = $this->db::PARAM_STR;
                }

                // @codeCoverageIgnoreEnd

                $query->bindValue(":$k", $v, $pdoParam);
            }

            return $query->execute();

        // @codeCoverageIgnoreStart

        } catch(Exception $e) {
            return false;
        }

        // @codeCoverageIgnoreEnd
    }

    /**
     * Create SQL tables that Shieldon needs.
     *
     * @return bool
     */
    protected function installSql(): bool
    {
        try {

            $sql = "
                CREATE TABLE IF NOT EXISTS `{$this->tableFilterLogs}` (
                    `log_ip` varchar(46) NOT NULL,
                    `log_data` blob,
                    PRIMARY KEY (`log_ip`)
                ) ENGINE={$this->tableDbEngine} DEFAULT CHARSET=latin1;
            ";

            $this->db->query($sql);

            $sql = "
                CREATE TABLE IF NOT EXISTS `{$this->tableRuleList}` (
                    `log_ip` varchar(46) NOT NULL,
                    `ip_resolve` varchar(255) NOT NULL,
                    `type` tinyint(3) UNSIGNED NOT NULL,
                    `reason` tinyint(3) UNSIGNED NOT NULL,
                    `time` int(10) UNSIGNED NOT NULL,
                    `attempts` int(10) UNSIGNED DEFAULT 0,
                    PRIMARY KEY (`log_ip`)
                ) ENGINE={$this->tableDbEngine} DEFAULT CHARSET=latin1;
            ";

            $this->db->query($sql);

            $sql = "
                CREATE TABLE `{$this->tableSessions}` (
                    `id` varchar(40) NOT NULL,
                    `ip` varchar(46) NOT NULL,
                    `time` int(10) UNSIGNED NOT NULL,
                    `microtimesamp` bigint(20) UNSIGNED NOT NULL,
                    `data` blob,
                    PRIMARY KEY (`id`)
                ) ENGINE={$this->tableDbEngine} DEFAULT CHARSET=latin1;
            ";

            $this->db->query($sql);

            return true;

        // @codeCoverageIgnoreStart

        } catch (Exception $e) {
            return false;
        }

        // @codeCoverageIgnoreEnd
    }

    /**
     * Clean all records in IP log and IP rule tables, and then rebuild new tables.
     *
     * @return bool
     */
    protected function rebuildSql(): bool
    {
        try {

            $sql = "DROP TABLE IF EXISTS `{$this->tableFilterLogs}`";
            $this->db->query($sql);

            $sql = "DROP TABLE IF EXISTS `{$this->tableRuleList}`";
            $this->db->query($sql);

            $sql = "DROP TABLE IF EXISTS `{$this->tableSessions}`";
            $this->db->query($sql);

            $this->installSql();

            return true;

        // @codeCoverageIgnoreStart

        } catch (Exception $e) {
            return false;
        }

        // @codeCoverageIgnoreEnd
    }

    /**
     * Check required tables exist or not.
     *
     * @return bool
     */
    protected function checkTableExists(): bool
    {
        $checkLogTable = $this->db->query("SHOW TABLES LIKE '{$this->tableFilterLogs}'");

        if ($checkLogTable) {
            if ($checkLogTable->rowCount() > 0) {
                return true;
            }
        }

        return false;
    }
}