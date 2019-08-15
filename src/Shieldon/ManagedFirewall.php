<?php declare(strict_types=1);
/*
 * This file is part of the Shieldon package.
 *
 * (c) Terry L. <contact@terryl.in>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 * 
 */

namespace Shieldon;

use Shieldon\Shieldon;
use Shieldon\Driver\RedisDriver;
use Shieldon\Driver\FileDriver;
use Shieldon\Driver\SqliteDriver;
use Shieldon\Driver\MysqlDriver;
use Shieldon\Log\ActionLogger;
use Shieldon\Component\Ip;

use PDO;
use PDOException;
use Redis;
use RedisException;
use Exception;

use function file_get_contents;
use function json_decode;

/**
 * For storing Shieldon instances.
 * 
 * @since 3.0.0
 */
class ManagedFirewall
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
     * Constructor.
     * 
     * @return void
     */
    public function __construct(string $configFilePath, Shieldon &$instance)
    {
        if (! file_exists($configFilePath)) {
            throw new Exception('Configuration file is missing.');
        }

        $jsonString = file_get_contents($configFilePath);

        $this->configuration = json_decode($jsonString, true);
        $this->shieldon =& $instance;

        $this->setDriver();
		$this->setChannel();
		$this->setIpSource();
		$this->setLogger();
		$this->setFilters();
		$this->setComponents();
		

		
    }

    /**
     * Get options from the configuration file.
     *
     * @param string $option
     * @param string $section
     *
     * @return mixed
     */
    protected function getOption(string $option, string $section = '')
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
     * Set the channel ID.
     *
     * @return void
     */
    public function setChannel(): void
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
    public function setDriver(): void
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
                    $fileSetting['directory_path'] = '';
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
    public function setLogger(): void
    {
        $loggerSetting = $this->getOption('action', 'loggers');

        if (! empty($loggerSetting['directory_path'])) {
            $this->shieldon->setLogger(new ActionLogger($loggerSetting['directory_path']));
        }
    }

	/**
	 * If you use CDN, please choose the real IP source.
	 *
	 * @return void
	 */
    public function setIpSource()
    {
		$ipSourceType = $this->getOption('ip_source');

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
    public function setFilters()
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

		if ($frequencySetting['enable']) {

			$s = $frequencySetting['config']['quota_s'];
			$m = $frequencySetting['config']['quota_m'];
			$h = $frequencySetting['config']['quota_h'];
			$d = $frequencySetting['config']['quota_d'];
	
			$quota['s'] = (is_numeric($s) && ! empty($s)) ? (int) $s : 2;
			$quota['m'] = (is_numeric($m) && ! empty($m)) ? (int) $m : 10;
			$quota['h'] = (is_numeric($h) && ! empty($h)) ? (int) $h : 30;
			$quota['d'] = (is_numeric($d) && ! empty($d)) ? (int) $d : 60;
	
			$this->shieldon->setProperty('time_unit_quota', $quota);
		}
    }

    /**
     * Set the components.
     *
     * @return void
     */
    public function setComponents()
    {
        $componentIp = new Ip();

		$this->shieldon->setComponent($componentIp);

		$this->ip_manager();

		/**
		 * Load "Trusted Bot" component.
		 */
		if ( 'yes' === wpso_get_option( 'enable_component_trustedbot', 'shieldon_component' ) ) {

			// This component will only allow popular search engline.
			// Other bots will go into the checking process.
			$component_trustedbot = new \Shieldon\Component\TrustedBot();

			$this->shieldon->setComponent( $component_trustedbot );
		}

		/**
		 * Load "Header" component.
		 */
		if ( 'yes' === wpso_get_option( 'enable_component_header', 'shieldon_component' ) ) {

			$component_header = new \Shieldon\Component\Header();

			// Deny all vistors without common header information.
			if ( 'yes' === wpso_get_option( 'header_strict_mode', 'shieldon_component' ) ) {
				$component_header->setStrict( true );
			}

			$this->shieldon->setComponent( $component_header );
		}

		/**
		 * Load "User-agent" component.
		 */
		if ( 'yes' === wpso_get_option( 'enable_component_agent', 'shieldon_component' ) ) {

			$component_agent = new \Shieldon\Component\UserAgent();

			// Visitors with empty user-agent information will be blocked.
			if ( 'yes' === wpso_get_option( 'agent_strict_mode', 'shieldon_component' ) ) {
				$component_agent->setStrict( true );
			}

			$this->shieldon->setComponent( $component_agent );
		}

		/**
		 * Load "Rdns" component.
		 */
		if ( 'yes' === wpso_get_option( 'enable_component_rdns', 'shieldon_component' ) ) {

			$component_rdns = new \Shieldon\Component\Rdns();

			// Visitors with empty Rdns record will be blocked.
            // IP resolved hostname (Rdns) and IP address must match.
			if ( 'yes' === wpso_get_option( 'rdns_strict_mode', 'shieldon_component' ) ) {
				$component_rdns->setStrict( true );
			}

			$this->shieldon->setComponent( $component_rdns );
		}
    }

    /**
     * Set the Captcha modules.
     *
     * @return void
     */
    public function setCaptchas()
    {
        if ( 'yes' === wpso_get_option( 'enable_captcha_google', 'shieldon_captcha' ) ) {

			$google_captcha_config['key']    = wpso_get_option( 'google_recaptcha_key', 'shieldon_captcha' );
			$google_captcha_config['secret'] = wpso_get_option( 'google_recaptcha_secret', 'shieldon_captcha' );
			$google_captcha_config['verion'] = wpso_get_option( 'google_recaptcha_version', 'shieldon_captcha' );
			$google_captcha_config['lang']   = wpso_get_option( 'google_recaptcha_version', 'shieldon_captcha' );

			$captcha_google = new \Shieldon\Captcha\Recaptcha( $google_captcha_config );

			$this->shieldon->setCaptcha( $captcha_google );
		}

		if ( 'yes' === wpso_get_option( 'enable_captcha_image', 'shieldon_captcha' ) ) {

			$image_captcha_config['word_length'] = wpso_get_option( 'image_captcha_length', 'shieldon_captcha' );

			$image_captcha_type = wpso_get_option( 'image_captcha_type', 'shieldon_captcha' );

			switch ($image_captcha_type) {
				case 'numeric':
					$image_captcha_config['pool'] = '0123456789';
					break;

				case 'alpha':
					$image_captcha_config['pool'] = '0123456789abcdefghijklmnopqrstuvwxyz';
					break;

				case 'alnum':
				default:
					$image_captcha_config['pool'] = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
			}
			
			$captcha_image = new \Shieldon\Captcha\ImageCaptcha( $image_captcha_config );
			$this->shieldon->setCaptcha( $captcha_image );
		}
    }

    /**
     * Set the online session limit.
     *
     * @return void
     */
    public function setSessionLimit()
    {
        if ( 'yes' === wpso_get_option( 'enable_online_session_limit', 'shieldon_daemon' ) ) {

			$online_users = wpso_get_option( 'session_limit_count', 'shieldon_daemon' );
			$alive_period = wpso_get_option( 'session_limit_period', 'shieldon_daemon' );
		
			$online_users = ( is_numeric( $online_users ) && ! empty( $online_users ) ) ? ( (int) $online_users ) : 100;
			$alive_period = ( is_numeric( $alive_period ) && ! empty( $alive_period ) ) ? ( (int) $alive_period * 60 )  : 300;

			$this->shieldon->limitSession( $online_users, $alive_period );
		}
    }

    private function reset_logs() {

		if ( 'yes' === wpso_get_option( 'data_reset_circle', 'shieldon_daemon' ) ) {

			$now_time = time();

			$last_reset_time = get_option( 'wpso_last_reset_time' );

			if ( empty( $last_reset_time ) ) {
				$last_reset_time = strtotime( date('Y-m-d 00:00:00') );
			} else {
				$last_reset_time = (int) $last_reset_time;
			}

			if ( ( $now_time - $last_reset_time ) > 86400 ) {
				$last_reset_time = strtotime( date('Y-m-d 00:00:00') );

				// Recond new reset time.
				update_option( 'wpso_last_reset_time', $last_reset_time );

				// Remove all logs.
				$this->shieldon->driver->rebuild();
			}
		}
	}

    private function is_excluded_list() {

		$list = wpso_get_option( 'excluded_urls', 'shieldon_exclusion' );

		if ( ! empty( $list ) ) {
			$urls = explode(PHP_EOL, $list);

			foreach ($urls as $url) {
				if ( false !== strpos( $this->current_url, $url ) ) {
					return true;
				}
			}
		}

		// Login page.
		if ( 'yes' === wpso_get_option( 'excluded_page_login', 'shieldon_exclusion' ) ) {
			if ( false !== strpos( $this->current_url, 'wp-login.php' ) ) {
				return true;
			}
		}

		// Signup page.
		if ( 'yes' === wpso_get_option( 'excluded_page_signup', 'shieldon_exclusion' ) ) {
			if ( false !== strpos( $this->current_url, 'wp-signup.php' ) ) {
				return true;
			}
		}

		// XML RPC.
		if ( 'yes' === wpso_get_option( 'excluded_page_xmlrpc', 'shieldon_exclusion' ) ) {
			if ( false !== strpos( $this->current_url, 'xmlrpc.php' ) ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * IP manager.
	 */
	private function ip_manager() {

		if ( false !== strpos( $this->current_url, 'wp-login.php' ) ) {

			// Login page.
			$login_whitelist = wpso_get_option( 'ip_login_whitelist', 'shieldon_ip_login' );
			$login_blacklist = wpso_get_option( 'ip_login_blacklist', 'shieldon_ip_login' );
			$login_deny_all  = wpso_get_option( 'ip_login_deny_all', 'shieldon_ip_login' );

			if ( ! empty( $login_whitelist ) ) {
				$whitelist = explode(PHP_EOL, $login_whitelist );
				$this->shieldon->component['Ip']->setAllowedList( $whitelist );
			}

			if ( ! empty( $login_blacklist ) ) {
				$blacklist = explode(PHP_EOL, $login_blacklist );
				$this->shieldon->component['Ip']->setDeniedList( $blacklist );
			}

			$passcode    = wpso_get_option( 'deny_all_passcode', 'shieldon_ip_login' );
			$is_passcode = isset( $_GET[ $passcode ] ) ? true : false;

			if ($is_passcode) {
				$_SESSION[ $passcode ] = true;
			} else {
				if ( isset( $_SESSION[ $passcode ] ) ) {
					$is_passcode = true;
				}
			}

			if ( ! $is_passcode && 'yes' === $login_deny_all ) {
				$this->shieldon->component['Ip']->denyAll();
			}

		} elseif ( false !== strpos( $this->current_url, 'wp-signup.php' ) ) {

			// Signup page.
			$signup_whitelist = wpso_get_option( 'ip_signup_whitelist', 'shieldon_ip_signup' );
			$signup_blacklist = wpso_get_option( 'ip_signup_blacklist', 'shieldon_ip_signup' );
			$signup_deny_all  = wpso_get_option( 'ip_signup_deny_all', 'shieldon_ip_signup' );

			if ( ! empty( $signup_whitelist ) ) {
				$whitelist = explode(PHP_EOL, $signup_whitelist );
				$this->shieldon->component['Ip']->setAllowedList( $whitelist );
			}

			if ( ! empty( $signup_blacklist ) ) {
				$blacklist = explode(PHP_EOL, $signup_blacklist );
				$this->shieldon->component['Ip']->setDeniedList( $blacklist );
			}

			if ( 'yes' === $signup_deny_all ) {
				$this->shieldon->component['Ip']->denyAll();
			}

		} elseif ( false !== strpos( $this->current_url, 'xmlrpc.php' ) ) {

			// XML RPC.
			$xmlrpc_whitelist = wpso_get_option( 'ip_xmlrpc_whitelist', 'shieldon_ip_xmlrpc' );
			$xmlrpc_blacklist = wpso_get_option( 'ip_xmlrpc_blacklist', 'shieldon_ip_xmlrpc' );
			$xmlrpc_deny_all  = wpso_get_option( 'ip_xmlrpc_deny_all', 'shieldon_ip_xmlrpc' );

			if ( ! empty( $xmlrpc_whitelist ) ) {
				$whitelist = explode(PHP_EOL, $xmlrpc_whitelist );
				$this->shieldon->component['Ip']->setAllowedList( $whitelist );
			}

			if ( ! empty( $xmlrpc_blacklist ) ) {
				$blacklist = explode(PHP_EOL, $xmlrpc_blacklist );
				$this->shieldon->component['Ip']->setDeniedList( $blacklist );
			}

			if ( 'yes' === $xmlrpc_deny_all ) {
				$this->shieldon->component['Ip']->denyAll();
			}

		} else {

			// Global.
			$global_whitelist = wpso_get_option( 'ip_global_whitelist', 'shieldon_ip_global' );
			$global_blacklist = wpso_get_option( 'ip_global_blacklist', 'shieldon_ip_global' );
			$global_deny_all  = wpso_get_option( 'ip_global_deny_all', 'shieldon_ip_global' );

			if ( ! empty( $global_whitelist ) ) {
				$whitelist = explode(PHP_EOL, $global_whitelist );
				$this->shieldon->component['Ip']->setAllowedList( $whitelist );
			}

			if ( ! empty( $global_blacklist ) ) {
				$blacklist = explode(PHP_EOL, $global_blacklist );
				$this->shieldon->component['Ip']->setDeniedList( $blacklist );
			}

			if ( 'yes' === $global_deny_all ) {
				$this->shieldon->component['Ip']->denyAll();
			}
		}
	}

    /*

    $this->set_driver();           // Set Shieldon data driver to store logs.
    $this->reset_logs();           // Clear all logs if new data cycle should be started.
    $this->set_logger();           // Set Action Logger.
    $this->set_frequency_check();  // Set Frequancy check. (settings)
    $this->set_filters();          // Set filters.
    $this->set_component();        // Set Shieldon components.
    $this->set_captcha();          // Set Shieldon CAPTCHA instances.
    $this->set_session_limit();    // Set online session limit settings.
    */
}
