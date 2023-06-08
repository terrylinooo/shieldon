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

use Psr\Http\Message\ResponseInterface;
use Shieldon\Firewall\Panel\BaseController;
use Shieldon\Firewall\Driver as Driver;
use ReflectionObject;
use function Shieldon\Firewall\__;
use function Shieldon\Firewall\get_request;
use function Shieldon\Firewall\unset_superglobal;
use function count;
use function date;
use function get_class;
use function ksort;
use function round;
use function sleep;
use function strrpos;
use function strtolower;
use function strtotime;
use function substr;

/**
 * Home
 */
class Home extends BaseController
{
    /**
     *   Public methods       | Desctiotion
     *  ----------------------|---------------------------------------------
     *   overview             | The overview page.
     *  ----------------------|---------------------------------------------
     */

    /**
     * Constructor
     */
    public function __construct()
    {
        parent::__construct();
    }

    // @codeCoverageIgnoreStart

    /**
     * Default entry
     *
     * @return ResponseInterface
     */
    public function index(): ResponseInterface
    {
        
        return $this->overview();
    }

    // @codeCoverageIgnoreEnd

    /**
     * Overview
     *
     * @return ResponseInterface
     */
    public function overview(): ResponseInterface
    {
        // Collection of the template variables.
        $data = [];

        // Handle the form post action.
        $this->overviewFormPost();

        /*
        |--------------------------------------------------------------------------
        | Logger
        |--------------------------------------------------------------------------
        |
        | All logs were recorded by ActionLogger.
        | Get the summary information from those logs.
        |
        */

        $data = $this->overviewTemplateVarsOfActionLogger($data);

        /*
        |--------------------------------------------------------------------------
        | Data circle
        |--------------------------------------------------------------------------
        |
        | A data circle includes the primary data tables of Shieldon.
        | They are ip_log_table, ip_rule_table and session_table.
        |
        */

        $data = $this->overviewTemplateVarsOfDataCircle($data);
 
        /*
        |--------------------------------------------------------------------------
        | Shieldon status
        |--------------------------------------------------------------------------
        |
        | 1. Components.
        | 2. Filters.
        | 3. Configuration.
        | 4. Data drivers.
        | 5. Captcha modules.
        | 6. Messenger modules.
        |
        */

        $data = $this->overviewTemplateVarsOfComponents($data);
        $data = $this->overviewTemplateVarsOfFilters($data);
        $data = $this->overviewTemplateVarsOfConfiguration($data);
        $data = $this->overviewTemplateVarsOfDataDrivers($data);
        $data = $this->overviewTemplateVarsOfCaptchas($data);
        $data = $this->overviewTemplateVarsOfMessengers($data);

        // Page title is also needed.
        $data['title'] = __('panel', 'title_overview', 'Overview');

        return $this->renderPage('panel/overview', $data);
    }

    /**
     * Detect and handle form post action.
     *
     * @return void
     */
    private function overviewFormPost()
    {
        $postParams = get_request()->getParsedBody();

        if (!isset($postParams['action_type'])) {
            return;
        }

        switch ($postParams['action_type']) {
            case 'reset_data_circle':
                $this->setConfig('cronjob.reset_circle.config.last_update', date('Y-m-d H:i:s'));
                $this->kernel->driver->rebuild();
                sleep(2);

                unset_superglobal('action_type', 'post');

                $this->saveConfig();

                $this->pushMessage(
                    'success',
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

                $this->pushMessage(
                    'success',
                    __(
                        'panel',
                        'reset_action_logs',
                        'Action logs have been removed.'
                    )
                );
                break;
        }
    }

    /**
     * Template variables of the section of Action Logger.
     *
     * @param array $data The template varibles.
     *
     * @return array
     */
    private function overviewTemplateVarsOfActionLogger(array $data = []): array
    {
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

        return $data;
    }

    /**
     * Template variables of the section of Data Circle.
     *
     * @param array $data The template varibles.
     *
     * @return array
     */
    private function overviewTemplateVarsOfDataCircle(array $data = []): array
    {
        $data['rule_list'] = $this->kernel->driver->getAll('rule');
        $data['ip_log_list'] = $this->kernel->driver->getAll('filter');
        $data['session_list'] = $this->kernel->driver->getAll('session');

        return $data;
    }

    /**
     * Template variables of the section of Shieldon Status.
     * Displayed on the Components area.
     *
     * @param array $data The template varibles.
     *
     * @return array
     */
    private function overviewTemplateVarsOfComponents(array $data = []): array
    {
        $data['components'] = [
            'Ip'         => (!empty($this->kernel->component['Ip'])) ? true : false,
            'TrustedBot' => (!empty($this->kernel->component['TrustedBot'])) ? true : false,
            'Header'     => (!empty($this->kernel->component['Header'])) ? true : false,
            'Rdns'       => (!empty($this->kernel->component['Rdns'])) ? true : false,
            'UserAgent'  => (!empty($this->kernel->component['UserAgent'])) ? true : false,
        ];

        return $data;
    }

    /**
     * Template variables of the section of Shieldon Status.
     * Displayed on the Filters area.
     *
     * @param array $data The template varibles.
     *
     * @return array
     */
    private function overviewTemplateVarsOfFilters(array $data = []): array
    {
        $reflection = new ReflectionObject($this->kernel);
        $t = $reflection->getProperty('filterStatus');
        $t->setAccessible(true);
        $filterStatus = $t->getValue($this->kernel);

        $data['filters'] = $filterStatus;

        return $data;
    }

    /**
     * Template variables of the section of Shieldon Status.
     * Displayed on the Configuration area.
     *
     * @param array $data The template varibles.
     *
     * @return array
     */
    private function overviewTemplateVarsOfConfiguration(array $data = []): array
    {
        $reflection = new ReflectionObject($this->kernel);
        $t = $reflection->getProperty('properties');
        $t->setAccessible(true);
        $properties = $t->getValue($this->kernel);
        
        $data['configuration'] = $properties;

        return $data;
    }

    /**
     * Template variables of the section of Shieldon Status.
     * Displayed on the Data Drivers area.
     *
     * @param array $data The template varibles.
     *
     * @return array
     */
    private function overviewTemplateVarsOfDataDrivers(array $data = []): array
    {
        $data['driver'] = [
            'mysql'  => ($this->kernel->driver instanceof Driver\MysqlDriver),
            'redis'  => ($this->kernel->driver instanceof Driver\RedisDriver),
            'file'   => ($this->kernel->driver instanceof Driver\FileDriver),
            'sqlite' => ($this->kernel->driver instanceof Driver\SqliteDriver),
        ];

        return $data;
    }

    /**
     * Template variables of the section of Shieldon Status.
     * Displayed on the Captchas area.
     *
     * @param array $data The template varibles.
     *
     * @return array
     */
    private function overviewTemplateVarsOfCaptchas(array $data = []): array
    {
        $reflection = new ReflectionObject($this->kernel);
        $t = $reflection->getProperty('captcha');
        $t->setAccessible(true);
        $captcha = $t->getValue($this->kernel);

        $data['captcha'] = [
            'recaptcha'    => (isset($captcha['ReCaptcha']) ? true : false),
            'imagecaptcha' => (isset($captcha['ImageCaptcha']) ? true : false),
        ];

        return $data;
    }

    /**
     * Template variables of the section of Shieldon Status.
     * Displayed on the Messengers area.
     *
     * @param array $data The template varibles.
     *
     * @return array
     */
    private function overviewTemplateVarsOfMessengers(array $data = []): array
    {
        $reflection = new ReflectionObject($this->kernel);
        $t = $reflection->getProperty('messenger');
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

        return $data;
    }
}
