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
use Shieldon\Firewall\Kernel\Enum;
use Shieldon\Firewall\HttpFactory;
use Shieldon\Firewall\Container;
use Shieldon\Event\Event;
use function Shieldon\Firewall\get_response;
use function Shieldon\Firewall\get_request;
use function Shieldon\Firewall\get_session_instance;
use function Shieldon\Firewall\__;
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
     *   Public methods       | Desctiotion
     *  ----------------------|---------------------------------------------
     *   respond              | Respond the result.
     *   setTemplateDirectory | Set the frontend template directory.
     *   getJavascript        | Print a JavaScript snippet in the pages.
     *  ----------------------|---------------------------------------------
     */

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
            Enum::RESPONSE_TEMPORARILY_DENY => [
                'type' => 'captcha',
                'code' => Enum::HTTP_STATUS_FORBIDDEN,
            ],

            Enum::RESPONSE_LIMIT_SESSION => [
                'type' => 'session_limitation',
                'code' => Enum::HTTP_STATUS_TOO_MANY_REQUESTS,
            ],

            Enum::RESPONSE_DENY => [
                'type' => 'rejection',
                'code' => Enum::HTTP_STATUS_BAD_REQUEST,
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
        $langCode = get_session_instance()->get('shieldon_ui_lang') ?? 'en';

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
        $uiInfo = $this->confirmUiInfoSettings($statusCode);

        $css = include $this->getTemplate('css/default');

        /**
         * Hook - dialog_output
         */
        Event::doDispatch('dialog_output');

        $performanceReport = $this->displayPerformanceReport();

        ob_start();
        include $viewPath;
        $output = ob_get_contents();
        ob_end_clean();

        // Remove unused variable notices generated from PHP intelephense.
        unset($css, $ui, $form, $captchas, $langCode, $performanceReport, $uiInfo);

        $stream = HttpFactory::createStream();
        $stream->write($output);
        $stream->rewind();

        return $response
            ->withHeader('X-Protected-By', 'shieldon.io')
            ->withBody($stream)
            ->withStatus($statusCode);
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
                document.cookie = "' . $tmpCookieName . '=' . $tmpCookieValue . ';domain=.' . $tmpCookieDomain .
                ';expires="+d.toUTCString();
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
        $directory = Enum::KERNEL_DIR . '/../../templates/frontend';

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

    /**
     * Count the performance statistics.
     *
     * @return array
     */
    protected function getPerformanceStats(): array
    {
        $statStart = Container::get('shieldon_start');
        $statEnd = Container::get('shieldon_end');

        $startTimeArr = explode(' ', $statStart['time']);
        $endTimeArr = explode(' ', $statStart['time']);

        $timeDifference = ($endTimeArr[1] - $startTimeArr[1]) + ($endTimeArr[0] - $startTimeArr[0]);
        $memoryDifference = round(($statEnd['memory'] - $statStart['memory']) / 1024, 2); // KB

        $data = [
            'time' => $timeDifference,
            'memory' => $memoryDifference,
        ];

        return $data;
    }

    /**
     * Display the HTML of the performance report.
     *
     * @return string
     */
    protected function displayPerformanceReport(): string
    {
        if (!Container::get('shieldon_start')) {
            return '';
        }

        $html = '';

        $performance = $this->getPerformanceStats();

        if ($performance['time'] < 0.001) {
            $performance['time'] = 'fewer than 0.001';
        }

        if (isset($performance['time'])) {
            $html .= '<div class="performance-report">';
            $html .= 'Memory consumed: <strong>' . $performance['memory'] . '</strong> KB / ';
            $html .= 'Execution:  <strong>' . $performance['time'] . ' </strong> seconds.';
            $html .= '</div>';
        }

        return $html;
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

        return $ui;
    }

    /**
     * Confirm UI information settings.
     *
     * @param int $statusCode HTTP status code.
     *
     * @return array
     */
    private function confirmUiInfoSettings(int $statusCode): array
    {
        $uiInfo = [];

        $reasonCode = $this->reason;

        $uiInfo['http_status_code'] = $statusCode;
        $uiInfo['reason_code']      = $reasonCode;
        $uiInfo['reason_text']      = __('core', 'messenger_text_reason_code_' . $reasonCode);

        $uiInfo['is_display_online_user_amount']  = $this->properties['display_online_info'];
        $uiInfo['is_display_user_information']    = $this->properties['display_user_info'];
        $uiInfo['is_display_display_http_code']   = $this->properties['display_http_code'];
        $uiInfo['is_display_display_reason_code'] = $this->properties['display_reason_code'];
        $uiInfo['is_display_display_reason_text'] = $this->properties['display_reason_text'];

        return $uiInfo;
    }
}
