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

use Shieldon\Firewall\Driver\AbstractDriver;
use RuntimeException;

/**
 * Driver Provider.
 */
class DriverProvider extends AbstractDriver
{
    /**
     * Data table for regular filter logs.
     *
     * @var string
     */
    protected $tableFilterLogs = 'shieldon_filter_logs';

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
     * The prefix of the database tables, or the name of file directory.
     *
     * @var string
     */
    protected $channel = '';

    /**
     * Check if is initialized or not.
     *
     * @var bool
     */
    protected $isInitialized = false;

    /**
     * The table types.
     *
     * @var array
     */
    protected $tableTypes = [
        'rule',
        'filter',
        'session',
    ];

    /**
     * Set data channel.
     *
     * @param string $channel The prefix of the data tables.
     *
     * @return void
     */
    public function setChannel(string $channel): void
    {
        $this->channel = $channel;

        if (!empty($this->channel)) {
            $this->tableFilterLogs = $this->channel . '_shieldon_filter_logs';
            $this->tableRuleList = $this->channel . '_shieldon_rule_list';
            $this->tableSessions = $this->channel . '_shieldon_sessions';
        }
    }

    /**
     * Get channel name.
     *
     * @return string
     */
    public function getChannel(): string
    {
        return $this->channel;
    }

    /**
     * Return parsed full data structure.
     *
     * @param array  $data The data needed to be parsed.
     * @param string $type The type of data table. accepts: filter | session | rule
     *
     * @return array
     */
    public function parseData(array $data, string $type = 'filter'): array
    {
        $parsedData = [];

        switch ($type) {
            // Rule table data structure.
            case 'rule':
                break;

            // Session table data structure.
            case 'session':
                break;

            // Log table data structure.
            case 'filter':
                // no break
            default:
                $fields = [

                    // Basic IP data.
                    'ip'       => 'string',
                    'session'  => 'string',
                    'hostname' => 'string',

                    // timestamp while visting first time.
                    'first_time_s'    => 'int',
                    'first_time_m'    => 'int',
                    'first_time_h'    => 'int',
                    'first_time_d'    => 'int',
                    'first_time_flag' => 'int',
                    'last_time'       => 'int',

                    // Signals for flagged bad behavior.
                    'flag_js_cookie'     => 'int',
                    'flag_multi_session' => 'int',
                    'flag_empty_referer' => 'int',

                    // Pageview count.
                    'pageviews_cookie' => 'int',
                    'pageviews_s'      => 'int',
                    'pageviews_m'      => 'int',
                    'pageviews_h'      => 'int',
                    'pageviews_d'      => 'int',
                ];

                foreach ($fields as $k => $v) {
                    $tmp = $data[$k] ?? '';

                    if ('string' === $v) {
                        $parsedData[$k] = (string) $tmp;
                    }

                    if ('int' === $v) {
                        $parsedData[$k] = (int) $tmp;
                    }
                }
                break;
            // end switch
        }

        return $parsedData;
    }

    // @codeCoverageIgnoreStart

    /**
     * Implement fetch.
     *
     * @param string $ip   The data id of the entry to fetch.
     * @param string $type The type of data table. accepts: filter | session | rule
     *
     * @return array The data or an empty array.
     */
    protected function doFetch(string $ip, string $type = 'filter'): array
    {
        return [];
    }

    /**
     * Implement fetch all.
     *
     * @param string $type The type of data table. accepts: filter | session | rule
     *
     * @return array The data or an empty array.
     */
    protected function doFetchAll(string $type = 'filter'): array
    {
        return [];
    }

    /**
     * Implement has.
     *
     * @param string $ip   The data id of the entry to check for.
     * @param string $type The type of data table. accepts: filter | session | rule
     *
     * @return bool
     */
    protected function checkExist(string $ip, string $type = 'filter'): bool
    {
        return false;
    }

    /**
     * Implement save.
     *
     * @param string $ip     The IP address as the data id.
     * @param array  $data   The data.
     * @param string $type   The type of the data table.
     * @param int    $expire The data will be deleted after expiring.
     *
     * @return bool
     */
    protected function doSave(string $ip, array $data, string $type = 'filter', $expire = 0): bool
    {
        return false;
    }

    /**
     * Implement delete.
     *
     * @param string $ip   The IP address.
     * @param string $type The type of data table. accepts: filter | session | rule
     *
     * @return bool
     */
    protected function doDelete(string $ip, string $type = 'filter'): bool
    {
        return false;
    }

    /**
     * Rebuild data tables.
     *
     * @return bool
     */
    protected function doRebuild(): bool
    {
        return false;
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
    }

    /**
     * Check data type.
     *
     * @param string $type The type of the data tables.
     *
     * @return void
     */
    protected function assertInvalidDataTable(string $type): void
    {
        if (!in_array($type, $this->tableTypes)) {
            throw new RuntimeException(
                'Invalid data type of the data tables.'
            );
        }
    }
    // @codeCoverageIgnoreEnd
}
