<?php declare(strict_types=1);
/*
 * This file is part of the Shieldon package.
 *
 * (c) Terry L. <contact@terryl.in>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Shieldon;

use ReflectionObject;

/**
 * Display a Control Panel UI for developers or administrators.
 */
class ControlPanel
{
	/**
	 * Shieldon instance.
	 *
	 * @var object
	 */
	protected $sheidlon;

	/**
	 * LogPaeser instance.
	 *
	 * @var object
	 */
	protected $parser;

	/**
	 * Error message.
	 *
	 * @var string
	 */
	private $err = '';

	/**
	 * Constructor.
	 *
	 * @param Shieldon $shieldon
	 */
	public function __construct(Shieldon $shieldon) 
	{
		$this->shieldon = $shieldon;

		if (! empty($this->shieldon->logger)) {

			// We need to know where the logs stored in.
			$logDirectory = $this->shieldon->logger->getDirectory();

			// Load logParser for parsing log files.
			$this->parser = new \Shieldon\Log\LogParser($logDirectory);

		} else {
			$this->err = 'ActionLogger is not implemented with Shieldon.';
		}
	}

	/**
	 * Display pages.
	 *
	 * @param string $slug
	 * @return void
	 */
	public function page()
	{
		$slug = $_GET['so_page'] ?? '';

		switch($slug) {

			case 'op_info':
				$this->info();
				break;

			case 'session_table':
				$this->sessionTable();
				break;

			case 'ip_log_table':
				$this->ipLogTable();
				break;

			case 'ip_rule_table':
				$this->ruleTable();
				break;

			case 'dashboard_today':
			case 'dashboard_yesterday':
			case 'dashboard_past_seven_days':
			case 'dashboard_this_month':
			case 'dashboard_last_month':
			default:
				$this->dashboard();
				break;
		}
	}

	/**
	 * Shieldon operating information.
	 *
	 * @return void
	 */
	public function info(): void
	{
		/*
		|--------------------------------------------------------------------------
		| Logger
		|--------------------------------------------------------------------------
		|
		| All logs were recorded by ActionLogger.
		| Get the summary information from those logs.
		|
		*/
		$loggerInfo = $this->shieldon->logger->getCurrentLoggerInfo();

		$data['logger_started_working_date'] = '';
		$data['logger_work_days'] = '';
		$data['logger_total_size'] = '';

		if (! empty($loggerInfo)) {

			$i = 0;
			ksort($loggerInfo);

			foreach ($loggerInfo as $date => $size) {
				if (0 === $i) {
					$data['logger_started_working_date'] = date('Y-m-d', strtotime((string) $date));
				}
				$i += (int) $size;
			}

			$data['logger_work_days'] = count($loggerInfo);
			$data['logger_total_size'] = round($i / (1024 * 1024), 5) . ' MB';
		}

		/*
		|--------------------------------------------------------------------------
		| Data circle
		|--------------------------------------------------------------------------
		|
		| A data circle includes the primary data tables of Shieldon.
		| They are ip_log_table, ip_rule_table and session_table.
		|
		*/


		/*
		|--------------------------------------------------------------------------
		| Shieldon status
		|--------------------------------------------------------------------------
		|
		| 1. Components.
		| 2. Filters.
		| 3. Configuration.
		|
		*/

		$data['components'] = [
			'Ip'         => (! empty($this->shieldon->component['Ip']))         ? true : false,
			'TrustedBot' => (! empty($this->shieldon->component['TrustedBot'])) ? true : false,
			'Header'     => (! empty($this->shieldon->component['Header']))     ? true : false,
			'Rdns'       => (! empty($this->shieldon->component['Rdns']))       ? true : false,
			'UserAgent'  => (! empty($this->shieldon->component['UserAgent']))  ? true : false,
		];

        $reflection = new ReflectionObject($this->shieldon);
        $t = $reflection->getProperty('enableCookieCheck');
        $t->setAccessible(true);
		$enableCookieCheck = $t->getValue($this->shieldon);

		$reflection = new ReflectionObject($this->shieldon);
        $t = $reflection->getProperty('enableSessionCheck');
        $t->setAccessible(true);
		$enableSessionCheck = $t->getValue($this->shieldon);

		$reflection = new ReflectionObject($this->shieldon);
        $t = $reflection->getProperty('enableFrequencyCheck');
        $t->setAccessible(true);
		$enableFrequencyCheck = $t->getValue($this->shieldon);

		$reflection = new ReflectionObject($this->shieldon);
        $t = $reflection->getProperty('enableRefererCheck');
        $t->setAccessible(true);
		$enableRefererCheck = $t->getValue($this->shieldon);

		$data['filters'] = [
			'cookie'    => $enableCookieCheck,
			'session'   => $enableSessionCheck,
			'frequency' => $enableFrequencyCheck,
			'referer'   => $enableRefererCheck,
		];

		$reflection = new ReflectionObject($this->shieldon);
        $t = $reflection->getProperty('properties');
        $t->setAccessible(true);
		$properties = $t->getValue($this->shieldon);
		
		$data['configuration'] = $properties;

		$this->renderPage('dashboard/op_info', $data);
	}

	/**
	 * Dsiplay dashboard.
	 *
	 * @return void
	 */
	public function dashboard(): void
	{
		$tab = $_GET['tab'] ?? 'today';

		switch ($tab) {
			case 'yesterday':
			case 'this_month':
			case 'last_month':
			case 'past_seven_days':
			case 'today':
				$type = $tab;
				break;

			default:
				$type = 'today';
		}

		$this->parser->prepare($type);

		$data['ip_details'] = $this->parser->getIpData();
		$data['period_data'] = $this->parser->getParsedPeriodData();

		if ('today' === $type ) {
			$this->parser->prepare('past_seven_hours');
			$data['past_seven_hour'] = $this->parser->getParsedPeriodData();
		}

		$data['page_url'] = $this->url('dashboard');

		$this->renderPage('dashboard/dashboard_' . $type, $data);
	}

	/**
	 * Rule table for current cycle.
	 *
	 * @param string
	 *
	 * @return void
	 */
	public function ruleTable(): void
	{
		if (isset($_POST['ip'])) {

			$ip = $_POST['ip'];
			$action = $_POST['action'];

			$actionCode['temporarily_ban'] = $this->shieldon::ACTION_TEMPORARILY_DENY;
			$actionCode['permanently_ban'] = $this->shieldon::ACTION_DENY;
			$actionCode['allow'] = $this->shieldon::ACTION_ALLOW;

			switch ($action) {
				case 'temporarily_ban':
				case 'permanently_ban':
				case 'allow':
					$logData['log_ip'] = $ip;
					$logData['ip_resolve'] = gethostbyaddr($ip);
					$logData['time'] = time();
					$logData['type'] = $actionCode[$action];
					$logData['reason'] = $this->shieldon::REASON_MANUAL_BAN;

					$this->shieldon->driver->save($ip, $logData, 'rule');
					break;

				case 'remove':
					$this->shieldon->driver->delete($ip, 'rule');
					break;
			}
		}

		$reasons = [
			$this->shieldon::REASON_MANUAL_BAN           => 'Added manually by administrator',
			$this->shieldon::REASON_IS_SEARCH_ENGINE     => 'Search engine bot',
			$this->shieldon::REASON_IS_GOOGLE            => 'Google bot',
			$this->shieldon::REASON_IS_BING              => 'Bing bot',
			$this->shieldon::REASON_IS_YAHOO             => 'Yahoo bot',
			$this->shieldon::REASON_TOO_MANY_SESSIONS    => 'Too many sessions',
			$this->shieldon::REASON_TOO_MANY_ACCESSES    => 'Too many accesses',
			$this->shieldon::REASON_EMPTY_JS_COOKIE      => 'Cannot create JS cookies',
			$this->shieldon::REASON_EMPTY_REFERER        => 'Empty referrer',
			$this->shieldon::REASON_REACHED_LIMIT_DAY    => 'Daily limit reached',
			$this->shieldon::REASON_REACHED_LIMIT_HOUR   => 'Hourly limit reached',
			$this->shieldon::REASON_REACHED_LIMIT_MINUTE => 'Minutely limit reached',
			$this->shieldon::REASON_REACHED_LIMIT_SECOND => 'Secondly limit reached',
		];

		$types = [
			$this->shieldon::ACTION_DENY             => 'DENY',
			$this->shieldon::ACTION_ALLOW            => 'ALLOW',
			$this->shieldon::ACTION_TEMPORARILY_DENY => 'CAPTCHA',
		];

		$data['rule_list'] = $this->shieldon->driver->getAll('rule');

		$data['reason_mapping'] = $reasons;
		$data['type_mapping'] = $types;

		$this->renderPage('dashboard/table_rules', $data);
	}

	/**
	 * IP log table for current cycle.
	 *
	 * @param string
	 *
	 * @return void
	 */
	public function ipLogTable(): void
	{
		$data['ip_log_list'] = $this->shieldon->driver->getAll('log');

		$this->renderPage('dashboard/table_ip_logs', $data);
	}

	/**
	 * Session table for current cycle.
	 *
	 * @param string
	 *
	 * @return void
	 */
	public function sessionTable(): void
	{
		$data['session_list'] = $this->shieldon->driver->getAll('session');

		$data['is_session_limit'] = false;
		$data['session_limit_count'] = 0;
		$data['session_limit_period'] = 0;
		$data['online_count'] = 0;
		$data['expires'] = 0;

        
		$data['is_session_limit'] = true;
		$data['session_limit_count'] = 0;
		$data['session_limit_period'] = 0;
		$data['online_count'] = count($data['session_list']);
		$data['expires'] = (int) $data['session_limit_period'] * 60;

		$this->renderPage('dashboard/table_sessions', $data);
	}

	/**
	 * Load view file.
	 *
	 * @param string $page
	 * @param array  $data
	 * @param bool   $echo
	 *
	 * @return string|void
	 */
	private function loadView(string $page, array $data = [], $echo = false)
	{
		if (! defined('SHIELDON_VIEW')) {
			define('SHIELDON_VIEW', true);
		}

		$viewFilePath =  __DIR__ . '/../views/' . $page . '.php';
	
		if (! empty($data)) {
			extract($data);
		}

		$result = '';
	
		if (file_exists($viewFilePath)) {
			ob_start();
			require $viewFilePath;
			$result = ob_get_contents();
			ob_end_clean();
		}

		if ($echo) {
			echo $result;
			exit;
		}

		return $result;
	}

	/**
	 * Undocumented function
	 *
	 * @param string $page
	 * @param array $data
	 * @return void
	 */
	private function renderPage(string $page, array $data)
	{
		$content['page_url'] = $this->url();
		$content['inline_css'] =  file_get_contents(__DIR__ . '/../views/assets/css/admin-style.css');
		$content['title'] = $data['title'] ?? '';
		$content['content'] = $this->loadView($page, $data);

		$this->loadView('dashboard/template', $content, true);
	}

	/**
	 * Providing the Dasboard URLs.
	 *
	 * @param string $page Page tab.
	 * @param string $tab  Tab.
	 * @return string
	 */
	private function url(string $page = '', string $tab = '')
	{
		$httpProtocal = 'http://';

		if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') {
			$httpProtocal = 'https://';
		}

		$path = parse_url($httpProtocal . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'], PHP_URL_PATH);

		$url = $httpProtocal . $_SERVER['HTTP_HOST'] . $path;
		$soPage = (! empty($page)) ? '?so_page=' . $page : '';
		$soTab = (! empty($tab)) ? '&tab=' . $tab : '';

		return $url . $soPage . $soTab;
	}
}

