<?php declare(strict_types=1);
/*
 * This file is part of the Shieldon package.
 *
 * (c) Terry L. <contact@terryl.in>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Shieldon\Log;

use Shieldon\Log\ActionLogger as Logger;
use function strtotime;
use function date;
use function round;

/**
 * Parse the log files that created by ActionLogger
 */
class LogParser
{
    // Log codes. Same as Shieldon action codes.
    public const LOG_BAN = 0;
    public const LOG_ALLOW = 1;    
	public const LOG_TEMPORARILY_BAN = 2;
	public const LOG_UNBAN = 9;
	
	public const LOG_LIMIT = 3;
	public const LOG_PAGEVIEW = 11;
	public const LOG_BLACKLIST = 98;
    public const LOG_CAPTCHA = 99;

    /**
     * Undocumented variable
     *
     * @var array
     */
    protected $periodUnits = [];

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
	 * Constructer.
	 */
    public function __construct(string $directory = '')
    {
        if (! isset($this->logger)) {
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
			'captcha_failure_percent',
			'captcha_success_percent',
        ];

		// range: today ~ now
		$this->periods['today'] = [
			'timesamp_begin' => strtotime('today'),
			'timesamp_end'   => strtotime('tomorrow'),
			'display_format' =>'h:00 a',
			'display_count'  => 24,
			'period'         => 3600,
		];
		
		// range: yesterday ~ today
		$this->periods['yesterday'] = [
			'timesamp_begin' => strtotime('yesterday'),
			'timesamp_end'   => strtotime('today'),
			'display_format' =>'H:00',
			'display_count'  => 24,
			'period'         => 3600,
		];

		// range: past_seven_hours ~ now
		$this->periods['past_seven_hours'] = [
			'timesamp_begin' => strtotime(date('Y-m-d H:00:00', strtotime('-7 hours'))),
			'timesamp_end'   => strtotime(date('Y-m-d H:00:00', strtotime('-1 hours'))),
			'display_format' =>'H:00',
			'display_count'  => 7,
			'period'         => 3600,
		];

		// range: past_seven_days ~ today
		$this->periods['past_seven_days'] = [
			'timesamp_begin' => strtotime(date('Ymd', strtotime('-7 days'))),
			'timesamp_end'   => strtotime('today'),
			'display_format' => 'D',
			'display_count'  => 7,
			'period'         => 86400,
		];

		// range: last_month ~ today
		$this->periods['this_month'] = [
			'timesamp_begin' => strtotime(date('Ym' . '01')),
			'timesamp_end'   => strtotime('today'),
			'display_format' =>'Y.m.d',
			'display_count'  => date('j'),
			'period'         => 86400,   
		];

		// range: last_month ~ this_month
		$this->periods['last_month'] = [
			'timesamp_begin' => strtotime(date('Ym' . '01', strtotime('-1 months'))),
			'timesamp_end'   => strtotime(date('Ym' . '01')),
			'display_format' =>'Y.m.d',
			'display_count'  => date('j', strtotime('-1 months')),
			'period'         => 86400,          
		];
	}

	/**
	 * Parse specific period of time of data.
	 *
	 * @param string $type Period type.
	 *
	 * @return void
	 */
	public function parsePeriodData(string $type = 'today')
	{
		switch ($type) {

			case 'yesterday':
				$this->periodUnits['yesterday'] = $this->periods['yesterday'];

				// Set start date and end date.
				$startDate = date('Ymd', strtotime('yesterday'));
				$endDate   = date('Ymd', strtotime('yesterday'));
				break;
	
			case 'past_seven_days':
				$this->periodUnits['past_seven_days'] = $this->periods['past_seven_days'];
				$startDate = date('Ymd', strtotime('-7 days'));
				$endDate = date('Ymd');
				break;

			case 'this_month':
				$this->periodUnits['this_month'] = $this->periods['this_month'];
				$startDate = date('Ym') . '01';
				$endDate = date('Ym') . '31';
				break;

			case 'last_month':
				$this->periodUnits['last_month'] = $this->periods['last_month'];
				$startDate = date('Ym', strtotime('-1 month')) . '01';
				$endDate = date('Ym', strtotime('-1 month')) . '31';
				break;

			case 'today':
				$this->periodUnits['today'] = $this->periods['today'];
				$this->periodUnits['past_seven_hours'] = $this->periods['past_seven_hours'];
				$startDate = date('Ymd', strtotime('yesterday'));
				$endDate = date('Ymd');
				break;

			default:

				// We also accept querying N days data from logs. For example: `past_365_days`.
				if (preg_match('/past_([0-9]+)_days/', $type, $matches) ) {

					$dayCount = $matches[1];
					$startDate = date('Ymd', strtotime('-' . $dayCount . ' days'));
					$endDate = date('Ymd');

					$this->periods['past_' . $dayCount . '_days'] = [
						'timesamp_begin' => strtotime(date('Ymd', strtotime('-' . $dayCount . ' days'))),
						'timesamp_end'   => strtotime('today'),
						'display_format' => 'D',
						'display_count'  => $dayCount,
						'period'         => 86400,
					];

				} else {
					$this->periodUnits['today'] = $this->periods['today'];
					$startDate = date('Ymd');
					$endDate = date('Ymd');
				}
			// endswitch;
		}

		// Fetch data from log files.
		$logs = $this->logger->get($startDate, $endDate);

		foreach($logs as $log) {

			$logTimesamp = (int) $log['timesamp'];
			$logIp = $log['ip'];

			// Add a new field `datetime` that original logs don't have.
			$log['datetime'] = date('Y-m-d H:i:s', $logTimesamp);

			foreach (array_keys($this->periodUnits) as $t) {

				for ($i = 0; $i < $this->periodUnits[$t]['display_count']; $i++) {

					$kTimesamp = $this->periodUnits[$t]['timesamp_begin'] + ($i * $this->periodUnits[$t]['period']);

					$detailTimesampBegin = $kTimesamp;
					$detailTimesampEnd = $kTimesamp + $this->periodUnits[$t]['period'];

					$k = date($this->periodUnits[$t]['display_format'], $kTimesamp);

					// Initialize all the counters.
					foreach ($this->fields as $field) {
						if (! isset($this->periodDetail[$t][$k][$field])) {
							$this->periodDetail[$t][$k][$field] = 0;
						}

						if ($logTimesamp >= $detailTimesampBegin && $logTimesamp < $detailTimesampEnd) {
							if (! isset($this->ipDetail[$t][$logIp][$field])) {
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
		$this->parsePeriodData($type);
	}

    /**
     * Get data
	 *
	 * @param string $type Period type.
	 *
     * @return array
     */
	public function getPeriodData(string $type = 'today')
	{
		if (! empty($this->periodDetail[$type])) {
			return $this->periodDetail[$type];
		}
        return [];
	}

    /**
     * Get data
	 *
	 * @param string $type Period type.
     *
     * @return array
     */
	public function getIpData(string $type = 'today')
	{
		if (! empty($this->ipDetail[$type])) {
			return $this->ipDetail[$type];
		}
        return [];
	}

	/**
	 * Parse log data for showing on dashboard.
	 *
	 * @param array  $logActionCode The log action code.
	 * @param string $t             Time period type. (For example: `today`, `yesterday`, `past_seven_days`)
	 * @param string $k             Time period key. (For example: `12:00 am`, `20190812`)
	 *
	 * @return void
	 */
	private function parse($log, $t, $k) 
	{
		$logActionCode = (int) $log['action_code'];
		$logIp = $log['ip'];
		$sessionId = $log['session_id'];

		$this->ipDetail[$t][$logIp]['session_id'][$sessionId ] = 1;

		if ($logActionCode === self::LOG_TEMPORARILY_BAN) {
			$this->periodDetail[$t][$k]['action_temp_ban_count']++;
			$this->ipDetail[$t][$logIp]['action_temp_ban_count']++;
		}

		if ($logActionCode === self::LOG_BAN) {
			$this->periodDetail[$t][$k]['action_ban_count']++;
			$this->ipDetail[$t][$logIp]['action_ban_count']++;
		}

		if ($logActionCode === self::LOG_UNBAN) {
			$this->periodDetail[$t][$k]['action_unban_count']++;
			$this->periodDetail[$t][$k]['captcha_success_count']++;
			$this->ipDetail[$t][$logIp]['action_unban_count']++;
			$this->ipDetail[$t][$logIp]['captcha_success_count']++;
		}

		if ($logActionCode === self::LOG_CAPTCHA) {
			$this->periodDetail[$t][$k]['captcha_count']++;
			$this->ipDetail[$t][$logIp ]['captcha_count']++;
		}

		if ($logActionCode === self::LOG_BLACKLIST) {
			$this->periodDetail[$t][$k]['blacklist_count']++;
			$this->ipDetail[$t][$logIp]['blacklist_count']++;
		}

		if ($logActionCode === self::LOG_PAGEVIEW) {
			$this->periodDetail[$t][$k]['pageview_count']++;
			$this->ipDetail[$t][$logIp]['pageview_count']++;
		}

		if ($this->periodDetail[$t][$k]['captcha_count'] > 0) {

			// captcha_count should be the same as action_temp_ban_count, otherwise others were failed to solve CAPTCHA.
			$this->periodDetail[$t][$k]['captcha_failure_count'] = $this->periodDetail[$t][$k]['captcha_count'] - $this->periodDetail[$t][$k]['captcha_success_count'];
			$this->periodDetail[$t][$k]['captcha_failure_percent'] = round($this->periodDetail[$t][$k]['captcha_failure_count'] / $this->periodDetail[$t][$k]['captcha_count'], 2 ) * 100;
			$this->periodDetail[$t][$k]['captcha_success_percent'] = round($this->periodDetail[$t][$k]['captcha_success_count'] / $this->periodDetail[$t][$k]['captcha_count'], 2 ) * 100;
			$this->periodDetail[$t][$k]['captcha_percent'] = round($this->ipDetail[$t][$logIp]['captcha_count'] / ($this->ipDetail[$t][$logIp]['captcha_count'] + $this->ipDetail[$t][$logIp]['pageview_count'] ), 2 ) * 100;

			$this->ipDetail[$t][$logIp]['captcha_failure_count'] = $this->ipDetail[$t][$logIp]['captcha_count'] - $this->ipDetail[$t][$logIp]['captcha_success_count'];
			$this->ipDetail[$t][$logIp]['captcha_failure_percent'] = round($this->ipDetail[$t][$logIp]['captcha_failure_count'] / $this->ipDetail[$t][$logIp]['captcha_count'], 2 ) * 100;
			$this->ipDetail[$t][$logIp]['captcha_success_percent'] = round($this->ipDetail[$t][$logIp]['captcha_success_count'] / $this->ipDetail[$t][$logIp]['captcha_count'], 2 ) * 100;
			$this->ipDetail[$t][$logIp]['captcha_percent'] = round($this->ipDetail[$t][$logIp]['captcha_count'] / ($this->ipDetail[$t][$logIp]['captcha_count'] + $this->ipDetail[$t][$logIp]['pageview_count'] ), 2 ) * 100;
		}
	}
}
