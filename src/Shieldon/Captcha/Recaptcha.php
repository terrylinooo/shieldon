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

namespace Shieldon\Captcha;

use function curl_error;
use function curl_exec;
use function curl_init;
use function curl_setopt;
use function json_decode;

class Recaptcha extends CaptchaProvider
{
    protected $key = '';
    protected $secret = '';
    protected $version = 'v2';
    protected $lang = 'en';

    protected $googleServiceUrl = 'https://www.google.com/recaptcha/api/siteverify';

    /**
     * Constructor.
     *
     * It will implement default configuration settings here.
     *
     * @array $config
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
        $post = get_request()->getParsedBody();

        if (empty($post['g-recaptcha-response'])) {
            return false;
        }

        $flag = false;
        $reCaptchaToken = str_replace(["'", '"'], '', $post['g-recaptcha-response']);

        $postData = [
            'secret' => $this->secret,
            'response' => $reCaptchaToken,
        ];

        $ch = curl_init();
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

        if (isset($ret) && $ret != false) {
            $tmp = json_decode($ret);
            if ($tmp->success == true) {
                $flag = true;
            }
        }

        curl_close($ch);

        // Prevent detecting POST method on RESTful frameworks.
        unset($_POST['g-recaptcha-response']);

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
            $html .= '<script src="https://www.google.com/recaptcha/api.js?render=' . $this->key . '&hl=' . $this->lang . '"></script>';
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
