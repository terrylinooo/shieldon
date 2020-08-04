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

namespace Shieldon\Firewall\Kernel;

use Psr\Http\Message\ResponseInterface;
use Shieldon\Firewall\Kernel;
use Shieldon\Firewall\HttpFactory;
use function Shieldon\Firewall\get_response;
use function Shieldon\Firewall\get_request;
use function Shieldon\Firewall\get_session;

use InvalidArgumentException;
use RuntimeException;
use function array_keys;
use function define;
use function defined;
use function is_dir;
use function ob_end_clean;
use function ob_get_contents;
use function ob_start;
use function file_exists;
use function sprintf;

/*
 * The template-related functions.
 */
trait TemplateTrait
{
    /**
     * The directory in where the frontend template files are placed.
     *
     * @var string
     */
    protected $templateDirectory = '';

    /**
     * Custom dialog UI settings.
     *
     * @var array
     */
    protected $dialog = [];

    /**
     *   Public methods       | Desctiotion
     *  ----------------------|---------------------------------------------
     *   setDialog            | Customize the dialog UI.
     *   respond              | Respond the result.
     *   setTemplateDirectory | Set the frontend template directory.
     *   getJavascript        | Print a JavaScript snippet in the pages.
     *  ----------------------|---------------------------------------------
     */

    /**
     * Get current visior's path.
     *
     * @return string
     */
    abstract public function getCurrentUrl(): string;

    /**
     * Customize the dialog UI.
     * 
     * @param array $settings The dialog UI settings.
     *
     * @return void
     */
    public function setDialog(array $settings): void
    {
        $this->dialog = $settings;
    }

    /**
     * Respond the result.
     *
     * @return ResponseInterface
     */
    public function respond(): ResponseInterface
    {
        $response = get_response();

        $httpStatusCodes = [
            Kernel::RESPONSE_TEMPORARILY_DENY => [
                'type' => 'captcha',
                'code' => Kernel::HTTP_STATUS_FORBIDDEN,
            ],

            Kernel::RESPONSE_LIMIT_SESSION => [
                'type' => 'session_limitation',
                'code' => Kernel::HTTP_STATUS_TOO_MANY_REQUESTS,
            ],

            Kernel::RESPONSE_DENY => [
                'type' => 'rejection',
                'code' => Kernel::HTTP_STATUS_BAD_REQUEST,
            ],
        ];

        // Nothing happened. Return.
        if (empty($httpStatusCodes[$this->result])) {
            return $response;
        }

        $type = $httpStatusCodes[$this->result]['type'];
        $statusCode = $httpStatusCodes[$this->result]['code'];

        $viewPath = $this->getTemplate($type);

        // The language of output UI. It is used on views.
        $langCode = get_session()->get('shieldon_ui_lang') ?? 'en';

        $onlineinfo = [];
        $onlineinfo['queue'] = $this->sessionStatus['queue'];
        $onlineinfo['count'] = $this->sessionStatus['count'];
        $onlineinfo['period'] = $this->sessionLimit['period'];

        $dialoguserinfo = [];
        $dialoguserinfo['ip'] = $this->ip;
        $dialoguserinfo['rdns'] = $this->rdns;
        $dialoguserinfo['user_agent'] = get_request()->getHeaderLine('user-agent');

        // Captcha form
        $form = $this->getCurrentUrl();
        $captchas = $this->captcha;

        // Check and confirm the UI settings.
        $ui = $this->confirmUiSettings();

        $css = include $this->getTemplate('css/default');

        ob_start();
        include $viewPath;
        $output = ob_get_contents();
        ob_end_clean();

        // Remove unused variable notices generated from PHP intelephense.
        unset($css, $ui, $form, $captchas, $langCode);

        $stream = HttpFactory::createStream();
        $stream->write($output);
        $stream->rewind();

        return $response
            ->withHeader('X-Protected-By', 'shieldon.io')
            ->withBody($stream)
            ->withStatus($statusCode);
    }

    /**
     * Confirm the UI settings.
     *
     * @return array
     */
    private function confirmUiSettings(): array
    {
        if (!defined('SHIELDON_VIEW')) {
            define('SHIELDON_VIEW', true);
        }

        $ui = [
            'background_image' => '',
            'bg_color'         => '#ffffff',
            'header_bg_color'  => '#212531',
            'header_color'     => '#ffffff',
            'shadow_opacity'   => '0.2',
        ];

        foreach (array_keys($ui) as $key) {
            if (!empty($this->dialog[$key])) {
                $ui[$key] = $this->dialog[$key];
            }
        }

        $ui['is_display_online_info'] = false;
        $ui['is_display_user_info'] = false;

        // Show online session count. It is used on views.
        if (!empty($this->properties['display_online_info'])) {
            $ui['is_display_online_info'] = true;
        }

        // Show user information such as IP, user-agent, device name.
        if (!empty($this->properties['display_user_info'])) {
            $ui['is_display_user_info'] = true;
        }

        return $ui;
    }

    /**
     * Print a JavaScript snippet in your webpages.
     * 
     * This snippet generate cookie on client's browser,then we check the 
     * cookie to identify the client is a rebot or not.
     *
     * @return string
     */
    public function getJavascript(): string
    {
        $tmpCookieName = $this->properties['cookie_name'];
        $tmpCookieDomain = $this->properties['cookie_domain'];

        if (empty($tmpCookieDomain) && get_request()->getHeaderLine('host')) {
            $tmpCookieDomain = get_request()->getHeaderLine('host');
        }

        $tmpCookieValue = $this->properties['cookie_value'];

        $jsString = '
            <script>
                var d = new Date();
                d.setTime(d.getTime()+(60*60*24*30));
                document.cookie = "' . $tmpCookieName . '=' . $tmpCookieValue . ';domain=.' . $tmpCookieDomain . ';expires="+d.toUTCString();
            </script>
        ';

        return $jsString;
    }

    /**
     * Set the frontend template directory.
     *
     * @param string $directory The directory in where the template files are placed.
     *
     * @return void
     */
    public function setTemplateDirectory(string $directory): void
    {
        if (!is_dir($directory)) {
            throw new InvalidArgumentException(
                'The template directory does not exist.'
            );
        }
        $this->templateDirectory = $directory;
    }

    /**
     * Get a template PHP file.
     *
     * @param string $type The template type.
     *
     * @return string
     */
    protected function getTemplate(string $type): string
    {
        $directory = Kernel::KERNEL_DIR . '/../../templates/frontend';

        if (!empty($this->templateDirectory)) {
            $directory = $this->templateDirectory;
        }

        $path = $directory . '/' . $type . '.php';

        if (!file_exists($path)) {
            throw new RuntimeException(
                sprintf(
                    'The templeate file is missing. (%s)',
                    $path
                )
            );
        }

        return $path;
    }
}
