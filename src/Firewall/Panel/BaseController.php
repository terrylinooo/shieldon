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
use Shieldon\Firewall\Panel\DemoModeTrait;
use Shieldon\Firewall\Panel\ConfigMethodsTrait;
use Shieldon\Firewall\Utils\Container;
use Shieldon\Firewall\Log\ActionLogParser;
use function Shieldon\Firewall\__;
use function Shieldon\Firewall\get_request;
use function Shieldon\Firewall\get_response;
use function Shieldon\Firewall\get_session;
use function Shieldon\Firewall\unset_superglobal;

use RuntimeException;
use function array_push;
use function define;
use function defined;
use function extract;
use function file_exists;
use function file_put_contents;
use function is_array;
use function is_numeric;
use function is_string;
use function json_encode;
use function ob_end_clean;
use function ob_get_contents;
use function ob_start;
use function trim;

/**
 * User
 */
class BaseController
{
    use FirewallTrait;
    use DemoModeTrait;
    use ConfigMethodsTrait;

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
        $body = [];

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
}

