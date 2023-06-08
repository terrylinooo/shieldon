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

namespace Shieldon\Firewall\Captcha;

use Shieldon\Firewall\Captcha\CaptchaProvider;

use function Shieldon\Firewall\get_request;
use function Shieldon\Firewall\unset_superglobal;
use CurlHandle; // PHP 8
use function curl_error;
use function curl_exec;
use function curl_init;
use function curl_setopt;
use function is_string;
use function json_decode;

/**
 * Google reCaptcha.
 */
class ReCaptcha extends CaptchaProvider
{
    /**
     * The site key.
     *
     * @var string
     */
    protected $key = '';

    /**
     * The secret key.
     *
     * @var string
     */
    protected $secret = '';

    /**
     * The version.
     *
     * @var string
     */
    protected $version = 'v2';

    /**
     * The language code of the UI.
     *
     * @var string
     */
    protected $lang = 'en';

    /**
     * The URL of Google ReCaptcha API.
     *
     * @var string
     */
    protected $googleServiceUrl = 'https://www.google.com/recaptcha/api/siteverify';

    /**
     * Constructor.
     *
     * It will implement default configuration settings here.
     *
     * @param array $config The settings of Google ReCpatcha.
     *
     * @return void
     */
    public function __construct(array $config = [])
    {
        parent::__construct();
        
        foreach ($config as $k => $v) {
            if (isset($this->{$k})) {
                $this->{$k} = $v;
            }
        }
    }

    /**
     * Response the result from Google service server.
     *
     * @return bool
     */
    public function response(): bool
    {
        $postParams = get_request()->getParsedBody();

        if (empty($postParams['g-recaptcha-response'])) {
            return false;
        }

        $flag = false;
        $reCaptchaToken = str_replace(["'", '"'], '', $postParams['g-recaptcha-response']);

        $postData = [
            'secret' => $this->secret,
            'response' => $reCaptchaToken,
        ];

        $ch = curl_init();

        if (version_compare(phpversion(), '8.0.0', '>=')) {
            if (!$ch instanceof CurlHandle) {
                // @codeCoverageIgnoreStart
                return false;
                // @codeCoverageIgnoreEnd
            }
        } else {
            if (!is_resource($ch)) {
                // @codeCoverageIgnoreStart
                return false;
                // @codeCoverageIgnoreEnd
            }
        }

        curl_setopt($ch, CURLOPT_URL, $this->googleServiceUrl);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Expect:'));
        curl_setopt($ch, CURLOPT_POST, 2);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        $ret = curl_exec($ch);

        // @codeCoverageIgnoreStart
        if (curl_errno($ch)) {
            echo 'error:' . curl_error($ch);
        }
        // @codeCoverageIgnoreEnd

        if (isset($ret) && is_string($ret)) {
            $tmp = json_decode($ret);
            if ($tmp->success == true) {
                $flag = true;
            }
        }

        curl_close($ch);

        // Prevent detecting POST method on RESTful frameworks.
        unset_superglobal('g-recaptcha-response', 'post');

        return $flag;
    }

    /**
     * Output a required HTML for reCaptcha v2.
     *
     * @return string
     */
    public function form(): string
    {
        $html = '<div>';
        $html .= '<div style="display: inline-block">';
        if ('v3' !== $this->version) {
            $html .= '<script src="https://www.google.com/recaptcha/api.js?hl=' . $this->lang . '"></script>';
            $html .= '<div class="g-recaptcha" data-sitekey="' . $this->key . '"></div>';
        } else {
            $html .= '<input type="hidden" name="g-recaptcha-response" id="g-recaptcha-response" value="">';
            $html .= '<script src="https://www.google.com/recaptcha/api.js?render=' .
                $this->key .'&hl=' . $this->lang . '"></script>';
            $html .= '<script>';
            $html .= '    grecaptcha.ready(function() {';
            $html .= '        grecaptcha.execute("' . $this->key . '", {action: "homepage"}).then(function(token) {';
            $html .= '            document.getElementById("g-recaptcha-response").value = token;';
            $html .= '        }); ';
            $html .= '    });';
            $html .= '</script>';
        }
        $html .= '</div>';
        $html .= '</div>';

        return $html;
    }
}
