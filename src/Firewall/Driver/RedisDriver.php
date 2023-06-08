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
use Redis;
use function is_bool;
use function json_decode;
use function json_encode;
use function ksort;

/**
 * Redis Driver.
 */
class RedisDriver extends DriverProvider
{
    /**
     * Redis instance.
     *
     * @var object|null
     */
    protected $redis;

    /**
     * Constructor.
     *
     * @param Redis $redis The Redis instance.
     *
     * @return void
     */
    public function __construct(Redis $redis)
    {
        $this->redis = $redis;
    }

    /**
     * Set data channel.
     *
     * @param string $channel The prefix of data tables.
     *
     * @return void
     */
    public function setChannel(string $channel): void
    {
        $this->channel = $channel;

        if (!empty($this->channel)) {
            $this->tableFilterLogs = $this->channel . ':shieldon_filter_logs';
            $this->tableRuleList   = $this->channel . ':shieldon_rule_list';
            $this->tableSessions   = $this->channel . ':shieldon_sessions';
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
        $this->isInitialized = true;
    }

    /**
     * {@inheritDoc}
     *
     * @param string $type The type of the data table.
     *
     * @return bool
     */
    protected function doFetchAll(string $type = 'filter'): array
    {
        $results = [];

        $this->assertInvalidDataTable($type);

        $keys = $this->redis->keys($this->getNamespace($type) . ':*');

        foreach ($keys as $key) {
            $content = $this->redis->get($key);

            if (!empty($content)) {
                $content = json_decode($content, true);

                if ($type === 'session') {
                    $sort = $content['microtimestamp'] . '.' . $content['id'];
                } else {
                    $sort = $content['log_ip'];
                }
    
                $results[$sort] = $content;
            }
        }

        // Sort by ascending timestamp (microtimestamp).
        ksort($results);

        return $results;
    }

    /**
     * {@inheritDoc}
     *
     * @param string $ip   The data id of the entry to fetch.
     * @param string $type The type of the data table.
     *
     * @return array
     */
    protected function doFetch(string $ip, string $type = 'filter'): array
    {
        $results = [];

        if (!$this->checkExist($ip, $type)) {
            return $results;
        }

        if ($type === 'filter') {
            $content = $this->redis->get($this->getKeyName($ip, $type));
            $resultData = json_decode($content, true);

            if (!empty($resultData['log_data'])) {
                $results = $resultData['log_data'];
            }
        } else {
            // type: rule | session
            $content = $this->redis->get($this->getKeyName($ip, $type));
            $results = json_decode($content, true);
        }

        return $results;
    }

    /**
     * {@inheritDoc}
     *
     * @param string $ip   The data id of the entry to check for.
     * @param string $type The type of the data table.
     *
     * @return bool
     */
    protected function checkExist(string $ip, string $type = 'filter'): bool
    {
        $isExist = $this->redis->exists($this->getKeyName($ip, $type));

        // This function took a single argument and returned TRUE or FALSE in phpredis versions < 4.0.0.

        // @codeCoverageIgnoreStart
        if (is_bool($isExist)) {
            return $isExist;
        }

        return $isExist > 0;
        // @codeCoverageIgnoreEnd
    }

    /**
     * {@inheritDoc}
     *
     * @param string $ip     The data id.
     * @param array  $data   The data.
     * @param string $type   The type of the data table.
     * @param int    $expire The data will be deleted after expiring.
     *
     * @return bool
     */
    protected function doSave(string $ip, array $data, string $type = 'filter', $expire = 0): bool
    {
        switch ($type) {
            case 'rule':
                $logData = $data;
                $logData['log_ip'] = $ip;
                break;

            case 'filter':
                $logData = [];
                $logData['log_ip'] = $ip;
                $logData['log_data'] = $data;
                break;

            case 'session':
                $logData = $data;
                break;

            // @codeCoverageIgnoreStart
            default:
                return false;
            // @codeCoverageIgnoreEnd
        }

        if ($expire > 0) {
            return $this->redis->setex(
                $this->getKeyName($ip, $type),
                $expire,
                json_encode($logData)
            );
        }

        return $this->redis->set(
            $this->getKeyName($ip, $type),
            json_encode($logData)
        );
    }

    /**
     * {@inheritDoc}
     *
     * @param string $ip   The key name of a redis entry.
     * @param string $type The type of the data table.
     *
     * @return bool
     */
    protected function doDelete(string $ip, string $type = 'filter'): bool
    {
        if (in_array($type, ['rule', 'filter', 'session'])) {
            return $this->redis->del($this->getKeyName($ip, $type)) >= 0;
        }
        return false;
    }

    /**
     * {@inheritDoc}
     *
     * Rebuild data tables.
     *
     * @return bool
     */
    protected function doRebuild(): bool
    {
        foreach (['rule', 'filter', 'session'] as $type) {
            $keys = $this->redis->keys($this->getNamespace($type) . ':*');

            if (!empty($keys)) {
                foreach ($keys as $key) {
                    $this->redis->del($key);
                }
            }
        }
        return false;
    }

    /**
     * Get key name.
     *
     * @param string $ip   The key name of a redis entry.
     * @param string $type The type of the data table.
     *
     * @return string
     */
    private function getKeyName(string $ip, string $type = 'filter'): string
    {
        $table = [
            'filter'  => $this->tableFilterLogs . ':' . $ip,
            'session' => $this->tableSessions   . ':' . $ip,
            'rule'    => $this->tableRuleList   . ':' . $ip,
        ];
        
        return $table[$type] ?? '';
    }

    /**
     * Get namespace.
     *
     * @param string $type The type of the data table.
     *
     * @return string
     */
    private function getNamespace(string $type = 'filter'): string
    {
        $table = [
            'filter'  => $this->tableFilterLogs,
            'session' => $this->tableSessions,
            'rule'    => $this->tableRuleList,
        ];

        return $table[$type] ?? '';
    }
}
