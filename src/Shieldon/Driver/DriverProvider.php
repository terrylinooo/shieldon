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

/**
 * DriverProvider
 */
abstract class DriverProvider extends AbstractDriver
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
    protected $isInitialized;

    /**
     * Set data channel.
     *
     * @param string $channel
     *
     * @return void
     */
    public function setChannel(string $channel): void
    {
        $this->channel = $channel;

        if (! empty($this->channel)) {
            $this->tableLogs = $this->channel . '_shieldon_logs';
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
     * @param array $data
     * @param string $tyle
     *
     * @return array
     */
    public function parseData(array $data, string $type = 'log'): array
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
            case 'log':
            default:

                $fields = [

                    // Basic IP data.
                    'ip'       => 'string', 
                    'session'  => 'string', 
                    'hostname' => 'string', 

                    // timesamp while visting first time.
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
}