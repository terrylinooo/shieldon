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
use Shieldon\Firewall\Kernel\Enum;
use Shieldon\Firewall\Firewall\Captcha\CaptchaFactory;
use Shieldon\Firewall\Captcha\Foundation;
use Shieldon\Event\Event;
use function Shieldon\Firewall\__;
use function Shieldon\Firewall\get_request;
use function Shieldon\Firewall\get_response;
use function Shieldon\Firewall\get_session_instance;
use function Shieldon\Firewall\unset_superglobal;
use function password_verify;

/**
 * User
 */
class User extends BaseController
{
    /**
     *   Public methods       | Desctiotion
     *  ----------------------|---------------------------------------------
     *   login                | Display the login form.
     *   logout               | Remove the login status.
     *  ----------------------|---------------------------------------------
     */

    /**
     * Constructor.
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Login
     *
     * @param string $mode login mode.
     *
     * @return ResponseInterface
     */
    public function login(): ResponseInterface
    {
        $this->applyCaptchaForms();

        $postParams = get_request()->getParsedBody();
        $login = false;
        $data = [];

        $data['error'] = '';
        $addonTitle = $this->markAsDemo;

        if (isset($postParams['s_user']) &&
            isset($postParams['s_pass'])
        ) {
            if ($this->mode === 'demo') {
                $loginResult = $this->userLoginAsDemo(
                    $postParams['s_user'],
                    $postParams['s_pass']
                );
            } else {
                $loginResult = $this->userLoginAsAdmin(
                    $postParams['s_user'],
                    $postParams['s_pass']
                );
            }

            $login = $loginResult['result'];
            $data['error'] = $loginResult['message'];
        }

        if ($login) {
            // This session variable is to mark current session as a logged user.
            get_session_instance()->set('shieldon_user_login', true);

            Event::doDispatch('user_login');

            // Redirect to overview page if logged in successfully.
            return get_response()->withHeader('Location', $this->url('home/overview'));
        }

        // Start to prompt a login form is not logged.
        if (!defined('SHIELDON_VIEW')) {
            define('SHIELDON_VIEW', true);
        }

        // `$ui` will be used in `css-default.php`. Do not remove it.
        $ui = [
            'background_image' => '',
            'bg_color'         => '#ffffff',
            'header_bg_color'  => '#212531',
            'header_color'     => '#ffffff',
            'shadow_opacity'   => '0.2',
        ];

        $data['csrf'] = $this->fieldCsrf();
        $data['form'] = get_request()->getUri()->getPath();
        $data['captchas'] = $this->captcha;

        $data['css'] = include Enum::KERNEL_DIR . '/../../templates/frontend/css/default.php';

        unset($ui);

        $data['title'] = __('panel', 'title_login', 'Login') . $addonTitle;

        return $this->respond(
            $this->loadView('frontend/login', $data)
        );
    }

    /**
     * Logout
     *
     * @return ResponseInterface
     */
    public function logout(): ResponseInterface
    {
        $sessionLoginStatus = get_session_instance()->get('shieldon_user_login');
        $sessionPanelLang = get_session_instance()->get('shieldon_panel_lang');
        $response = get_response();

        if (isset($sessionLoginStatus)) {
            unset_superglobal('shieldon_user_login', 'session');
        }

        if (isset($sessionPanelLang)) {
            unset_superglobal('shieldon_panel_lang', 'session');
        }

        // @codeCoverageIgnoreStart
        if ($this->kernel->psr7) {
            unset_superglobal('_shieldon', 'cookie');
        } else {
            setcookie('_shieldon', '', time() - 3600, '/');
        }
        // // @codeCoverageIgnoreEnd

        return $response->withHeader('Location', $this->url('user/login'));
    }

    /**
     * Set the Captcha modules.
     *
     * @return void
     */
    protected function applyCaptchaForms(): void
    {
        $this->captcha[] = new Foundation();

        $captchaList = [
            'recaptcha',
            'image',
        ];

        foreach ($captchaList as $captcha) {
            $setting = $this->getConfig('captcha_modules.' . $captcha);

            if (is_array($setting)) {
                if (CaptchaFactory::check($setting)) {
                    $this->captcha[] = CaptchaFactory::getInstance($captcha, $setting);
                }
            }
            unset($setting);
        }
    }

    /**
     * Login as demonstration.
     *
     * @param string $username The username.
     * @param string $password The password.
     *
     * @return array
     */
    private function userLoginAsDemo($username, $password): array
    {
        $login = false;
        $errorMsg = '';

        if ($username === 'demo' && $password === 'demo') {
            $login = true;
        }

        $captcha = $this->checkCaptchaValidation($login, $errorMsg);
        $login = $captcha['result'];
        $errorMsg = $captcha['message'];

        return [
            'result' => $login,
            'message' => $errorMsg,
        ];
    }

    /**
     * Login as administration.
     *
     * @param string $username The username.
     * @param string $password The password.
     *
     * @return array
     */
    private function userLoginAsAdmin($username, $password): array
    {
        $admin = $this->getConfig('admin');

        $login = false;
        $errorMsg = '';

        if (
            // Default password, unencrypted.
            $admin['user'] === $username &&
            $admin['pass'] === $password
        ) {
            // @codeCoverageIgnoreStart
            $login = true;
            // @codeCoverageIgnoreEnd
        } elseif (
            // User has already changed password, encrypted.
            $admin['user'] === $username &&
            password_verify($password, $admin['pass'])
        ) {
            $login = true;
        } else {
            $errorMsg = __('panel', 'login_message_invalid_user_or_pass', 'Invalid username or password.');
        }

        $captcha = $this->checkCaptchaValidation($login, $errorMsg);
        $login = $captcha['result'];
        $errorMsg = $captcha['message'];

        return [
            'result' => $login,
            'message' => $errorMsg,
        ];
    }

    /**
     * Check Captcha.
     *
     * @param bool   $login    The login status that will be overwritten.
     * @param string $errorMsg The error message.
     *
     * @return array
     */
    private function checkCaptchaValidation(bool $login, string $errorMsg): array
    {
        foreach ($this->captcha as $captcha) {
            if (!$captcha->response()) {
                $login = false;
                $errorMsg = __('panel', 'login_message_invalid_captcha', 'Invalid Captcha code.');
            }
        }

        return [
            'result' => $login,
            'message' => $errorMsg,
        ];
    }
}
