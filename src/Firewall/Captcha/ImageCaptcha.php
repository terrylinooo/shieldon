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

use RuntimeException;
use GdImage; // PHP 8
use Shieldon\Firewall\Captcha\CaptchaProvider;

use function Shieldon\Firewall\get_request;
use function Shieldon\Firewall\get_session_instance;
use function Shieldon\Firewall\unset_superglobal;
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

/**
 * Simple Image Captcha.
 */
class ImageCaptcha extends CaptchaProvider
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
     * Image resource.
     * Throw exception the the value is not resource.
     *
     * @var resource|null|bool
     */
    private $im;

    /**
     * The length of the word.
     *
     * @var int
     */
    protected $length = 4;

    /**
     * Constructor.
     *
     * It will implement default configuration settings here.
     *
     * @param array $config The settings for creating Captcha.
     *
     * @return void
     */
    public function __construct(array $config = [])
    {
        $defaults = [
            'img_width'    => 250,
            'img_height'   => 50,
            'word_length'  => 8,
            'font_spacing' => 10,
            'pool'         => '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ',
            'colors'       => [
                'background' => [255, 255, 255],
                'border'     => [153, 200, 255],
                'text'       => [51,  153, 255],
                'grid'       => [153, 200, 255],
            ],
        ];

        foreach ($defaults as $k => $v) {
            if (isset($config[$k])) {
                $this->properties[$k] = $config[$k];
            } else {
                $this->properties[$k] = $defaults[$k];
            }
        }

        if (!is_array($this->properties['colors'])) {
            $this->properties['colors'] = $defaults['colors'];
        }

        foreach ($defaults['colors'] as $k => $v) {
            if (!is_array($this->properties['colors'][$k])) {
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
        $postParams = get_request()->getParsedBody();
        $sessionCaptchaHash = get_session_instance()->get('shieldon_image_captcha_hash');

        if (empty($postParams['shieldon_image_captcha']) || empty($sessionCaptchaHash)) {
            return false;
        }

        $flag = false;

        if (password_verify($postParams['shieldon_image_captcha'], $sessionCaptchaHash)) {
            $flag = true;
        }

        // Prevent detecting POST method on RESTful frameworks.
        unset_superglobal('shieldon_image_captcha', 'post');

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
        if (!extension_loaded('gd')) {
            return '';
        }
        // @codeCoverageIgnoreEnd

        $html = '';
        $base64image = $this->createCaptcha();
        $imgWidth = $this->properties['img_width'];
        $imgHeight = $this->properties['img_height'];

        if (!empty($base64image)) {
            $html = '<div style="padding: 0px; overflow: hidden; margin: 10px 0;">';
            $html .= '<div style="
                border: 1px #dddddd solid;
                overflow: hidden;
                border-radius: 3px;
                display: inline-block;
                padding: 5px;
                box-shadow: 0px 0px 4px 1px rgba(0,0,0,0.08);">';
            $html .= '<div style="margin-bottom: 2px;"><img src="data:image/' .
                $this->imageType . ';base64,' .
                $base64image . '" style="width: ' .
                $imgWidth . '; height: ' .
                $imgHeight . ';"></div>';
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
     * @return string
     */
    protected function createCaptcha()
    {
        $imgWidth = $this->properties['img_width'];
        $imgHeight = $this->properties['img_height'];

        $this->createCanvas($imgWidth, $imgHeight);

        $im = $this->getImageResource();

        // Assign colors.
        $colors = [];

        foreach ($this->properties['colors'] as $k => $v) {

            /**
             * Create color identifier for each color.
             *
             * @var int
             */
            $colors[$k] = imagecolorallocate($im, $v[0], $v[1], $v[2]);
        }

        $this->createRandomWords();

        $this->createBackground(
            $imgWidth,
            $imgHeight,
            $colors['background']
        );

        $this->createSpiralPattern(
            $imgWidth,
            $imgHeight,
            $colors['grid']
        );

        $this->writeText(
            $imgWidth,
            $imgHeight,
            $colors['text']
        );

        $this->createBorder(
            $imgWidth,
            $imgHeight,
            $colors['border']
        );

        // Save hash to the user sesssion.
        $hash = password_hash($this->word, PASSWORD_BCRYPT);

        get_session_instance()->set('shieldon_image_captcha_hash', $hash);
        get_session_instance()->save();

        return $this->getImageBase64Content();
    }

    /**
     * Prepare the random words that want to display to front.
     *
     * @return void
     */
    private function createRandomWords()
    {
        $this->word = '';

        $poolLength = strlen($this->properties['pool']);
        $randMax = $poolLength - 1;

        for ($i = 0; $i < $this->properties['word_length']; $i++) {
            $this->word .= $this->properties['pool'][random_int(0, $randMax)];
        }

        $this->length = strlen($this->word);
    }

    /**
     * Create a canvas.
     *
     * This method initialize the $im.
     *
     * @param int $imgWidth  The width of the image.
     * @param int $imgHeight The height of the image.
     *
     * @return void
     */
    private function createCanvas(int $imgWidth, int $imgHeight)
    {
        if (function_exists('imagecreatetruecolor')) {
            $this->im = imagecreatetruecolor($imgWidth, $imgHeight);
    
            // @codeCoverageIgnoreStart
        } else {
            $this->im = imagecreate($imgWidth, $imgHeight);
        }

        // @codeCoverageIgnoreEnd
    }

    /**
     * Create the background.
     *
     * @param int $imgWidth  The width of the image.
     * @param int $imgHeight The height of the image.
     * @param int $bgColor   The RGB color for the background of the image.
     *
     * @return void
     */
    private function createBackground(int $imgWidth, int $imgHeight, $bgColor)
    {
        $im = $this->getImageResource();

        imagefilledrectangle($im, 0, 0, $imgWidth, $imgHeight, $bgColor);
    }

    /**
     * Create a spiral patten.
     *
     * @param int $imgWidth  The width of the image.
     * @param int $imgHeight The height of the image.
     * @param int $gridColor The RGB color for the gri of the image.
     *
     * @return void
     */
    private function createSpiralPattern(int $imgWidth, int $imgHeight, $gridColor)
    {
        $im = $this->getImageResource();

        // Determine angle and position.
        $angle = ($this->length >= 6) ? mt_rand(-($this->length - 6), ($this->length - 6)) : 0;
        $xAxis = mt_rand(6, (360 / $this->length) - 16);
        $yAxis = ($angle >= 0) ? mt_rand($imgHeight, $imgWidth) : mt_rand(6, $imgHeight);

        // Create the spiral pattern.
        $theta   = 1;
        $thetac  = 7;
        $radius  = 16;
        $circles = 20;
        $points  = 32;

        for ($i = 0, $cp = ($circles * $points) - 1; $i < $cp; $i++) {
            $theta += $thetac;
            $rad = $radius * ($i / $points);

            $x = (int) (($rad * cos($theta)) + $xAxis);
            $y = (int) (($rad * sin($theta)) + $yAxis);

            $theta += $thetac;
            $rad1 = $radius * (($i + 1) / $points);

            $x1 = (int) (($rad1 * cos($theta)) + $xAxis);
            $y1 = (int) (($rad1 * sin($theta)) + $yAxis);

            imageline($im, $x, $y, $x1, $y1, $gridColor);
            $theta -= $thetac;
        }
    }

    /**
     * Write the text into the image canvas.
     *
     * @param int $imgWidth  The width of the image.
     * @param int $imgHeight The height of the image.
     * @param int $textColor The RGB color for the grid of the image.
     *
     * @return void
     */
    private function writeText(int $imgWidth, int $imgHeight, $textColor)
    {
        $im = $this->getImageResource();

        $z = (int) ($imgWidth / ($this->length / 3));
        $x = mt_rand(0, $z);
        // $y = 0;

        for ($i = 0; $i < $this->length; $i++) {
            $y = mt_rand(0, $imgHeight / 2);
            imagestring($im, 5, $x, $y, $this->word[$i], $textColor);
            $x += ($this->properties['font_spacing'] * 2);
        }
    }

    /**
     * Write the text into the image canvas.
     *
     * @param int $imgWidth    The width of the image.
     * @param int $imgHeight   The height of the image.
     * @param int $borderColor The RGB color for the border of the image.
     *
     * @return void
     */
    private function createBorder(int $imgWidth, int $imgHeight, $borderColor): void
    {
        $im = $this->getImageResource();

        // Create the border.
        imagerectangle($im, 0, 0, $imgWidth - 1, $imgHeight - 1, $borderColor);
    }

    /**
     * Get the base64 string of the image.
     *
     * @return string
     */
    private function getImageBase64Content(): string
    {
        $im = $this->getImageResource();

        // Generate image in base64 string.
        ob_start();

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

        $imageContent = ob_get_contents();
        ob_end_clean();
        imagedestroy($im);

        return base64_encode($imageContent);
    }

    /**
     * Get image resource.
     *
     * @return resource|GdImage
     */
    private function getImageResource()
    {
        if (version_compare(phpversion(), '8.0.0', '>=')) {
            if (!$this->im instanceof GdImage) {
                // @codeCoverageIgnoreStart
                throw new RuntimeException(
                    'Cannot create image resource.'
                );
                // @codeCoverageIgnoreEnd
            }
        } else {
            if (!is_resource($this->im)) {
                // @codeCoverageIgnoreStart
                throw new RuntimeException(
                    'Cannot create image resource.'
                );
                // @codeCoverageIgnoreEnd
            }
        }

        return $this->im;
    }
}
