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

namespace Shieldon\Firewall\Log;

use Shieldon\Firewall\Log\ActionLogger as Logger;

use function date;
use function round;
use function strtotime;

/**
 * Parse the log files that created by ActionLogger.
 */
final class ActionLogParser
{
    // Log codes. Same as Shieldon action codes.
    const LOG_BAN = 0;
    const LOG_ALLOW = 1;
    const LOG_TEMPORARILY_BAN = 2;
    const LOG_UNBAN = 9;
    
    const LOG_LIMIT = 3;
    const LOG_PAGEVIEW = 11;
    const LOG_BLACKLIST = 98;
    const LOG_CAPTCHA = 99;

    /**
     * Statistic data fields.
     *
     * @var array
     */
    protected $fields = [];
    
    /**
     * Period type of the statistic data.
     *
     * @var array
     */
    protected $periods = [];

    /**
     * Data detail.
     *
     * For example:
     * $this->periodDetail['today']['12:00 am'][$field] = 7;
     *
     * @var array
     */
    protected $periodDetail = [];

    /**
     * IP Detail
     *
     * For example:
     * $this->ipDetail['today']['127.0.0.1'][$fields] = 6;
     *
     * @var array
     */
    protected $ipDetail = [];

    /**
     * ActionLogger instance.
     *
     * @var ActionLogger
     */
    protected $logger;

    /**
     * Period type.
     *
     * @var string
     */
    protected $type = 'today';

    /**
     * Constructer.
     *
     * @param string $directory The directory where to store the logs in.
     */
    public function __construct(string $directory = '')
    {
        if (!isset($this->logger)) {
            $this->logger = new Logger($directory);
        }

        $this->fields = [
            'captcha_count',
            'captcha_success_count',
            'captcha_failure_count',
            'pageview_count',
            'action_ban_count',
            'action_temp_ban_count',
            'action_unban_count',
            'blacklist_count',
            'session_limit_count',
            'captcha_failure_percentage',
            'captcha_success_percentage',
        ];

        // range: today ~ now
        $this->periods['today'] = [
            'timestamp_begin' => strtotime('today'),
            'timestamp_end'   => strtotime('tomorrow'),
            'display_format'  => 'h:00 a',
            'display_count'   => 24,
            'period'          => 3600,
        ];
        
        // range: yesterday ~ today
        $this->periods['yesterday'] = [
            'timestamp_begin' => strtotime('yesterday'),
            'timestamp_end'   => strtotime('today'),
            'display_format'  => 'H:00',
            'display_count'   => 24,
            'period'          => 3600,
        ];

        // range: past_seven_hours ~ now
        $this->periods['past_seven_hours'] = [
            'timestamp_begin' => strtotime(date('Y-m-d H:00:00', strtotime('-7 hours'))),
            'timestamp_end'   => strtotime(date('Y-m-d H:00:00', strtotime('-1 hours'))),
            'display_format'  => 'H:00',
            'display_count'   => 7,
            'period'          => 3600,
        ];

        // range: past_seven_days ~ today
        $this->periods['past_seven_days'] = [
            'timestamp_begin' => strtotime(date('Ymd', strtotime('-7 days'))),
            'timestamp_end'   => strtotime('today'),
            'display_format'  => 'D',
            'display_count'   => 7,
            'period'          => 86400,
        ];

        // range: last_month ~ today
        $this->periods['this_month'] = [
            'timestamp_begin' => strtotime(gmdate('Ym' . '01')),
            'timestamp_end'   => strtotime('today'),
            'display_format'  => 'Y.m.d',
            'display_count'   => gmdate('j'),
            'period'          => 86400,
        ];

        // range: last_month ~ this_month
        $this->periods['last_month'] = [
            'timestamp_begin' => strtotime(gmdate('Ym' . '01', strtotime('-1 months'))),
            'timestamp_end'   => strtotime(gmdate('Ym' . '01')),
            'display_format'  => 'Y.m.d',
            'display_count'   => gmdate('j', strtotime('-1 months')),
            'period'          => 86400,
        ];
    }

    /**
     * Get the start and end date depends on the log type.
     *
     * @return array
     */
    protected function getStartEndDate(): array
    {
        $dataRange = [
            'yesterday' => [
                'start' => date('Ymd', strtotime('yesterday')),
                'end'   => date('Ymd'),
            ],
            'past_seven_days' => [
                'start' => date('Ymd', strtotime('-7 days')),
                'end'   => date('Ymd'),
            ],
            'this_month' => [
                'start' => date('Ym') . '01',
                'end'   => date('Ym') . '31',
            ],
            'last_month' => [
                'start' => date('Ym', strtotime('-1 month')) . '01',
                'end'   => date('Ym', strtotime('-1 month')) . '31',
            ],
            'past_seven_hours' => [
                'start' => date('Ymd', strtotime('yesterday')),
                'end'   => date('Ymd'),
            ],
            'today' => [
                'start' => date('Ymd'),
                'end'   => '',
            ],
        ];

        if (empty($dataRange[$this->type])) {
            if (preg_match('/past_([0-9]+)_days/', $this->type, $matches)) {
                $dayCount = $matches[1];
                $startDate = date('Ymd', strtotime('-' . $dayCount . ' days'));
                $endDate = date('Ymd');

                $this->periods['past_' . $dayCount . '_days'] = [
                    'timestamp_begin' => strtotime(date('Ymd', strtotime('-' . $dayCount . ' days'))),
                    'timestamp_end'   => strtotime('today'),
                    'display_format' => 'D',
                    'display_count'  => $dayCount,
                    'period'         => 86400,
                ];
            } else {
                $startDate = date('Ymd');
                $endDate = '';
                $this->periods[$this->type] = $this->periods['today'];
            }
        } else {
            $startDate = $dataRange[$this->type]['start'];
            $endDate = $dataRange[$this->type]['end'];
        }

        return [
            'start' => $startDate,
            'end' => $endDate,
        ];
    }

    /**
     * Parse specific period of time of data.
     *
     * Warning: This method may take long time to generate real-time stats on a high-traffic website.
     * Aprroximately 10,000 rows for 3-5 seconds, depnonds on your server's CPU speed.
     *
     * @return void
     */
    public function parsePeriodData(): void
    {
        $dateRange = $this->getStartEndDate();
        $startDate = $dateRange['start'];
        $endDate = $dateRange['end'];

        // Fetch data from log files.
        $logs = $this->logger->get($startDate, $endDate);

        foreach ($logs as $log) {
            $logTimesamp = (int) $log['timestamp'];
            $logIp = $log['ip'];

            // Add a new field `datetime` that original logs don't have.
            $log['datetime'] = date('Y-m-d H:i:s', $logTimesamp);
            
            foreach (array_keys($this->periods) as $t) {
                for ($i = 0; $i < $this->periods[$t]['display_count']; $i++) {
                    $kTimesamp = $this->periods[$t]['timestamp_begin'] + ($i * $this->periods[$t]['period']);

                    $detailTimesampBegin = $kTimesamp;
                    $detailTimesampEnd = $kTimesamp + $this->periods[$t]['period'];

                    $k = date($this->periods[$t]['display_format'], $kTimesamp);

                    // Initialize all the counters.
                    foreach ($this->fields as $field) {
                        if (!isset($this->periodDetail[$t][$k][$field])) {
                            $this->periodDetail[$t][$k][$field] = 0;
                        }

                        if ($logTimesamp >= $detailTimesampBegin && $logTimesamp < $detailTimesampEnd) {
                            if (!isset($this->ipDetail[$t][$logIp][$field])) {
                                $this->ipDetail[$t][$logIp][$field] = 0;
                            }
                        }
                    }

                    // Initialize all the counters.
                    if ($logTimesamp >= $detailTimesampBegin && $logTimesamp < $detailTimesampEnd) {
                        $this->parse($log, $t, $k);
                    }
                }
            }
        }
    }
    
    /**
     * Prepare data.
     *
     * @param string $type Period type.
     *
     * @return void
     */
    public function prepare(string $type = 'today'): void
    {
        $this->type = $type;

        $this->parsePeriodData();
    }

    /**
     * Get data
     *
     * @return array
     */
    public function getPeriodData()
    {
        if (!empty($this->periodDetail[$this->type])) {
            return $this->periodDetail[$this->type];
        }
        return [];
    }

    /**
     * Get data
     *
     * @return array
     */
    public function getIpData()
    {
        if (!empty($this->ipDetail[$this->type])) {
            return $this->ipDetail[$this->type];
        }
        return [];
    }

    /**
     * Get parsed perid data.
     *
     * @param string $ip The IP address.
     *
     * @return array
     */
    public function getParsedIpData($ip = ''): array
    {
        if (empty($ip)) {
            return [];
        }

        $results = [];

        $results['captcha_chart_string']       = ''; // string
        $results['pageview_chart_string']      = ''; // string
        $results['captcha_success_count']      = 0;  // integer
        $results['captcha_failure_count']      = 0;  // integer
        $results['captcha_count']              = 0;  // integer
        $results['pageview_count']             = 0;  // integer
        $results['captcha_percentageage']      = 0;  // integer
        $results['captcha_failure_percentage'] = 0;  // integer
        $results['captcha_success_percentage'] = 0;  // integer
        $results['action_ban_count']           = 0;  // integer
        $results['action_temp_ban_count']      = 0;  // integer
        $results['action_unban_count']         = 0;  // integer
        $results['blacklist_count']            = 0;  // integer
        $results['session_limit_count']        = 0;  // integer

        $ipdData = $this->getIpData();

        if (!empty($ipdData)) {
            foreach ($ipdData as $ipKey => $ipInfo) {
                if ($ipKey === $ip) {
                    $results['captcha_success_count'] += $ipInfo['captcha_success_count'];
                    $results['captcha_failure_count'] += $ipInfo['captcha_failure_count'];
                    $results['captcha_count']         += $ipInfo['captcha_count'];
                    $results['pageview_count']        += $ipInfo['pageview_count'];
                    $results['action_ban_count']      += $ipInfo['action_ban_count'];
                    $results['action_temp_ban_count'] += $ipInfo['action_temp_ban_count'];
                    $results['action_unban_count']    += $ipInfo['action_unban_count'];
                    $results['blacklist_count']       += $ipInfo['blacklist_count'];
                    $results['session_limit_count']   += $ipInfo['session_limit_count'];
                }
            }

            if ($results['captcha_count'] > 0) {
                $results['captcha_percentageage'] = (int) (
                    round($results['captcha_count'] / ($results['captcha_count'] + $results['pageview_count']), 2) * 100
                );
                $results['captcha_failure_percentage'] = (int) (
                    round($results['captcha_failure_count'] / $results['captcha_count'], 2) * 100
                );
                $results['captcha_success_percentage'] = (int) (
                    round($results['captcha_success_count'] / $results['captcha_count'], 2) * 100
                );
            }
        }

        return $results;
    }

    /**
     * Get parsed perid data.
     *
     * @return array
     */
    public function getParsedPeriodData(): array
    {
        $periodData = $this->getPeriodData();

        $results = [];

        $results['label_chart_string']         = ''; // string
        $results['captcha_chart_string']       = ''; // string
        $results['pageview_chart_string']      = ''; // string
        $results['captcha_success_count']      = 0;  // integer
        $results['captcha_failure_count']      = 0;  // integer
        $results['captcha_count']              = 0;  // integer
        $results['pageview_count']             = 0;  // integer
        $results['captcha_percentageage']      = 0;  // integer
        $results['captcha_failure_percentage'] = 0;  // integer
        $results['captcha_success_percentage'] = 0;  // integer
        $results['action_ban_count']           = 0;  // integer
        $results['action_temp_ban_count']      = 0;  // integer
        $results['action_unban_count']         = 0;  // integer
        $results['blacklist_count']            = 0;  // integer
        $results['session_limit_count']        = 0;  // integer

        if (!empty($periodData)) {
            $chartCaptcha = [];
            $chartPageview = [];
            $chartCaptchaSuccess = [];
            $chartCaptchaFailure = [];
            $labels = [];

            foreach ($periodData as $label => $period) {
                $labels[] = $label;

                $chartCaptcha[]        = $period['captcha_count'];
                $chartPageview[]       = $period['pageview_count'];
                $chartCaptchaSuccess[] = $period['captcha_success_count'];
                $chartCaptchaFailure[] = $period['captcha_failure_count'];
    
                $results['captcha_success_count'] += $period['captcha_success_count'];
                $results['captcha_failure_count'] += $period['captcha_failure_count'];
                $results['captcha_count']         += $period['captcha_count'];
                $results['pageview_count']        += $period['pageview_count'];
                $results['action_ban_count']      += $period['action_ban_count'];
                $results['action_temp_ban_count'] += $period['action_temp_ban_count'];
                $results['action_unban_count']    += $period['action_unban_count'];
                $results['blacklist_count']       += $period['blacklist_count'];
                $results['session_limit_count']   += $period['session_limit_count'];
            }

            $results['captcha_chart_string']         = implode(',', $chartCaptcha);
            $results['pageview_chart_string']        = implode(',', $chartPageview);
            $results['captcha_success_chart_string'] = implode(',', $chartCaptchaSuccess);
            $results['captcha_failure_chart_string'] = implode(',', $chartCaptchaFailure);
            $results['label_chart_string']           = "'" . implode("','", $labels) . "'";

            if ($results['captcha_count'] > 0) {
                $results['captcha_percentageage'] = (int) (
                    round($results['captcha_count'] / ($results['captcha_count'] + $results['pageview_count']), 2) * 100
                );
                $results['captcha_failure_percentage'] = (int) (
                    round($results['captcha_failure_count'] / $results['captcha_count'], 2) * 100
                );
                $results['captcha_success_percentage'] = (int) (
                    round($results['captcha_success_count'] / $results['captcha_count'], 2) * 100
                );
            }
        }

        return $results;
    }

    /**
     * Parse log data for showing on dashboard.
     *
     * @param array  $log The log action code.
     * @param string $t   Time period type. (For example: `today`, `yesterday`, `past_seven_days`)
     * @param string $k   Time period key. (For example: `12:00 am`, `20190812`)
     *
     * @return void
     */
    private function parse($log, $t, $k): void
    {
        $logActionCode = (int) $log['action_code'];
        $ip = $log['ip'];
        $sessionId = $log['session_id'];

        $this->ipDetail[$t][$ip]['session_id'][$sessionId ] = 1;

        if ($logActionCode === self::LOG_TEMPORARILY_BAN) {
            $this->periodDetail[$t][$k]['action_temp_ban_count']++;
            $this->periodDetail[$t][$k]['captcha_count']++;
            $this->periodDetail[$t][$k]['captcha_failure_count']++;

            $this->ipDetail[$t][$ip]['action_temp_ban_count']++;
            $this->ipDetail[$t][$ip]['captcha_count']++;
            $this->ipDetail[$t][$ip]['captcha_failure_count']++;
        }

        if ($logActionCode === self::LOG_BAN) {
            $this->periodDetail[$t][$k]['action_ban_count']++;
            $this->ipDetail[$t][$ip]['action_ban_count']++;
        }

        if ($logActionCode === self::LOG_UNBAN) {
            $this->periodDetail[$t][$k]['action_unban_count']++;
            $this->periodDetail[$t][$k]['captcha_success_count']++;
            $this->periodDetail[$t][$k]['captcha_failure_count']--;

            $this->ipDetail[$t][$ip]['action_unban_count']++;
            $this->ipDetail[$t][$ip]['captcha_success_count']++;
            $this->ipDetail[$t][$ip]['captcha_failure_count']--;
        }

        if ($logActionCode === self::LOG_CAPTCHA) {
            $this->periodDetail[$t][$k]['captcha_count']++;
            $this->periodDetail[$t][$k]['captcha_failure_count']++;

            $this->ipDetail[$t][$ip]['captcha_count']++;
            $this->ipDetail[$t][$ip]['captcha_failure_count']++;
        }

        if ($logActionCode === self::LOG_BLACKLIST) {
            $this->periodDetail[$t][$k]['blacklist_count']++;
            $this->ipDetail[$t][$ip]['blacklist_count']++;
        }

        if ($logActionCode === self::LOG_PAGEVIEW) {
            $this->periodDetail[$t][$k]['pageview_count']++;
            $this->ipDetail[$t][$ip]['pageview_count']++;
        }

        if ($logActionCode === self::LOG_LIMIT) {
            $this->periodDetail[$t][$k]['session_limit_count']++;
            $this->ipDetail[$t][$ip]['session_limit_count']++;
        }
    }

    /**
     * Return current log's directory.
     *
     * @return string
     */
    public function getDirectory(): string
    {
        return $this->logger->getDirectory();
    }
}
