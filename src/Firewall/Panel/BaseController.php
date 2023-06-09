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
use Shieldon\Firewall\Firewall;
use Shieldon\Firewall\FirewallTrait;
use Shieldon\Firewall\Panel\DemoModeTrait;
use Shieldon\Firewall\Panel\ConfigMethodsTrait;
use Shieldon\Firewall\Panel\CsrfTrait;
use Shieldon\Firewall\Container;
use Shieldon\Firewall\Log\ActionLogParser;
use RuntimeException;
use function Shieldon\Firewall\__;
use function Shieldon\Firewall\get_request;
use function Shieldon\Firewall\get_response;
use function Shieldon\Firewall\get_session_instance;
use function Shieldon\Firewall\unset_superglobal;
use function Shieldon\Firewall\get_user_lang;
use function array_push;
use function define;
use function defined;
use function extract;
use function file_exists;
use function file_put_contents;
use function in_array;
use function is_array;
use function json_encode;
use function ob_end_clean;
use function ob_get_contents;
use function ob_start;
use function trim;
use const JSON_PRETTY_PRINT;

/**
 * Base controller.
 */
class BaseController
{
    /**
     *   Public methods       | Desctiotion
     *  ----------------------|---------------------------------------------
     *                        | No public methods.
     *  ----------------------|---------------------------------------------
     */

    /**
     *   Public methods       | Desctiotion
     *  ----------------------|---------------------------------------------
     *                        | No public methods.
     *  ----------------------|---------------------------------------------
     */
    use ConfigMethodsTrait;

    /**
     *   Public methods       | Desctiotion
     *  ----------------------|---------------------------------------------
     *   csrf                 | Receive the CSRF name and token from the App.
     *   setCsrfField         | Set CSRF input fields.
     *   fieldCsrf            | Output HTML input element with CSRF token.
     *  ----------------------|---------------------------------------------
     */
    use CsrfTrait;

    /**
     *   Public methods       | Desctiotion
     *  ----------------------|---------------------------------------------
     *   demo                 | Start a demo mode. Setting fields are hidden.
     *  ----------------------|---------------------------------------------
     */
    use DemoModeTrait;

    /**
     *   Public methods       | Desctiotion
     *  ----------------------|---------------------------------------------
     *   getKernel            | Get the Shieldon Kernel instance.
     *   getConfiguration     | Get the configuration data.
     *   getDirectory         | Get the dictionary where the data is stored.
     *   getFileName          | Get the path of the configuration file.
     *   getConfig            | Get the value by identification string.
     *   setConfig            | Set the value by identification string.
     *  ----------------------|---------------------------------------------
     */
    use FirewallTrait;

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
     * Language code.
     *
     * @var string
     */
    protected $locate = 'en';

    /**
     * Captcha modules.
     *
     * @var array
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

        $flashMessage = get_session_instance()->get('flash_messages');

        // Flash message, use it when redirecting page.
        if (!empty($flashMessage) && is_array($flashMessage)) {
            $this->messages = $flashMessage;
            get_session_instance()->remove('flash_messages');
        }

        $this->locate = get_user_lang();
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

        $viewFilePath = __DIR__ . '/../../../templates/' . $page . '.php';
    
        if (!empty($data)) {
            extract($data);
        }

        $output = '';
    
        if (file_exists($viewFilePath)) {
            ob_start();
            include $viewFilePath;
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
        $body = [];

        if (empty($channelName)) {
            $channelName = 'default';
        }

        $body['title'] = $data['title'] ?? '';
        $body['title'] .= ' - ' . __('panel', 'title_site_wide', 'Shieldon Firewall');
        $body['title'] .= ' v' . SHIELDON_FIREWALL_VERSION;

        $body['channel_name'] = $channelName;
        $body['mode_name'] = $this->mode;
        $body['page_url'] = $this->url();
        $body['content'] = $this->loadView($page, $data);

        $body['js_url'] = $this->url('asset/js');
        $body['css_url'] = $this->url('asset/css');
        $body['favicon_url'] = $this->url('asset/favicon');
        $body['logo_url'] = $this->url('asset/logo');

        if ($this->mode === 'demo') {
            $body['title'] .= ' (DEMO)';
        }

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
     * This method is used in a template loading other templates.
     *
     * @param string $page The page type. (filename)
     * @param array  $data The variables passed to that page.
     *
     * @return void
     */
    protected function loadViewPart(string $page, array $data = []): void
    {
        if (!defined('SHIELDON_VIEW')) {
            define('SHIELDON_VIEW', true);
        }

        foreach ($data as $k => $v) {
            ${$k} = $v;
        }

        include __DIR__ . '/../../../templates/' . $page . '.php';
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

        array_push(
            $this->messages,
            [
                'type' => $type,
                'text' => $text,
                'class' => $class,
            ]
        );
    }

    /**
     * Return the relative URL.
     *
     * @param string $path The page's path.
     *
     * @return string
     */
    protected function url(string $path = ''): string
    {
        return '/' . trim($this->base, '/') . '/' . $path . '/';
    }

    /**
     * Save the configuration settings to the JSON file.
     *
     * @return void
     */
    protected function saveConfig(): void
    {
        if ($this->mode !== 'managed') {
            return;
        }

        $postParams = (array) get_request()->getParsedBody();

        $configFilePath = $this->directory . '/' . $this->filename;

        foreach ($this->csrfField as $csrfInfo) {
            // @codeCoverageIgnoreStart
            if (!empty($csrfInfo['name'])) {
                unset_superglobal($csrfInfo['name'], 'post');
            }
            // @codeCoverageIgnoreEnd
        }

        $this->saveConfigPrepareSettings($postParams);

        //  Start checking the availibility of the data driver settings.
        $result = true;
        $result = $this->saveConfigCheckDataDriver($result);
        $result = $this->saveConfigCheckActionLogger($result);
        $result = $this->saveConfigCheckIptables($result);

        // Only update settings while data driver is correctly connected.
        if ($result) {
            file_put_contents($configFilePath, json_encode($this->configuration, JSON_PRETTY_PRINT));

            $this->pushMessage(
                'success',
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
     * @param mixed  $default Default value.
     *
     * @return void
     */
    protected function _(string $field, $default = ''): void
    {
        if ($this->mode === 'demo') {
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
                'messengers.sendgrid.config.recipients', // array
            ];

            if (in_array($field, $hiddenForDemo)) {
                echo __('panel', 'field_not_visible', 'This field cannot be viewed in demonstration mode.');
                return;
            }
        }

        $fieldtype = gettype($this->getConfig($field));

        if ($fieldtype === 'array') {
            echo implode("\n", $this->getConfig($field));
            return;
        }

        echo $this->getConfig($field) ?: $default;
    }

    /**
     * Use on HTML checkbox and radio elements.
     *
     * @param string $value        The variable or configuation field.
     * @param mixed  $valueChecked The value.
     * @param bool   $isConfig     Is it a configuration field or not.
     *
     * @return void
     */
    protected function checked(string $value, $valueChecked, bool $isConfig = true): void
    {
        if ($isConfig) {
            if ($this->getConfig($value) === $valueChecked) {
                echo 'checked';
                return;
            }
        } else {
            if ($value === $valueChecked) {
                echo 'checked';
                return;
            }
        }

        echo '';
    }

    /**
     * Echo correspondence string on Messenger setting page.
     *
     * @param string $moduleName The messenger module's name.
     * @param string $echoType   Value: css | icon
     *
     * @return void
     */
    protected function messengerAjaxStatus(string $moduleName, string $echoType = 'css'): void
    {
        $echo = [];

        $echo['css'] = $this->getConfig('messengers.' . $moduleName . '.confirm_test') ?
            'success' :
            '';
        
        $echo['icon'] = $this->getConfig('messengers.' . $moduleName . '.confirm_test') ?
            '<i class="fas fa-check"></i>' :
            '<i class="fas fa-exclamation"></i>';

        echo $echo[$echoType];
    }

    /**
     * Check the required fields.
     *
     * @param array $fields The fields from POST form.
     *
     * @return bool
     */
    protected function checkPostParamsExist(...$fields): bool
    {
        $postParams = (array) get_request()->getParsedBody();

        foreach ($fields as $field) {
            if (empty($postParams[$field])) {
                return false;
            }
        }

        return true;
    }
}
