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

/**
 * Add form fields for the CSRF features of some frameworks.
 */
class Csrf extends CaptchaProvider
{
    /**
     * Form input name.
     *
     * @var string
     */
    protected $name = '';

    /**
     * Form input value.
     *
     * @var string
     */
    protected $value = '';

    /**
     * Constructor.
     *
     * It will implement default configuration settings here.
     *
     * @param array $config The field of the CSRF configuration.
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
     * Response the result.
     *
     * @return bool
     */
    public function response(): bool
    {
        return true;
    }

    /**
     * Output a required HTML.
     *
     * @return string
     */
    public function form(): string
    {
        $html = '<input type="hidden" name="' . $this->name . '" value="' . $this->value . '">';

        return $html;
    }
}
