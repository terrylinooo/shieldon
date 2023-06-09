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

namespace Shieldon\Firewall\Panel;

use function Shieldon\Firewall\__;

use PDO;
use PDOException;
use Redis;
use Exception;
use function class_exists;
use function is_dir;
use function is_numeric;
use function is_string;
use function is_writable;
use function mkdir;
use function password_hash;
use function preg_split;
use function str_replace;
use function umask;

/*
 * @since 2.0.0
 */
trait ConfigMethodsTrait
{
    /**
     *   Public methods       | Desctiotion
     *  ----------------------|---------------------------------------------
     *                        | No public methods.
     *  ----------------------|---------------------------------------------
     */

    /**
     * Get a variable from configuration.
     *
     * @param string $field The field of the configuration.
     *
     * @return mixed
     */
    abstract public function getConfig(string $field);

    /**
     * Set a variable to the configuration.
     *
     * @param string $field The field of the configuration.
     * @param mixed  $value The vale of a field in the configuration.
     *
     * @return void
     */
    abstract public function setConfig(string $field, $value): void;

    /**
     * Response message to front.
     *
     * @param string $type The message status type. error|success
     * @param string $text The message body.
     *
     * @return void
     */
    abstract protected function pushMessage(string $type, string $text): void;

    /**
     * Handle to update settings.
     *
     * @param string $postKey  The key of the post data.
     * @param string $postData The value of the post data.
     * @param array $postParams The post data.
     * @return void
     */
    private function handleUpdatingSettings(string $postKey, string $postData, array $postParams): void
    {
        if ($postData === 'on') {
            $this->setConfig(str_replace('__', '.', $postKey), true);
        } elseif ($postData === 'off') {
            $this->setConfig(str_replace('__', '.', $postKey), false);
        } elseif ($postKey === 'ip_variable_source') {
            $this->setConfig('ip_variable_source.REMOTE_ADDR', false);
            $this->setConfig('ip_variable_source.HTTP_CF_CONNECTING_IP', false);
            $this->setConfig('ip_variable_source.HTTP_X_FORWARDED_FOR', false);
            $this->setConfig('ip_variable_source.HTTP_X_FORWARDED_HOST', false);
            $this->setConfig('ip_variable_source.' . $postData, true);
        } elseif ($postKey === 'dialog_ui__shadow_opacity') {
            $this->setConfig('dialog_ui.shadow_opacity', (string) $postData);
        } elseif ($postKey === 'admin__pass') {
            if (strlen($postParams['admin__pass']) < 58) {
                $this->setConfig('admin.pass', password_hash($postData, PASSWORD_BCRYPT));
            }
        } elseif (strpos($postKey, 'config__recipients') !== false) {
            // For example:
            // messengers__sendgrid__config__recipients
            // => messengers.sendgrid.config.recipients
            $this->setConfig(
                str_replace('__', '.', $postKey),
                preg_split(
                    '/\r\n|[\r\n]/',
                    $postData
                )
            );
        } elseif (is_numeric($postData)) {
            $this->setConfig(str_replace('__', '.', $postKey), (int) $postData);
        } else {
            $this->setConfig(str_replace('__', '.', $postKey), $postData);
        }
    }

    /**
     * Parse the POST fields and set them into configuration data structure.
     * Used for saveConfig method only.
     *
     * @param array $postParams The PSR-7 variable of $_POST
     *
     * @return void
     */
    protected function saveConfigPrepareSettings(array $postParams): void
    {
        foreach ($postParams as $postKey => $postData) {
            if (!is_string($postData)) {
                continue;
            }
            $this->handleUpdatingSettings($postKey, $postData, $postParams);
        }
    }

    /**
     * Check the settings of Action Logger.
     *
     * @param bool $result The result passed from previous check.
     *
     * @return bool
     */
    protected function saveConfigCheckActionLogger(bool $result): bool
    {
        if (!$result) {
            return false;
        }

        // Check Action Logger settings.
        $enableActionLogger = $this->getConfig('loggers.action.enable');

        // $actionLogDir = rtrim($this->getConfig('loggers.action.config.directory_path'), '\\/ ');
        $actionLogDir = $this->directory . '/action_logs';

        if ($enableActionLogger) {
            $this->setConfig('loggers.action.config.directory_path', $actionLogDir);

            if (!is_dir($actionLogDir)) {
                // @codeCoverageIgnoreStart
                $originalUmask = umask(0);
                mkdir($actionLogDir, 0777, true);
                umask($originalUmask);
                // @codeCoverageIgnoreEnd
            }

            if (!is_writable($actionLogDir)) {
                // @codeCoverageIgnoreStart
                $result = false;
                $this->pushMessage(
                    'error',
                    __(
                        'panel',
                        'error_logger_directory_not_writable',
                        'Action Logger requires the storage directory writable.'
                    )
                );
                // @codeCoverageIgnoreEnd
            }
        }

        return $result;
    }

    /**
     * Check the settings of iptables.
     *
     * @param bool $result The result passed from previous check.
     *
     * @return bool
     */
    protected function saveConfigCheckIptables(bool $result): bool
    {
        if (!$result) {
            return false;
        }

        // System firewall.
        $enableiptables = $this->getConfig('iptables.enable');

        // $iptablesWatchingFolder = rtrim($this->getConfig('iptables.config.watching_folder'), '\\/ ');
        $iptablesWatchingFolder = $this->directory . '/iptables';
        
        if ($enableiptables) {
            $this->setConfig('iptables.config.watching_folder', $iptablesWatchingFolder);

            if (!is_dir($iptablesWatchingFolder)) {
                // @codeCoverageIgnoreStart
                $originalUmask = umask(0);
                mkdir($iptablesWatchingFolder, 0777, true);
                umask($originalUmask);
                // @codeCoverageIgnoreEnd
            }
    
            // Create default log files.
            if (is_writable($iptablesWatchingFolder)) {
                fopen($iptablesWatchingFolder . '/iptables_queue.log', 'w+');
                fopen($iptablesWatchingFolder . '/ipv4_status.log', 'w+');
                fopen($iptablesWatchingFolder . '/ipv6_status.log', 'w+');
                fopen($iptablesWatchingFolder . '/ipv4_command.log', 'w+');
                fopen($iptablesWatchingFolder . '/ipv6_command.log', 'w+');

                return $result;
            }

            // @codeCoverageIgnoreStart
            $result = false;

            $this->pushMessage(
                'error',
                __(
                    'panel',
                    'error_ip6tables_directory_not_writable',
                    'iptables watching folder requires the storage directory writable.'
                )
            );
            // @codeCoverageIgnoreEnd
        }

        return $result;
    }

    /**
     * Check the settings of Data drivers.
     *
     * @param bool $result The result passed from previous check.
     *
     * @return bool
     */
    protected function saveConfigCheckDataDriver(bool $result): bool
    {
        if (!$result) {
            return false;
        }

        $type = $this->getConfig('driver_type');

        $methods = [
            'mysql'  => 'saveCofigCheckDataDriverMySql',
            'sqlite' => 'saveCofigCheckDataDriverSqlLite',
            'redis'  => 'saveCofigCheckDataDriverRedis',
            'file'   => 'saveCofigCheckDataDriverFile',
        ];

        $method = $methods[$type];
        $result = $this->{$method}($result);

        return $result;
    }

    /**
     * Check the settings of Data drivers.
     *
     * @param bool $result The result passed from previous check.
     *
     * @return bool
     */
    protected function saveCofigCheckDataDriverMySql(bool $result): bool
    {
        if (class_exists('PDO')) {
            $db = [
                'host'    => $this->getConfig('drivers.mysql.host'),
                'dbname'  => $this->getConfig('drivers.mysql.dbname'),
                'user'    => $this->getConfig('drivers.mysql.user'),
                'pass'    => $this->getConfig('drivers.mysql.pass'),
                'charset' => $this->getConfig('drivers.mysql.charset'),
            ];

            try {
                new PDO(
                    'mysql:host=' . $db['host'] . ';dbname=' . $db['dbname'] . ';charset=' . $db['charset'],
                    (string) $db['user'],
                    (string) $db['pass']
                );
            } catch (PDOException $e) {
                $result = false;

                $this->pushMessage(
                    'error',
                    __(
                        'panel',
                        'error_mysql_connection',
                        'Cannot connect to your MySQL database, please check your settings.'
                    )
                );
            }
            return $result;
        }

        // @codeCoverageIgnoreStart

        $result = false;

        $this->pushMessage(
            'error',
            __(
                'panel',
                'error_mysql_driver_not_supported',
                'Your system doesn’t support MySQL driver.'
            )
        );

        return $result;

        // @codeCoverageIgnoreEnd
    }

    /**
     * Check the settings of Data drivers.
     *
     * @param bool $result The result passed from previous check.
     *
     * @return bool
     */
    protected function saveCofigCheckDataDriverSqlLite(bool $result): bool
    {
        // $sqliteDir = rtrim($this->getConfig('drivers.sqlite.directory_path'), '\\/ ');
        $sqliteDir = $this->directory . '/data_driver_sqlite';

        $this->setConfig('drivers.sqlite.directory_path', $sqliteDir);

        $sqliteFilePath = $sqliteDir . '/shieldon.sqlite3';

        if (!is_dir($sqliteDir)) {
            // @codeCoverageIgnoreStart
            $originalUmask = umask(0);
            mkdir($sqliteDir, 0777, true);
            umask($originalUmask);
            // @codeCoverageIgnoreEnd
        }

        if (class_exists('PDO')) {
            try {
                new PDO('sqlite:' . $sqliteFilePath);

                // @codeCoverageIgnoreStart
            } catch (PDOException $e) {
                $this->pushMessage('error', $e->getMessage());
                $result = false;
            }

            // @codeCoverageIgnoreEnd

            if (!is_writable($sqliteFilePath)) {
                // @codeCoverageIgnoreStart
                $this->pushMessage(
                    'error',
                    __(
                        'panel',
                        'error_sqlite_directory_not_writable',
                        'SQLite data driver requires the storage directory writable.'
                    )
                );
                $result = false;
                // @codeCoverageIgnoreEnd
            }

            return $result;
        }

        // @codeCoverageIgnoreStart

        $result = false;

        $this->pushMessage(
            'error',
            __(
                'panel',
                'error_sqlite_driver_not_supported',
                'Your system doesn’t support SQLite driver.'
            )
        );

        return $result;

        // @codeCoverageIgnoreEnd
    }

    /**
     * Check the settings of Data drivers.
     *
     * @param bool $result The result passed from previous check.
     *
     * @return bool
     */
    protected function saveCofigCheckDataDriverRedis(bool $result): bool
    {
        if (class_exists('Redis')) {
            try {
                $redis = new Redis();
                $redis->connect(
                    (string) $this->getConfig('drivers.redis.host'),
                    (int) $this->getConfig('drivers.redis.port')
                );
                unset($redis);
            } catch (Exception $e) {
                $this->pushMessage('error', $e->getMessage());
                $result = false;
            }

            return $result;
        }

        // @codeCoverageIgnoreStart

        $result = false;

        $this->pushMessage(
            'error',
            __(
                'panel',
                'error_redis_driver_not_supported',
                'Your system doesn’t support Redis driver.'
            )
        );

        return $result;

        // @codeCoverageIgnoreEnd
    }

    /**
     * Check the settings of Data drivers.
     *
     * @param bool $result The result passed from previous check.
     *
     * @return bool
     */
    protected function saveCofigCheckDataDriverFile(bool $result): bool
    {
        //$fileDir = rtrim($this->getConfig('drivers.file.directory_path'), '\\/ ');
        $fileDir = $this->directory . '/data_driver_file';

        $this->setConfig('drivers.file.directory_path', $fileDir);

        if (!is_dir($fileDir)) {
            // @codeCoverageIgnoreStart
            $originalUmask = umask(0);
            mkdir($fileDir, 0777, true);
            umask($originalUmask);
            // @codeCoverageIgnoreEnd
        }

        if (!is_writable($fileDir)) {
            // @codeCoverageIgnoreStart
            $result = false;
            $this->pushMessage(
                'error',
                __(
                    'panel',
                    'error_file_directory_not_writable',
                    'File data driver requires the storage directory writable.'
                )
            );
            // @codeCoverageIgnoreEnd
        }

        return $result;
    }
}
