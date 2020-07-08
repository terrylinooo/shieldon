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
use RuntimeException;
use Shieldon\Firewall\FirewallTrait;
use Shieldon\Firewall\Utils\Container;
use Shieldon\Firewall\Firewall;
use function Shieldon\Firewall\get_request;
use function Shieldon\Firewall\get_response;
use function Shieldon\Firewall\get_session;

/**
 * User
 */
class BaseController
{
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
     * self: Shieldon | managed: Firewall
     *
     * @var string
     */
    protected $mode = 'self';

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
     * Login as a demo user.
     *
     * @var array
     */
    protected $demoUser = [
        'user' => 'demo',
        'pass' => 'demo',
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
     * @var Interface
     */
    protected $captcha = [];

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

        $sessionLang = get_session()->get('SHIELDON_PANEL_LANG');

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
     * @return \Psr\Http\Message\ResponseInterface
     */
    protected function loadView(string $page, array $data = []): ResponseInterface
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

        $response = get_response();
        $stream = $response->getBody();
        $stream->write($output);
        $stream->rewind();

        return $response->withBody($stream);
    }

    /**
     * Render the web page with full layout.
     *
     * @param string $page The page type. (filename)
     * @param array  $data The variables passed to that page.
     *
     * @return \Psr\Http\Message\ResponseInterface
     */
    protected function renderPage(string $page, array $data): ResponseInterface
    {
        $channelName = $this->kernel->driver->getChannel();

        $content['channel_name'] = $channelName ?? 'default';
        $content['mode_name'] = $this->mode;
        $content['page_url'] = $this->url();
        $content['content'] = $this->loadView($page, $data);
        $content['title'] = $data['title'] ?? '';

        return $this->loadView('panel/template', $content);
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
     * Providing the Dasboard URLs.
     *
     * @param string $page Page tab.
     * @param string $tab  Tab.
     *
     * @return string
     */
    protected function url(string $page = '', string $tab = ''): string
    {
        $httpProtocal = 'http://';

        if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') {
            $httpProtocal = 'https://';
        }

        $path = parse_url($httpProtocal . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'], PHP_URL_PATH);

        $url = $httpProtocal . $_SERVER['HTTP_HOST'] . $path;
        $soPage = (!empty($page)) ? '?so_page=' . $page : '';
        $soTab = (!empty($tab)) ? '&tab=' . $tab : '';

        return $url . $soPage . $soTab;
    }

    /**
     * Prompt an authorization login.
     *
     * @return void
     */
    protected function httpAuth(): void
    {
        $check = get_session()->get('SHIELDON_USER_LOGIN');

        if (empty($check)) {
            $this->login();
        }
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
}

