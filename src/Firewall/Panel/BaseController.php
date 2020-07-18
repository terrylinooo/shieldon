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
use Shieldon\Firewall\Firewall;
use Shieldon\Firewall\FirewallTrait;
use Shieldon\Firewall\Panel\DemoTrait;
use Shieldon\Firewall\Utils\Container;
use Shieldon\Firewall\Log\ActionLogParser;
use function Shieldon\Firewall\__;
use function Shieldon\Firewall\get_request;
use function Shieldon\Firewall\get_response;
use function Shieldon\Firewall\get_session;

use PDO;
use PDOException;
use Redis;
use RedisException;
use RuntimeException;
use function array_push;
use function class_exists;
use function define;
use function defined;
use function extract;
use function file_exists;
use function file_put_contents;
use function is_array;
use function is_numeric;
use function is_string;
use function is_writable;
use function json_encode;
use function mkdir;
use function ob_end_clean;
use function ob_get_contents;
use function ob_start;
use function password_hash;
use function preg_split;
use function rtrim;
use function str_replace;
use function trim;
use function umask;

/**
 * User
 */
class BaseController
{
    use FirewallTrait;
    use DemoTrait;

    /**
     * LogPaeser instance.
     *
     * @var object
     */
    protected $parser;

    /**
     * Messages.
     *
     * @var array
     */
    protected $messages = [];

    /**
     * Check page availability.
     *
     * @var array
     */
    protected $pageAvailability = [

        // Need to implement Action Logger to make it true.
        'logs' => false,
    ];

    /**
     * see $this->csrf()
     *
     * @var array
     */
    protected $csrfField = [];

    /**
     * Language code.
     *
     * @var string
     */
    protected $locate = 'en';

    /**
     * Captcha modules.
     *
     * @var Interface
     */
    protected $captcha = [];

    /**
     * The base URL of the firewall panel.
     *
     * @var string
     */
    public $base = '';

    /**
     * Firewall panel base controller.                  
     */
    public function __construct() 
    {
        $firewall = Container::get('firewall');

        if (!($firewall instanceof Firewall)) {
            throw new RuntimeException(
                'The Firewall instance should be initialized first.'
            );
        }

        $this->mode          = 'managed';
        $this->kernel        = $firewall->getKernel();
        $this->configuration = $firewall->getConfiguration();
        $this->directory     = $firewall->getDirectory();
        $this->filename      = $firewall->getFilename();
        $this->base          = SHIELDON_PANEL_BASE;

        if (!empty($this->kernel->logger)) {

            // We need to know where the logs stored in.
            $logDirectory = $this->kernel->logger->getDirectory();

            // Load ActionLogParser for parsing log files.
            $this->parser = new ActionLogParser($logDirectory);

            $this->pageAvailability['logs'] = true;
        }

        $flashMessage = get_session()->get('flash_messages');

        // Flash message, use it when redirecting page.
        if (!empty($flashMessage)) {
            $this->messages = $flashMessage;
            get_session()->remove('flash_messages');
        }

        $this->locate = 'en';

        $sessionLang = get_session()->get('shieldon_panel_lang');

        if (!empty($sessionLang)) {
            $this->locate = $sessionLang;
        }
    }

    /**
     * Load view file.
     *
     * @param string $page The page type. (filename)
     * @param array  $data The variables passed to that page.
     *
     * @return string
     */
    protected function loadView(string $page, array $data = []): string
    {
        if (!defined('SHIELDON_VIEW')) {
            define('SHIELDON_VIEW', true);
        }

        $viewFilePath =  __DIR__ . '/../../../templates/' . $page . '.php';
    
        if (!empty($data)) {
            extract($data);
        }

        $output = '';
    
        if (file_exists($viewFilePath)) {
            ob_start();
            require $viewFilePath;
            $output = ob_get_contents();
            ob_end_clean();
        }

        return $output;
    }

    /**
     * Render the web page with full layout.
     *
     * @param string $page The page type. (filename)
     * @param array  $data The variables passed to that page.
     *
     * @return ResponseInterface
     */
    protected function renderPage(string $page, array $data): ResponseInterface
    {
        $channelName = $this->kernel->driver->getChannel();

        if (empty($channelName)) {
            $channelName = 'default';
        }

        $body['channel_name'] = $channelName;
        $body['mode_name'] = $this->mode;
        $body['page_url'] = $this->url();
        $body['content'] = $this->loadView($page, $data);
        $body['title'] = $data['title'] ?? '';

        $body['title'] .= ' - ' . __('panel', 'title_site_wide', 'Shieldon Firewall');
        $body['title'] .= ' v' . SHIELDON_FIREWALL_VERSION;

        $page = $this->loadView('panel/template', $body);

        return $this->respond($page);
    }

    /**
     * Return the response instance.
     *
     * @param string $body The content body.
     *
     * @return ResponseInterface
     */
    protected function respond(string $body): ResponseInterface
    {
        $response = get_response();
        $stream = $response->getBody();
        $stream->write($body);
        $stream->rewind();

        return $response->withBody($stream);
    }

    /**
     * Include a view file.
     *
     * @param string $page The page type. (filename)
     * @param array  $data The variables passed to that page.
     *
     * @return void
     */
    protected function _include(string $page, array $data = []): void
    {
        if (!defined('SHIELDON_VIEW')) {
            define('SHIELDON_VIEW', true);
        }

        foreach ($data as $k => $v) {
            ${$k} = $v;
        }

        require __DIR__ . '/../../../templates/' . $page . '.php';
    }

    /**
     * Response message to front.
     *
     * @param string $type The message status type. error|success
     * @param string $text The message body.
     *
     * @return void
     */
    protected function pushMessage(string $type, string $text): void
    {
        $class = $type;

        if ($type == 'error') {
            $class = 'danger';
        }

        array_push($this->messages, [
            'type' => $type,
            'text' => $text,
            'class' => $class,
        ]);
    }

    /**
     * Return the relative URL.
     *
     * @param string $path The page's path.
     * @param string $tab  Tab.
     *
     * @return string
     */
    protected function url(string $path = '', string $tab = ''): string
    {
        $query = !empty($tab) ? '?tab=' . $tab : '';

        return '/' . trim($this->base, '/') . '/' . $path . '/' . $query;
    }

    /**
     * Output HTML input element with CSRF token.
     *
     * @return void
     */
    public function _csrf(): void
    {
        if (!empty($this->csrfField)) {
            foreach ($this->csrfField as $value) {
                echo '<input type="hidden" name="' . $value['name'] . '" value="' . $value['value'] . '" id="csrf-field">';
            }
        }
    }

    /**
     * Save the configuration settings to the JSON file.
     *
     * @return void
     */
    protected function saveConfig(): void
    {
        $postParams = get_request()->getParsedBody();

        $configFilePath = $this->directory . '/' . $this->filename;

        foreach ($this->csrfField as $csrfInfo) {
            if (!empty($csrfInfo['name'])) {
                unset_superglobal($csrfInfo['name'], 'post');
            }
        }

        if (empty($postParams) || !is_array($postParams) || 'managed' !== $this->mode) {
            return;
        }

        $this->saveConfigPrepareSettings($postParams);

        //  Start checking the availibility of the data driver settings.
        $result = true;
        $result = $this->saveConfigCheckDataDriver($result);
        $result = $this->saveConfigCheckActionLogger($result);
        $result = $this->saveConfigCheckIptables($result);

        // Only update settings while data driver is correctly connected.
        if ($result) {
            file_put_contents($configFilePath, json_encode($this->configuration));

            $this->pushMessage('success',
                __(
                    'panel',
                    'success_settings_saved',
                    'Settings saved.'
                )
            );
        }
    }

    /**
     * Echo the setting string to the template.
     *
     * @param string $field   Field.
     * @param mixed  $defailt Default value.
     *
     * @return void
     */
    protected function _(string $field, $default = ''): void
    {
        if (is_string($this->getConfig($field)) || is_numeric($this->getConfig($field))) {

            if ('demo' === $this->mode) {

                // Hide sensitive data because of security concerns.
                $hiddenForDemo = [
                    'drivers.redis.auth',
                    'drivers.file.directory_path',
                    'drivers.sqlite.directory_path',
                    'drivers.mysql.dbname',
                    'drivers.mysql.user',
                    'drivers.mysql.pass',
                    'captcha_modules.recaptcha.config.site_key',
                    'captcha_modules.recaptcha.config.secret_key',
                    'loggers.action.config.directory_path',
                    'admin.user',
                    'admin.pass',
                    'admin.last_modified',
                    'messengers.telegram.config.api_key',
                    'messengers.telegram.config.channel',
                    'messengers.sendgrid.config.api_key',
                    'messengers.sendgrid.config.sender',
                    'messengers.sendgrid.config.recipients',
                    'messengers.line_notify.config.access_token',
                    'iptables.config.watching_folder',
                    'ip6tables.config.watching_folder',
                ];

                if (in_array($field, $hiddenForDemo)) {
                    echo __('panel', 'field_not_visible', 'Cannot view this field in demo mode.');
                } else {
                    echo (!empty($this->getConfig($field))) ? $this->getConfig($field) : $default;
                }

            } else {
                echo (!empty($this->getConfig($field))) ? $this->getConfig($field) : $default;
            }
        } elseif (is_array($this->getConfig($field))) {

            if ('demo' === $this->mode) {
                $hiddenForDemo = [
                    'messengers.sendgrid.config.recipients'
                ];

                if (in_array($field, $hiddenForDemo)) {
                    echo __('panel', 'field_not_visible', 'Cannot view this field in demo mode.');
                } else {
                    echo implode("\n", $this->getConfig($field));
                }

            } else {
                echo implode("\n", $this->getConfig($field));
            }
        }
    }

    /**
     * Use on HTML checkbox and radio elements.
     *
     * @param string $value
     * @param mixed  $valueChecked
     * @param bool   $isConfig
     *
     * @return void
     */
    protected function checked(string $value, $valueChecked, bool $isConfig = true): void
    {
        if ($isConfig) {
            if ($this->getConfig($value) === $valueChecked) {
                echo 'checked';
            } else {
                echo '';
            }
        } else {
            if ($value === $valueChecked) {
                echo 'checked';
            } else {
                echo '';
            }
        }
    }

    /**
     * Echo correspondence string on Messenger setting page.
     *
     * @param string $moduleName
     * @param string $echoType
     *
     * @return void
     */
    protected function _m(string $moduleName, string $echoType = 'css'): void
    {
        if ('css' === $echoType) {
            echo $this->getConfig('messengers.' . $moduleName . '.confirm_test') ? 'success' : '';
        }

        if ('icon' === $echoType) {
            echo $this->getConfig('messengers.' . $moduleName . '.confirm_test') ? '<i class="fas fa-check"></i>' : '<i class="fas fa-exclamation"></i>';
        }
    }

    /**
     * Use on HTML select elemets.
     *
     * @param string $value
     * @param mixed  $valueChecked
     *
     * @return void
     */
    protected function selected(string $value, $valueChecked): void
    {
        if ($this->getConfig($value) === $valueChecked) {
            echo 'selected';
        } else {
            echo '';
        }
    }

    /**
     * Parse the POST fields and set them into configuration data structure.
     * Used for saveConfig method only.
     *
     * @param array $postParams
     *
     * @return void
     */
    private function saveConfigPrepareSettings(array $postParams): void
    {
        foreach ($postParams as $postKey => $postData) {
            if (is_string($postData)) {
                if ($postData === 'on') {
                    $this->setConfig(str_replace('__', '.', $postKey), true);

                } elseif ($postData === 'off') {
                    $this->setConfig(str_replace('__', '.', $postKey), false);

                } else {
                    if ($postKey === 'ip_variable_source') {
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
                    } else if ($postKey === 'messengers__sendgrid__config__recipients') {
                        $this->setConfig(
                            'messengers.sendgrid.config.recipients',
                            preg_split('/\r\n|[\r\n]/',
                            $postData)
                        );
                    } else {
                        if (is_numeric($postData)) {
                            $this->setConfig(str_replace('__', '.', $postKey), (int) $postData);
                        } else  {
                            $this->setConfig(str_replace('__', '.', $postKey), $postData);
                        }
                    }
                }
            }
        }
    }

    /**
     * Check the settings of Action Logger.
     *
     * @return bool
     */
    private function saveConfigCheckActionLogger(bool $result): bool
    {
        if (!$result) {
            return false;
        }

        // Check Action Logger settings.
        $enableActionLogger = $this->getConfig('loggers.action.enable');
        $actionLogDir = rtrim($this->getConfig('loggers.action.config.directory_path'), '\\/ ');

        $result = true;

        if ($enableActionLogger) {
            if (empty($actionLogDir)) {
                $actionLogDir = $this->directory . '/action_logs';
            }

            $this->setConfig('loggers.action.config.directory_path', $actionLogDir);

            if (!is_dir($actionLogDir)) {
                $originalUmask = umask(0);
                @mkdir($actionLogDir, 0777, true);
                umask($originalUmask);
            }

            if (!is_writable($actionLogDir)) {
                $result = false;
                $this->pushMessage('error',
                    __(
                        'panel',
                        'error_logger_directory_not_writable',
                        'Action Logger requires the storage directory writable.'
                    )
                );
            }
        }

        return $result;
    }

    /**
     * Check the settings of Iptables.
     *
     * @return bool
     */
    private function saveConfigCheckIptables(bool $result): bool
    {
        if (!$result) {
            return false;
        }

        // System firewall.
        $enableIptables = $this->getConfig('iptables.enable');
        $iptablesWatchingFolder = rtrim($this->getConfig('iptables.config.watching_folder'), '\\/ ');

        $result = true;

        if ($enableIptables) {
            if (empty($iptablesWatchingFolder)) {
                $iptablesWatchingFolder = $this->directory . '/iptables';
            }

            $this->setConfig('iptables.config.watching_folder', $iptablesWatchingFolder);

            if (!is_dir($iptablesWatchingFolder)) {
                $originalUmask = umask(0);
                @mkdir($iptablesWatchingFolder, 0777, true);
                umask($originalUmask);

                // Create default log files.
                if (is_writable($iptablesWatchingFolder)) {
                    fopen($iptablesWatchingFolder . '/iptables_queue.log', 'w+');
                    fopen($iptablesWatchingFolder . '/ipv4_status.log',    'w+');
                    fopen($iptablesWatchingFolder . '/ipv6_status.log',    'w+');
                    fopen($iptablesWatchingFolder . '/ipv4_command.log',   'w+');
                    fopen($iptablesWatchingFolder . '/ipv6_command.log',   'w+');
                }
            }
    
            if (!is_writable($iptablesWatchingFolder)) {
                $result = false;
                $this->pushMessage('error',
                    __(
                        'panel',
                        'error_ip6tables_directory_not_writable',
                        'iptables watching folder requires the storage directory writable.'
                    )
                );
            }
        }

        return $result;
    }

    /**
     * Check the settings of Data drivers.
     *
     * @return bool
     */
    protected function saveConfigCheckDataDriver(bool $result): bool
    {
        if (!$result) {
            return false;
        }

        switch ($this->configuration['driver_type']) {
            case 'mysql':
                if (class_exists('PDO')) {
                    $db = [
                        'host'    => $this->getConfig('drivers.mysql.host'),
                        'dbname'  => $this->getConfig('drivers.mysql.dbname'),
                        'user'    => $this->getConfig('drivers.mysql.user'),
                        'pass'    => $this->getConfig('drivers.mysql.pass'),
                        'charset' => $this->getConfig('drivers.mysql.charset'),
                    ];

                    try {
                        $pdo = new PDO(
                            'mysql:host=' . $db['host'] . ';dbname=' . $db['dbname'] . ';charset=' . $db['charset'],
                            (string) $db['user'],
                            (string) $db['pass']
                        );
                    } catch(PDOException $e) {
                        $result = false;
                        $this->pushMessage('error', 
                            __(
                                'panel',
                                'error_mysql_connection',
                                'Cannot access to your MySQL database, please check your settings.'
                            )
                        );
                    }
                } else {
                    $result = false;
                    $this->pushMessage('error',
                        __(
                            'panel',
                            'error_mysql_driver_not_supported',
                            'Your system doesn’t support MySQL driver.'
                        )
                    );
                }
                break;

            case 'sqlite':
                $sqliteDir = rtrim($this->getConfig('drivers.sqlite.directory_path'), '\\/ ');

                if (empty($sqliteDir)) {
                    $sqliteDir = $this->directory . '/data_driver_sqlite';
                }

                $sqliteFilePath = $sqliteDir . '/shieldon.sqlite3';
                $this->setConfig('drivers.sqlite.directory_path', $sqliteDir);
                
                if (!file_exists($sqliteFilePath)) {
                    if (!is_dir($sqliteDir)) {
                        $originalUmask = umask(0);
                        @mkdir($sqliteDir, 0777, true);
                        umask($originalUmask);
                    }
                }

                if (class_exists('PDO')) {
                    try {
                        $pdo = new PDO('sqlite:' . $sqliteFilePath);
                    } catch(PDOException $e) {
                        $result = false;
                        $this->pushMessage('error', $e->getMessage());
                    }
                } else {
                    $result = false;
                    $this->pushMessage('error',
                        __(
                            'panel',
                            'error_sqlite_driver_not_supported',
                            'Your system doesn’t support SQLite driver.'
                        )
                    );
                }

                if (!is_writable($sqliteFilePath)) {
                    $result = false;
                    $this->pushMessage('error',
                        __(
                            'panel',
                            'error_sqlite_directory_not_writable',
                            'SQLite data driver requires the storage directory writable.'
                        )
                    );
                }
                break;

            case 'redis':
                if (class_exists('Redis')) {
                    try {
                        $redis = new Redis();
                        $redis->connect(
                            (string) $this->getConfig('drivers.redis.host'), 
                            (int)    $this->getConfig('drivers.redis.port')
                        );
                    } catch(RedisException $e) {
                        $result = false;
                        $this->pushMessage('error', $e->getMessage());
                    }
                } else {
                    $result = false;
                    $this->pushMessage('error',
                        __(
                            'panel',
                            'error_redis_driver_not_supported',
                            'Your system doesn’t support Redis driver.'
                        )
                    );
                }
                break;

            case 'file':
            default:
                $fileDir = rtrim($this->getConfig('drivers.file.directory_path'), '\\/ ');

                if (empty($fileDir)) {
                    $fileDir = $this->directory . '/data_driver_file';
                    $this->setConfig('drivers.file.directory_path', $fileDir);
                }

                $this->setConfig('drivers.file.directory_path', $fileDir);

                if (!is_dir($fileDir)) {
                    $originalUmask = umask(0);
                    @mkdir($fileDir, 0777, true);
                    umask($originalUmask);
                }

                if (!is_writable($fileDir)) {
                    $result = false;
                    $this->pushMessage('error',
                        __(
                            'panel',
                            'error_file_directory_not_writable',
                            'File data driver requires the storage directory writable.'
                        )
                    );
                }
            // endswitch
        }

        return $result;
    }
}

