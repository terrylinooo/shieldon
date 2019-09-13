<?php declare(strict_types=1);
/*
 * This file is part of the Shieldon Firewall package, an enhanced package for Shieldon library.
 * It's lincese is exactly defferent to Shieldon package, pelase read the license 
 * information below.
 *
 * (c) Terry L. <contact@terryl.in>
 *
 * @author     Terry L. <contact@terryl.in>
 * @package    Shieldon
 * @subpackage Shieldon Firewall
 * @link       https://shieldon.io
 * @license    Free to use when reserving the credit link, see explanation below.
 *
 *                                  *** License ***
 *
 * Shieldon Firewall is free for both personal and commercial use If the Shieldon's credit link 
 * is displayed on every Shieldon-generated pages such as CAPTCHA page、password protection page 
 * and so on. If you are willing to remove the credit link, please purchase a commercail license 
 * from https://shieldon.io to support use make it better.
 */

namespace Shieldon;

use Shieldon\Shieldon;

use Shieldon\Component\Header;
use Shieldon\Component\Ip;
use Shieldon\Component\Rdns;
use Shieldon\Component\TrustedBot;
use Shieldon\Component\UserAgent;
use Shieldon\Container;
use Shieldon\Driver\FileDriver;
use Shieldon\Driver\MysqlDriver;
use Shieldon\Driver\RedisDriver;
use Shieldon\Driver\SqliteDriver;
use Shieldon\Log\ActionLogger;

use PDO;
use PDOException;
use Redis;
use RedisException;

use function count;
use function explode;
use function file_get_contents;
use function file_put_contents;
use function json_decode;
use function strpos;

/**
 * Managed Firewall.
 * 
 * @since 3.0.0
 */
class Firewall
{
    /**
     * Shieldon instance.
     *
     * @var object
     */
    private $shieldon;

    /**
     * Configuration data of Shieldon.
     *
     * @var array
     */
    protected $configuration;

    /**
     * If status is false and then Sheldon will stop working.
     *
     * @var boolean
     */
	private $status = true;
	
	/**
	 * The configuation file's path.
	 *
	 * @var string
	 */
	private $directory = '/tmp';

	/**
	 * The filename of the configuration file.
	 *
	 * @var string
	 */
	private $filename = 'config.firewall.json';

    /**
     * Constructor.
     */
    public function __construct(string $directory)
    {
		// Set to container.
		Container::set('firewall', $this);

        if ('' !== $directory) {
			$this->directory = rtrim($directory, '\\/');
		}

		if (! is_dir($this->directory)) {
            $originalUmask = umask(0);
            @mkdir($this->directory, 0777, true);
            umask($originalUmask);
		}

		if (! is_writable($this->directory)) {
			throw new RuntimeException('The directory usded by Firewall must be writable. (' . $this->directory . ')');
		}

		$configFilePath = $this->directory . '/' . $this->filename;

		if (! file_exists($configFilePath)) {
			$jsonString = file_get_contents(__DIR__ . '/../config/firewall.json');
		} else {
			$jsonString = file_get_contents($configFilePath);
		}

		$this->configuration = json_decode($jsonString, true);

		$this->shieldon = new Shieldon();

		$this->shieldon->managedByFirewall();

		$this->setDriver();
		
		$this->setChannel();

		$this->setIpSource();

		$this->setLogger();

		$this->setFilters();

		$this->setComponents();

		$this->setCaptchas();

		$this->setSessionLimit();

		$this->setCronJob();

		$this->setExcludedUrls();

		$this->status = $this->getOption('daemon');
	}

	/**
	 * Just, run!
	 *
	 * @return void
	 */
	public function run(): void
	{
		if ($this->status) {
		
			$result = $this->shieldon->run();

			if ($result !== $this->shieldon::RESPONSE_ALLOW) {

				if ($this->shieldon->captchaResponse()) {
					$this->shieldon->unban();
				}
				$this->shieldon->output(200);
			}
		}
	}

	/**
	 * Get the Shieldon instance.
	 *
	 * @return object
	 */
	public function getShieldonInstance(): object
	{
		return $this->shieldon;
	}

	/**
	 * Get the configuation settings.
	 *
	 * @return array
	 */
	public function getConfiguration(): array
	{
		return $this->configuration;
	}

    /**
     * Set the channel ID.
     *
     * @return void
     */
    protected function setChannel(): void
    {
        $channelId = $this->getOption('channel_id');

        if ($channelId) {
			$this->shieldon->setChannel($channelId);
        }
    }

    /**
     * Set a data driver for Shieldon use.
     *
     * @return void
     */
    protected function setDriver(): void
    {
        $driverType = $this->getOption('driver_type');

		switch ($driverType) {

            case 'reids':
            
                $redisSetting = $this->getOption('redis', 'drivers');

				try {

                    $host = '127.0.0.1';
                    $port = 6379;

                    if (! empty($redisSetting['host'])) {
                        $host = $redisSetting['host'];
                    }

                    if (! empty($redisSetting['port'])) {
                        $port = $redisSetting['port'];
                    }

					// Create a Redis instance.
					$redis = new Redis();
                    $redis->connect($host, $port);
                    
                    if (! empty($redisSetting['auth'])) {
                        $redis->auth($redisSetting['auth']);  
                    }

					// Use Redis data driver.
					$this->shieldon->setDriver(new RedisDriver($redis));

				} catch(RedisException $e) {
                    $this->status = false;

					echo $e->getMessage();
				}

				break;

            case 'file':
            
                $fileSetting = $this->getOption('file', 'drivers');

				if (empty($fileSetting['directory_path'])) {
                    $fileSetting['directory_path'] = $this->directory;
                    $this->status = false;
                }

				// Use File data driver.
				$this->shieldon->setDriver(new FileDriver($fileSetting['directory_path']));

				break;

            case 'sqlite':
            
                $sqliteSetting = $this->getOption('sqlite', 'drivers');

				if (empty($sqliteSetting['directory_path'])) {
                    $sqliteSetting['directory_path'] = '';
                    $this->status = false;
                }

				try {
					
					// Specific the sqlite file location.
					$sqliteLocation = $sqliteSetting . '/shieldon.sqlite3';

					// Create a PDO instance.
					$pdoInstance = new PDO('sqlite:' . $sqliteLocation);

					// Use Sqlite data driver.
					$this->shieldon->setDriver(new SqliteDriver($pdoInstance));
	
				} catch(PDOException $e) {
					echo $e->getMessage();
				}

				break;

			case 'mysql':
			default:

                $mysqlSetting = $this->getOption('mysql', 'drivers');

				try {

					// Create a PDO instance.
					$pdoInstance = new PDO(
                        'mysql:host=' 
                            . $mysqlSetting['host']   . ';dbname=' 
                            . $mysqlSetting['dbname'] . ';charset=' 
                            . $mysqlSetting['charset']
						, $mysqlSetting['user']
						, $mysqlSetting['pass']
					);

					// Use MySQL data driver.
					$this->shieldon->setDriver(new MysqlDriver($pdoInstance));

				} catch(PDOException $e) {
					echo $e->getMessage();
                }
            // end switch.
		}
    }

    /**
     * Set up the action logger.
     *
     * @return void
     */
    protected function setLogger(): void
    {
		$loggerSetting = $this->getOption('action', 'loggers');
		
		if ($loggerSetting['enable']) {
			if (! empty($loggerSetting['config']['directory_path'])) {
				$this->shieldon->setLogger(new ActionLogger($loggerSetting['config']['directory_path']));
			}
		}
    }

	/**
	 * If you use CDN, please choose the real IP source.
	 *
	 * @return void
	 */
    protected function setIpSource(): void
    {
		$ipSourceType = $this->getOption('ip_variable_source');

		if ($ipSourceType['REMOTE_ADDR']) {
			$this->shieldon->setIp($_SERVER['REMOTE_ADDR']);

		// Cloudflare
		} elseif ($ipSourceType['HTTP_CF_CONNECTING_IP']) {
			$this->shieldon->setIp($_SERVER['HTTP_CF_CONNECTING_IP']);

		// Google Cloud CDN, Google Load-balancer, AWS.
		} elseif ($ipSourceType['HTTP_X_FORWARDED_FOR']) {
			$this->shieldon->setIp($_SERVER['HTTP_X_FORWARDED_FOR']);

		// KeyCDN, or other CDN providers not listed here.
		} elseif ($ipSourceType['HTTP_X_FORWARDED_HOST']) {
			$this->shieldon->setIp($_SERVER['HTTP_X_FORWARDED_HOST']);

		// Fallback.
		} else {
			$this->shieldon->setIp($_SERVER['REMOTE_ADDR']);
		}
    }

    /**
     * Set the filiters.
     *
     * @return void
     */
    protected function setFilters(): void
    {
		$sessionSetting = $this->getOption('session', 'filters');
		$cookieSetting = $this->getOption('cookie', 'filters');
		$refererSetting = $this->getOption('referer', 'filters');
		$frequencySetting = $this->getOption('frequency', 'filters');

		$filterConfig = [
			'session'   => $sessionSetting['enable'],
			'cookie'    => $cookieSetting['enable'],
			'referer'   => $refererSetting['enable'],
			'frequency' => $frequencySetting['enable'],
		];

		$this->shieldon->setFilters($filterConfig);

		$this->shieldon->setProperty('limit_unusual_behavior', [
			'session' => $sessionSetting['config']['quota'] ?? 5,
			'cookie'  => $cookieSetting['config']['quota'] ?? 5,
			'referer' => $refererSetting['config']['quota'] ?? 5,
		]);

		if ($frequencySetting['enable']) {

			$frequencyQuota = [
				's' => $frequencySetting['config']['quota_s'] ?? 2,
				'm' => $frequencySetting['config']['quota_m'] ?? 10,
				'h' => $frequencySetting['config']['quota_h'] ?? 30,
				'd' => $frequencySetting['config']['quota_d'] ?? 60,
			];

			$this->shieldon->setProperty('time_unit_quota', $frequencyQuota);
		}

		if ($cookieSetting['enable']) {

			$cookieName = $cookieSetting['config']['cookie_name'] ?? 'ssjd';
			$cookieDomain = $cookieSetting['config']['cookie_domain'] ?? '';
			$cookieValue = $cookieSetting['config']['cookie_value'] ?? '1';
	
			$this->shieldon->setProperty('cookie_name', $cookieName);
			$this->shieldon->setProperty('cookie_domain', $cookieDomain);
			$this->shieldon->setProperty('cookie_value', $cookieValue);
		}

		if ($refererSetting['enable']) {
			$this->shieldon->setProperty('interval_check_referer', $refererSetting['config']['time_buffer']);
		}

		if ($sessionSetting['enable']) {
			$this->shieldon->setProperty('interval_check_session', $sessionSetting['config']['time_buffer']);
		}
    }

    /**
     * Set the components.
     *
     * @return void
     */
    protected function setComponents(): void
    {
		$ipSetting = $this->getOption('ip', 'components');
		$rdnsSetting = $this->getOption('rdns', 'components');
		$headerSetting = $this->getOption('header', 'components');
		$userAgentSetting = $this->getOption('user_agent', 'components');
		$trustedBotSetting = $this->getOption('trusted_bot', 'components');

		if ($ipSetting['enable']) {
			$componentIp = new Ip();
			$this->shieldon->setComponent($componentIp);
			$this->ipManager();
		}

		if ($trustedBotSetting['enable']) {
			$componentTrustedBot = new TrustedBot();

			// This component will only allow popular search engline.
			// Other bots will go into the checking process.
			$this->shieldon->setComponent($componentTrustedBot);
		}

		if ($headerSetting['enable']) {
			$componentHeader = new Header();

			// Deny all vistors without common header information.
			if ($headerSetting['strict_mode']) {
				$componentHeader->setStrict( true );
			}

			$this->shieldon->setComponent($componentHeader);
		}

		if ($userAgentSetting['enable']) {
			$componentUserAgent = new UserAgent();

			// Deny all vistors without user-agent information.
			if ($userAgentSetting['strict_mode']) {
				$componentUserAgent->setStrict( true );
			}

			$this->shieldon->setComponent($componentUserAgent);
		}

		if ($rdnsSetting['enable']) {
			$componentRdns = new Rdns();

			// Visitors with empty RDNS record will be blocked.
            // IP resolved hostname (RDNS) and IP address must conform with each other.
			if ($rdnsSetting['strict_mode']) {
				$componentRdns->setStrict( true );
			}

			$this->shieldon->setComponent($componentRdns);
		}
    }

    /**
     * Set the Captcha modules.
     *
     * @return void
     */
    protected function setCaptchas(): void
    {
		$recaptchaSetting = $this->getOption('recaptcha', 'captcha_modules');
		$imageSetting = $this->getOption('image', 'captcha_modules');

		if ($recaptchaSetting['enable']) {

			$googleRecaptcha = [
				'key'     => $recaptchaSetting['config']['site_key'],
				'secret'  => $recaptchaSetting['config']['secret_key'],
				'version' => $recaptchaSetting['config']['version'],
				'lang'    => $recaptchaSetting['config']['lang'],
			];

			$this->shieldon->setCaptcha(new Recaptcha($googleRecaptcha));
		}

		if ($imageSetting['enable']) {

			$type = $imageSetting['config']['type'] ?? 'alnum';
			$length = $imageSetting['config']['length'] ?? 8;

			switch ($type) {
				case 'numeric':
					$imageCaptchaConfig['pool'] = '0123456789';
					break;

				case 'alpha':
					$imageCaptchaConfig['pool'] = '0123456789abcdefghijklmnopqrstuvwxyz';
					break;

				case 'alnum':
				default:
					$imageCaptchaConfig['pool'] = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
			}

			$imageCaptchaConfig['word_length'] = $length;

			$this->shieldon->setCaptcha(new ImageCaptcha($imageCaptchaConfig));
		}
    }

    /**
     * Set the online session limit.
     *
     * @return void
     */
    protected function setSessionLimit(): void
    {
		$sessionLimitSetting = $this->getOption('online_session_limit');

        if ($sessionLimitSetting['enable']) {

			$onlineUsers = $sessionLimitSetting['config']['count'] ?? 100;
			$alivePeriod = $sessionLimitSetting['config']['period'] ?? 300;

			$this->shieldon->limitSession($onlineUsers, $alivePeriod);
		}
    }

	/**
	 * Set the cron job.
	 * This is triggered by the pageviews, not system cron job.
	 *
	 * @return void
	 */
	private function setCronJob(): void 
	{
		$cronjobSetting = $this->getOption('reset_circle', 'cronjob');

		if ($cronjobSetting['enable']) {

			$nowTime = time();

			$lastResetTime = $cronjobSetting['config']['last_update'];

			if (! empty($lastResetTime) ) {
				$lastResetTime = strtotime($lastResetTime);
			} else {
				$lastResetTime = strtotime(date('Y-m-d 00:00:00'));
			}

			if (($nowTime - $lastResetTime) > $cronjobSetting['config']['period']) {

				// Update new reset time.
				$this->updateOption('cronjob.reset_circle.config.last_update', $lastResetTime);

				// Remove all logs.
				$this->shieldon->driver->rebuild();
			}
		}
	}

	/**
	 * Set the URLs that want to be excluded from Shieldon protection.
	 *
	 * @return void
	 */
	protected function setExcludedUrls() 
	{
		$excludedUrls = $this->getOption('excluded_urls');
		$list = array_column($excludedUrls, 'url');

		$this->shieldon->setExcludedUrls($list);
	}

	/**
	 * IP manager.
	 */
	protected function ipManager()
	{
		$ipList = $this->getOption('ip_manager');

		$allowedList = [];
		$deniedList = [];

		foreach ($ipList as $ip) {

			if (0 === strpos($this->shieldon->getCurrentUrl(), $ip['url']) ) {

				if ('allow' === $ip['rule']) {
					$allowedList[] = $ip['ip'];
				}

				if ('deny' === $ip['rule']) {
					$deniedList[] = $ip['ip'];
				}
			}
		}

		if (! empty($allowedList)) {
			$this->shieldon->component['Ip']->setAllowedList($allowedList);
		}

		if (! empty($deniedList)) {
			$this->shieldon->component['Ip']->setDeniedList($deniedList);
		}
	}

	/**
     * Get options from the configuration file.
     *
     * @param string $option
     * @param string $section
     *
     * @return mixed
     */
    private function getOption(string $option, string $section = '')
    {
        if (! empty($this->configuration[$section][$option])) {
            return $this->configuration[$section][$option];
        }

        if (! empty($this->configuration[$option]) && $section === '') {
            return $this->configuration[$option];
        }

        return false;
	}

	/**
	 * Save data to the configuration variable.
	 *
	 * @param string $arrayLevelString
	 * @param string $assignValue
	 *
	 * @return void
	 */
	private function updateOption($arrayLevelString = '', $assignValue = ''): void
	{
		$i = explode('.', $arrayLevelString);
		$count = count($i);
		$isUpdateFile = true;

		switch ($count) {
			case 1:
				$this->configuration[$i[0]] = $assignValue;
				break;

			case 2:
				$this->configuration[$i[0]][$i[1]] = $assignValue;
				break;

			case 3:
				$this->configuration[$i[0]][$i[1]][$i[2]] = $assignValue;
				break;

			case 4:
				$this->configuration[$i[0]][$i[1]][$i[2]][$i[3]] = $assignValue;
				break;

			case 5:
				$this->configuration[$i[0]][$i[1]][$i[2]][$i[3]][$i[4]] = $assignValue;
				break;

			default:
				$isUpdateFile = false;
		}

		if ($isUpdateFile) {
			$this->updateConfigurationFile();
		}
	}

	/**
	 * Update configuration file.
	 *
	 * @return void
	 */
	private function updateConfigurationFile()
	{
		file_put_contents($this->configFilePath, json_encode($this->configuration));
	}
}
