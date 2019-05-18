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

use PDO;
/**
 * Abstract Mysql Driver.
 */
abstract class AbstractSqlDriver extends DriverProvider
{
    /**
     * Data table for regular session logs.
     *
     * @var string
     */
    protected $tableLogs = 'shieldon_logs';

    /**
     * Data table name for whitelist.
     *
     * @var string
     */
    protected $tableRuleList = 'shieldon_rule_list';

    /**
     * Data table for recording online session count.
     *
     * @var string
     */
    protected $tableSessions = 'shieldon_sessions';

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
    public function init($dbCheck = true): void
    {
        if (! empty($this->channel)) {
            $this->tableLogs = $this->channel . '_' . $this->tableLogs;
            $this->tableRuleList = $this->channel . '_' . $this->tableRuleList;
            $this->tableSessions = $this->channel . '_' . $this->tableSessions;
        }

        if (! $this->checkTableExists()) {
            $this->installSql();
        }
    }

    /**
     * {@inheritDoc}
     */
    protected function doFetch(string $ip, string $type = 'log'): array
    {
        switch ($type) {

            case 'rule':

                $sql = 'SELECT * FROM ' . $this->tableRuleList . '
                    WHERE log_ip = :log_ip
                    LIMIT 1';

                $query = $this->db->prepare($sql);
                $query->bindValue(':log_ip', $ip);

                $query->execute();
                $result = $query->fetch();

                if (is_array($result)) {
                    return $result;
                }
                break;

            case 'log':
            default:

                $sql = 'SELECT log_ip, log_data FROM ' . $this->tableLogs . '
                    WHERE log_ip = :log_ip
                    LIMIT 1';

                $query = $this->db->prepare($sql);
                $query->bindValue(':log_ip', $ip);

                $query->execute();
                $result = $query->fetch();

                if (! empty($result['log_data'])) {
                    return json_decode($result['log_data'], true); 
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
        switch ($type) {

            case 'rule':
                $tableName = $this->tableRuleList;
                break;

            case 'log':
            default:
                $tableName = $this->tableLogs;
                break;
        }

        $sql = 'SELECT log_ip FROM ' . $tableName . '
            WHERE log_ip = :log_ip
            LIMIT 1';

        $query = $this->db->prepare($sql);
        $query->bindValue(':log_ip', $ip);

        $query->execute();
        $result = $query->fetch();

        if (! empty($result['log_ip'])) {
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
                $tableName = $this->tableRuleList;
                $logData = $data;
                break;

            case 'log':
            default:
                $tableName = $this->tableLogs;
                $logData['log_data'] = json_encode($data);
                break;
        }

        $logData['log_ip'] = $ip;

        if ($this->checkExist($ip, $type)) {
            $logWhere['log_ip'] = $ip;
            return $this->update($tableName, $logData, $logWhere);
        } else {
            return (bool) $this->insert($tableName, $logData);
        }
    }

    /**
     * {@inheritDoc}
     */
    protected function doDelete(string $ip, string $type = 'log'): bool
    {
        switch ($type) {

            case 'rule':
                $tableName = $this->tableRuleList;
                break;

            case 'log':
            default:
                $tableName = $this->tableLogs;
                break;
        }
        return $this->db->delete($tableName, $ip);
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
    private function update(string $table, array $data, array $where)
    {
        $placeholder = [];
        foreach($data as $k => $v) {
            $placeholder[] = "$k = :$k";
        }

        $dataPlaceholder = implode(', ', $placeholder);

        $placeholder = [];
        foreach($where as $k => $v) {
            $placeholder[] = "$k = :$k";
        }

        $wherePlaceholder = implode(' AND ', $placeholder);

        try {
            $sql = 'UPDATE ' . $table . ' SET ' . $dataPlaceholder . ' WHERE ' . $wherePlaceholder;
            $query = $this->db->prepare($sql);

            $bind = array_merge($data, $where);
    
            foreach($bind as $k => $v) {
                if (is_numeric($v)) {
                    $pdoParam = $this->db::PARAM_INT;
                } elseif (is_bool($v)) {
                    $pdoParam = $this->db::PARAM_BOOL;
                } elseif (is_null($v)) {
                    $pdoParam = $this->db::PARAM_NULL;
                } else {
                    $pdoParam = $this->db::PARAM_STR;
                }
                $query->bindValue(":$k", $bind[$k], $pdoParam);
            }

            return $query->execute();

        } catch(\Exception $e) {
            
        }

        return false;
    }

    /**
     * Insert database table.
     *
     * @param string $table
     * @param array  $data
     *
     * @return bool
     */
    private function insert(string $table, array $data) {

        $placeholder = [];
        foreach($data as $k => $v) {
            $placeholderField[] = "`$k`";
            $placeholderValue[] = ":$k";
        }

        $dataPlaceholderField = implode(', ', $placeholderField);
        $dataPlaceholderValue = implode(', ', $placeholderValue);

        try {
            $sql = 'INSERT INTO ' . $table . ' (' . $dataPlaceholderField . ') VALUES (' . $dataPlaceholderValue . ')';
            $query = $this->db->prepare($sql);

            foreach($data as $k => $v) {
                if (is_numeric($v)) {
                    $pdoParam = $this->db::PARAM_INT;
                } elseif (is_bool($v)) {
                    $pdoParam = $this->db::PARAM_BOOL;
                } elseif (is_null($v)) {
                    $pdoParam = $this->db::PARAM_NULL;
                } else {
                    $pdoParam = $this->db::PARAM_STR;
                }
                $query->bindValue(":$k", $data[$k], $pdoParam);
            }
            $result = $query->execute();

            return $this->db->lastInsertId();

        } catch(\Exception $e) {

        }

        return false;
    }

    /**
     * Create SQL tables that Shieldon needs.
     *
     * @return void
     */
    protected function installSql(): void
    {
        $sql = "
            CREATE TABLE IF NOT EXISTS `{$this->tableLogs}` (
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
                `type` tinyint(1) UNSIGNED NOT NULL,
                `reason` tinyint(3) UNSIGNED NOT NULL,
                `time` int(10) UNSIGNED NOT NULL,
                PRIMARY KEY (`log_ip`)
            ) ENGINE={$this->tableDbEngine} DEFAULT CHARSET=latin1;
        ";

        $this->db->query($sql);

        $sql = "
            CREATE TABLE `{$this->tableDbEngine}` (
                `id` varchar(40) NOT NULL,
                `ip` varchar(46) NOT NULL,
                `time` int(10) UNSIGNED NOT NULL,
                PRIMARY KEY (`id`)
            ) ENGINE={$this->tableDbEngine} DEFAULT CHARSET=latin1;
        ";

        $this->db->query($sql);
    }

    /**
     * Clean all records in IP log and IP rule tables, and then rebuild new tables.
     *
     * @return void
     */
    protected function rebuildSql(): void
    {
        $sql = "DROP TABLE IF EXISTS `{$this->tableLogs}`";
        $this->db->query($sql);

        $sql = "DROP TABLE IF EXISTS `{$this->tableRuleList}`";
        $this->db->query($sql);

        $sql = "DROP TABLE IF EXISTS `{$this->tableDbEngine}`";
        $this->db->query($sql);

        $this->installSql();
    }

    /**
     * Check required tables exist or not.
     *
     * @return bool
     */
    protected function checkTableExists(): bool
    {
        $checkLogTable = $this->db->query("SHOW TABLES LIKE '{$this->tableLogs}'");

        if ($checkLogTable) {
            if ($checkLogTable->rowCount() > 0) {
                return true;
            }
        }

        return false;
    }
}