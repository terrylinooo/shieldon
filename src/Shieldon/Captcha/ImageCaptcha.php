<?php declare(strict_types=1);
/*
 * This file is part of the Shieldon package.
 *
 * (c) Terry L. <contact@terryl.in>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Shieldon\Captcha;

use function base64_encode;
use function cos;
use function function_exists;
use function imagecolorallocate;
use function imagecreate;
use function imagecreatetruecolor;
use function imagedestroy;
use function imagefilledrectangle;
use function imagejpeg;
use function imageline;
use function imagepng;
use function imagerectangle;
use function imagestring;
use function mt_rand;
use function ob_end_clean;
use function ob_get_contents;
use function password_hash;
use function password_verify;
use function random_int;
use function sin;
use function strlen;

class ImageCaptcha implements CaptchaInterface
{
    /**
     * Settings.
     *
     * @var array
     */
    protected $properties = [];


    /**
     * Image type.
     *
     * @var string
     */
    protected $imageType = '';

    /**
     * Word.
     *
     * @var string
     */
    protected $word = '';

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
        $defaults = [
            'img_width'	  => 250,
            'img_height'  => 50,
            'word_length' => 8,
            'font_spacing' => 10,
            'pool'		  => '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ',
            'colors'	  => [
                'background' => [255, 255, 255],
                'border'	 => [153, 200, 255],
                'text'		 => [51, 153, 255],
                'grid'		 => [153, 200, 255]
            ]
        ];

        foreach ($defaults as $k => $v) {
            if (isset($config[$k])) {
                $this->properties[$k] = $config[$k];
            } else {
                $this->properties[$k] = $defaults[$k];
            }
        }

        if (! is_array($this->properties['colors'])) {
            $this->properties['colors'] = $defaults['colors'];
        }

        foreach ($defaults['colors'] as $k => $v) {
            if (! is_array($this->properties['colors'][$k])) {
                $this->properties['colors'][$k] = $defaults['colors'][$k];
            }
        }
    }

    /**
     * Response the result.
     *
     * @return bool
     */
    public function response(): bool
    {
        if (empty($_POST['shieldon_image_captcha']) || empty($_SESSION['shieldon_image_captcha_hash'])) {
            return false;
        }

        $flag = false;

        if (password_verify($_POST['shieldon_image_captcha'], $_SESSION['shieldon_image_captcha_hash'])) {
            $flag = true;
        }

        // Prevent detecting POST method on RESTful frameworks.
        unset($_POST['shieldon_image_captcha']);

        return $flag;
    }

    /**
     * Output a required HTML.
     *
     * @return string
     */
    public function form(): string
    {
        // @codeCoverageIgnoreStart
        if (! extension_loaded('gd')) {
            return '';
        }
        // @codeCoverageIgnoreEnd

        $html = '';
        $base64image = $this->createCaptcha();

        if (! empty($base64image)) {
            $html = '<div style="padding: 0px; overflow: hidden; margin: 10px 0;">';
            $html .= '<div style="
                border: 1px #dddddd solid;
                overflow: hidden;
                border-radius: 3px;
                display: inline-block;
                padding: 5px;
                box-shadow: 0px 0px 4px 1px rgba(0,0,0,0.08);">';
            $html .= '<div style="margin-bottom: 2px;"><img src="data:image/' . $this->imageType . ';base64,' . $base64image . '" style="width: ' . $this->properties['img_width'] . '; height: ' . $this->properties['img_height'] . ';"></div>';
            $html .= '<div><input type="text" name="shieldon_image_captcha" style="
                width: 100px;
                border: 1px solid rgba(27,31,35,.2);
                border-radius: 3px;
                background-color: #fafafa;
                font-size: 14px;
                font-weight: bold;
                line-height: 20px;
                box-shadow: inset 0 1px 2px rgba(27,31,35,.075);
                vertical-align: middle;
                padding: 6px 12px;;"></div>';
            $html .= '</div>';
            $html .= '</div>';
        }

        return $html;
    }

    /**
     * Create CAPTCHA
     *
     * @return	string
     */
    protected function createCaptcha()
    {
        $this->word = '';
        $poolLength = strlen($this->properties['pool']);
        $randMax = $poolLength - 1;

        for ($i = 0; $i < $this->properties['word_length']; $i++) {
            $this->word .= $this->properties['pool'][random_int(0, $randMax)];
        }

        // Determine angle and position.
        $length	= strlen($this->word);
        $angle	= ($length >= 6) ? mt_rand(-($length - 6), ($length - 6)) : 0;
        $xAxis	= mt_rand(6, (360 / $length) - 16);
        $yAxis  = ($angle >= 0) ? mt_rand($this->properties['img_height'], $this->properties['img_width']) : mt_rand(6, $this->properties['img_height']);

        // Create image.
        if (function_exists('imagecreatetruecolor')) {
            $im = imagecreatetruecolor($this->properties['img_width'], $this->properties['img_height']);

        // @codeCoverageIgnoreStart
        } else {
            $im = imagecreate($this->properties['img_width'], $this->properties['img_height']);
        }
        // @codeCoverageIgnoreEnd

        // Assign colors.
        $colors = [];
        foreach ($this->properties['colors'] as $k => $v) {
            $colors[$k] = imagecolorallocate($im, $v[0], $v[1], $v[2]);
        }

        // Create the rectangle.
        imagefilledrectangle($im, 0, 0, $this->properties['img_width'], $this->properties['img_height'], $colors['background']);

        // Create the spiral pattern.
        $theta		= 1;
        $thetac		= 7;
        $radius		= 16;
        $circles	= 20;
        $points		= 32;

        for ($i = 0, $cp = ($circles * $points) - 1; $i < $cp; $i++) {
            $theta += $thetac;
            $rad = $radius * ($i / $points);

            $x = (int) (($rad * cos($theta)) + $xAxis);
            $y = (int) (($rad * sin($theta)) + $yAxis);

            $theta += $thetac;
            $rad1 = $radius * (($i + 1) / $points);

            $x1 = (int) (($rad1 * cos($theta)) + $xAxis);
            $y1 = (int) (($rad1 * sin($theta)) + $yAxis);

            imageline($im, $x, $y, $x1, $y1, $colors['grid']);
            $theta -= $thetac;
        }

        // Write the text
        $z = (int) ($this->properties['img_width'] / ($length / 3));
        $x = mt_rand(0, $z);
        $y = 0;

        for ($i = 0; $i < $length; $i++) {
            $y = mt_rand(0 , $this->properties['img_height'] / 2);
            imagestring($im, 5, $x, $y, $this->word[$i], $colors['text']);
            $x += ($this->properties['font_spacing'] * 2);
        }

        // Create the border.
        imagerectangle($im, 0, 0, $this->properties['img_width'] - 1, $this->properties['img_height'] - 1, $colors['border']);

        // Generate image in base64 string.
        ob_start ();

        if (function_exists('imagejpeg')) {
            $this->imageType = 'jpeg';
            imagejpeg($im);

        // @codeCoverageIgnoreStart
        } elseif (function_exists('imagepng')) {
            $this->imageType = 'png';
            imagepng($im);
        } else {
            echo '';
        }
        // @codeCoverageIgnoreEnd

        $image_data = ob_get_contents();
        ob_end_clean();
        imagedestroy($im);

        // Save hash.
        $_SESSION['shieldon_image_captcha_hash'] = password_hash($this->word, PASSWORD_BCRYPT);

        return base64_encode($image_data);
    }
}
