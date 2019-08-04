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

use Shieldon\Driver\DriverProvider;
use Shieldon\Log\LogParser;

/**
 * Display a simple statistics dashboard for Shieldon users.
 */
class Dashboard
{
	// Reason codes (allow)
	const REASON_IS_SEARCH_ENGINE = 100;
	const REASON_IS_GOOGLE = 101;
	const REASON_IS_BING = 102;
	const REASON_IS_YAHOO = 103;

	// Reason codes (deny)
	const REASON_TOO_MANY_SESSIONS = 1;
	const REASON_TOO_MANY_ACCESSES = 2;
	const REASON_EMPTY_JS_COOKIE = 3;
	const REASON_EMPTY_REFERER = 4;

	const REASON_REACHED_LIMIT_DAY = 11;
	const REASON_REACHED_LIMIT_HOUR = 12;
	const REASON_REACHED_LIMIT_MINUTE = 13;
	const REASON_REACHED_LIMIT_SECOND = 14;

	const REASON_MANUAL_BAN = 99;

	// Action codes.
	const ACTION_DENY = 0;
	const ACTION_ALLOW = 1;
	const ACTION_TEMPORARILY_DENY = 2;

	/**
	 * Shieldon driver instance.
	 *
	 * @var object
	 */
	protected $driver;

	/**
	 * Shieldon logParser instance.
	 *
	 * @var object
	 */
	protected $parser;

	/**
	 * Constructor.
	 *
	 * @param DriverProvider $driver
	 * @param LogParser      $logParser
	 */
	public function __construct(DriverProvider $driver, LogParser $logParser) 
	{
		$this->driver = $driver;
		$this->parser = $logParser;
	}

	/**
	 * Dsiplay dashboard.
	 *
	 * @return void
	 */
	public function dashboard(): void
	{
		$tab = 'today';

		if (! empty($_GET['tab'])) {
			$tab = htmlspecialchars($_GET['tab']);
		}

		

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

			$actionCode['temporarily_ban'] = self::ACTION_TEMPORARILY_DENY;
			$actionCode['permanently_ban'] = self::ACTION_DENY;
			$actionCode['allow'] = self::ACTION_ALLOW;

			switch ($action) {
				case 'temporarily_ban':
				case 'permanently_ban':
				case 'allow':
					$logData['log_ip'] = $ip;
					$logData['ip_resolve'] = gethostbyaddr($ip);
					$logData['time'] = time();
					$logData['type'] = $actionCode[$action];
					$logData['reason'] = self::REASON_MANUAL_BAN;

					$this->driver->save($ip, $logData, 'rule');
					break;

				case 'remove':
					$this->driver->delete($ip, 'rule');
					break;
			}
		}

		$reasons = [
			self::REASON_MANUAL_BAN           => 'Added manually by administrator',
			self::REASON_IS_SEARCH_ENGINE     => 'Search engine bot',
			self::REASON_IS_GOOGLE            => 'Google bot',
			self::REASON_IS_BING              => 'Bing bot',
			self::REASON_IS_YAHOO             => 'Yahoo bot',
			self::REASON_TOO_MANY_SESSIONS    => 'Too many sessions',
			self::REASON_TOO_MANY_ACCESSES    => 'Too many accesses',
			self::REASON_EMPTY_JS_COOKIE      => 'Cannot create JS cookies',
			self::REASON_EMPTY_REFERER        => 'Empty referrer',
			self::REASON_REACHED_LIMIT_DAY    => 'Daily limit reached',
			self::REASON_REACHED_LIMIT_HOUR   => 'Hourly limit reached',
			self::REASON_REACHED_LIMIT_MINUTE => 'Minutely limit reached',
			self::REASON_REACHED_LIMIT_SECOND => 'Secondly limit reached',
		];

		$types = [
			self::ACTION_DENY             => 'DENY',
			self::ACTION_ALLOW            => 'ALLOW',
			self::ACTION_TEMPORARILY_DENY => 'CAPTCHA',
		];

		$data['rule_list'] = $this->driver->getAll('rule');
		$data['reason_mapping'] = $reasons;
		$data['type_mapping'] = $types;
		$data['last_reset_time'] = '';

		$this->loadView('dashboard/table_rules', $data);
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
		$data['ip_log_list'] = $this->driver->getAll('log');
		$data['last_reset_time'] = '';

		$this->loadView('dashboard/table_ip_logs', $data);
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
		$data['session_list'] = $this->driver->getAll('session');

		$data['is_session_limit'] = false;
		$data['session_limit_count'] = 0;
		$data['session_limit_period'] = 0;
		$data['online_count'] = 0;
		$data['expires'] = 0;

        $reflection = new \ReflectionObject(self);
        $t = $reflection->getProperty('isLimitSession');
        $t->setAccessible(true);
        $isLimitSession = $t->getValue(self);

		if (! empty($isLimitSession)) {
			$data['is_session_limit'] = true;
			$data['session_limit_count'] = $isLimitSession[0];
			$data['session_limit_period'] = $isLimitSession[1];
			$data['online_count'] = count($data['session_list']);
			$data['expires'] = (int) $data['session_limit_period'] * 60;
		}

		$data['last_reset_time'] = '';

		$this->loadView('dashboard/table_sessions', $data);
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
		$soPage = '?so_page=' . $page;
		$soTab = (! empty($tab)) ? '&tab=' . $tab : '';

		return $url . $soPage . $soTab;
	}
}

