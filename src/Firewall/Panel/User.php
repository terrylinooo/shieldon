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
use Shieldon\Firewall\Captcha as Captcha;
use function Shieldon\Firewall\__;
use function Shieldon\Firewall\get_request;
use function Shieldon\Firewall\get_response;
use function Shieldon\Firewall\get_session;
use function Shieldon\Firewall\unset_superglobal;

use function password_verify;

/**
 * User
 */
class User extends BaseController
{
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
     * @return ResponseInterface
     */
    public function login(): ResponseInterface
    {
        $this->applyCaptchaForms();

        $postParams = get_request()->getParsedBody();

        $login = false;
        $data['error'] = '';

        if (isset($postParams['s_user']) && isset($postParams['s_pass'])) {

            $admin = $this->getConfig('admin');

            if (
                // Default password, unencrypted.
                $admin['user']  === $postParams['s_user'] && 
                'shieldon_pass' === $postParams['s_pass'] &&
                'shieldon_pass' === $admin['pass']
            ) {
                $login = true;

            } elseif (
                // User has already changed password, encrypted.
                $admin['user'] === $postParams['s_user'] && 
                password_verify($postParams['s_pass'], $admin['pass'])
            ) {
                $login = true;
    
            } else {
                $data['error'] = __('panel', 'login_message_invalid_user_or_pass', 'Invalid username or password.');
            }

            // Check the response from Captcha modules.
            foreach ($this->captcha as $captcha) {
                if (!$captcha->response()) {
                    $login = false;
                    $data['error'] = __('panel', 'login_message_invalid_captcha', 'Invalid Captcha code.');
                }
            }
        }

        if ($login) {

            // This session variable is to mark current session as a logged user.
            get_session()->set('shieldon_user_login', true);

            // Redirect to overview page if logged in successfully.
            return get_response()->withHeader('Location', $this->url('home/overview'));
        }

        // Start to prompt a login form is not logged.
        define('SHIELDON_VIEW', true);

        // `$ui` will be used in `css-default.php`. Do not remove it.
        $ui = [
            'background_image' => '',
            'bg_color'         => '#ffffff',
            'header_bg_color'  => '#212531',
            'header_color'     => '#ffffff',
            'shadow_opacity'   => '0.2',
        ];

        $data['css'] = require $this->kernel::KERNEL_DIR . '/../../templates/frontend/css/default.php';
        unset($ui);

        $data['title'] = __('panel', 'title_login', 'Login');

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
        $sessionLoginStatus = get_session()->get('shieldon_user_login');
        $sessionPanelLang = get_session()->get('shieldon_panel_lang');
        $response = get_response();

        if (isset($sessionLoginStatus)) {
            unset_superglobal('shieldon_user_login', 'session');
        }

        if (isset($sessionPanelLang)) {
            unset_superglobal('shieldon_panel_lang', 'session');
        }

        return $response->withdHeader('Location', $this->url('user/login'));
    }

    /**
     * Set the Captcha modules.
     *
     * @return void
     */
    protected function applyCaptchaForms(): void
    {
        $this->captcha[] = new Captcha\Foundation();

        $recaptchaSetting = $this->getConfig('captcha_modules.recaptcha');
        $imageSetting = $this->getConfig('captcha_modules.image');

        if ($recaptchaSetting['enable']) {

            $googleRecaptcha = [
                'key'     => $recaptchaSetting['config']['site_key'],
                'secret'  => $recaptchaSetting['config']['secret_key'],
                'version' => $recaptchaSetting['config']['version'],
                'lang'    => $recaptchaSetting['config']['lang'],
            ];

            $this->captcha[] = new Captcha\Recaptcha($googleRecaptcha);
        }

        if ($imageSetting['enable']) {

            $type = $imageSetting['config']['type'] ?? 'alnum';
            $length = $imageSetting['config']['length'] ?? 8;

            switch ($type) {
                case 'numeric':
                    $imageCaptchaConfig['pool'] = '0123456789';
                    break;

                case 'alpha':
                    $imageCaptchaConfig['pool'] = '0123456789abcdefghijklmnopqrstuvwxyz';
                    break;

                case 'alnum':
                default:
                    $imageCaptchaConfig['pool'] = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
            }

            $imageCaptchaConfig['word_length'] = $length;

            $this->captcha[] = new Captcha\ImageCaptcha($imageCaptchaConfig);
        }
    }
}

