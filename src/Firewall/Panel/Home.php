<?php
/*
 * This file is part of the Shieldon package.
 *
 * (c) Terry L. <contact@terryl.in>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Shieldon\Firewall\Panel;

use Psr\Http\Message\ResponseInterface;
use Shieldon\Firewall\Panel\BaseController;
use ReflectionObject;
use function Shieldon\Firewall\get_request;
use function Shieldon\Firewall\unset_superglobal;
use function Shieldon\Firewall\__;

/**
 * Home
 */
class Home extends BaseController
{
    /**
     * Constructor
     */
    public function __construct() 
    {
        parent::__construct();
    }

    /**
     * Default entry
     *
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function index(): ResponseInterface
    {
        return $this->overview();
    }

    /**
     * Overview
     *
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function overview(): ResponseInterface
    {
        $postParams = get_request()->getParsedBody();

        if (isset($postParams['action_type'])) {

            switch ($postParams['action_type']) {

                case 'reset_data_circle':
                    $this->setConfig('cronjob.reset_circle.config.last_update', date('Y-m-d H:i:s'));
                    $this->kernel->driver->rebuild();
                    sleep(2);

                    unset_superglobal('action_type', 'post');

                    $this->saveConfig();

                    $this->pushMessage('success',
                        __(
                            'panel',
                            'reset_data_circle',
                            'Data circle tables have been reset.'
                        )
                    );
                    break;

                case 'reset_action_logs':
                    $this->kernel->logger->purgeLogs();
                    sleep(2);

                    $this->pushMessage('success',
                        __(
                            'panel',
                            'reset_action_logs',
                            'Action logs have been removed.'
                        )
                    );
                    break;

                default:
            }
        }

        /*
        |--------------------------------------------------------------------------
        | Logger
        |--------------------------------------------------------------------------
        |
        | All logs were recorded by ActionLogger.
        | Get the summary information from those logs.
        |
        */

        $data['action_logger'] = false;

        if (!empty($this->kernel->logger)) {
            $loggerInfo = $this->kernel->logger->getCurrentLoggerInfo();
            $data['action_logger'] = true;
        }

        $data['logger_started_working_date'] = 'No record';
        $data['logger_work_days'] = '0 day';
        $data['logger_total_size'] = '0 MB';

        if (!empty($loggerInfo)) {

            $i = 0;
            ksort($loggerInfo);

            foreach ($loggerInfo as $filename => $size) {
                $filename = (string) $filename;
                if (false === strpos($filename, '.json')) {
                    if (0 === $i) {
                        $data['logger_started_working_date'] = date('Y-m-d', strtotime($filename));
                    }
                    $i += (int) $size;
                }
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

        // Data circle.
        $data['rule_list'] = $this->kernel->driver->getAll('rule');
        $data['ip_log_list'] = $this->kernel->driver->getAll('filter_log');
        $data['session_list'] = $this->kernel->driver->getAll('session');

        /*
        |--------------------------------------------------------------------------
        | Shieldon status
        |--------------------------------------------------------------------------
        |
        | 1. Components.
        | 2. Filters.
        | 3. Configuration.
        | 4. Captcha modules.
        | 5. Messenger modules.
        |
        */

        $data['components'] = [
            'Ip'         => (!empty($this->kernel->component['Ip']))         ? true : false,
            'TrustedBot' => (!empty($this->kernel->component['TrustedBot'])) ? true : false,
            'Header'     => (!empty($this->kernel->component['Header']))     ? true : false,
            'Rdns'       => (!empty($this->kernel->component['Rdns']))       ? true : false,
            'UserAgent'  => (!empty($this->kernel->component['UserAgent']))  ? true : false,
        ];

        $reflection = new ReflectionObject($this->kernel);
        $t = $reflection->getProperty('filterStatus');
        $t->setAccessible(true);
        $filterStatus = $t->getValue($this->kernel);

        $data['filters'] = $filterStatus;

        $reflection = new ReflectionObject($this->kernel);
        $t = $reflection->getProperty('properties');
        $t->setAccessible(true);
        $properties = $t->getValue($this->kernel);
        
        $data['configuration'] = $properties;

        $data['driver'] = [
            'mysql'  => ($this->kernel->driver instanceof MysqlDriver),
            'redis'  => ($this->kernel->driver instanceof RedisDriver),
            'file'   => ($this->kernel->driver instanceof FileDriver),
            'sqlite' => ($this->kernel->driver instanceof SqliteDriver),
        ];

        $reflection = new ReflectionObject($this->kernel);
        $t = $reflection->getProperty('captcha');
        $t->setAccessible(true);
        $captcha = $t->getValue($this->kernel);

        $data['captcha'] = [
            'recaptcha'    => (isset($captcha['Recaptcha']) ? true : false),
            'imagecaptcha' => (isset($captcha['ImageCaptcha']) ? true : false),
        ];

        $reflection = new ReflectionObject($this->kernel);
        $t = $reflection->getProperty('messengers');
        $t->setAccessible(true);
        $messengers = $t->getValue($this->kernel);

        $operatingMessengers = [
            'telegram'     => false,
            'linenotify'   => false,
            'sendgrid'     => false,
            'mailgun'      => false,
            'smtp'         => false,
            'slack'        => false,
            'slackwebhook' => false,
            'rocketchat'   => false,
            'mail'         => false,
        ];

        foreach ($messengers as $messenger) {
            $class = get_class($messenger);
            $class = strtolower(substr($class, strrpos($class, '\\') + 1));

            if (isset($operatingMessengers[$class])) {
                $operatingMessengers[$class] = true;
            }
        }

        $data['messengers'] = $operatingMessengers;

        return $this->renderPage('panel/overview', $data);
    }
}

