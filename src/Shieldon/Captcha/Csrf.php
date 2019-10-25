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

class Csrf implements CaptchaInterface
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
     * @array $config
     *
     * @return void
     */
    public function __construct(array $config = [])
    {
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
