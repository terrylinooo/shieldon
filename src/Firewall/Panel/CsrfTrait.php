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

use function count;
use function is_string;

/*
 * Tradit for demonstration.
 */
trait CsrfTrait
{
    /**
     * See $this->csrf()
     *
     * @var array
     */
    protected $csrfField = [];

    /**
     * Most popular PHP framework has a built-in CSRF protection such as Laravel.
     * We need to pass the CSRF token for our form actions.
     *
     * @param string|array ...$csrfparams The arguments.
     *
     * @return void
     */
    public function csrf(...$csrfparams): void
    {
        foreach ($csrfparams as $value) {
            foreach ($value as $k => $v) {
                $this->csrfField[] = [
                    'name'  => $k,
                    'value' => $v,
                ];
            }
        }
    }

    /**
     * Output HTML input element with CSRF token.
     *
     * @return string
     */
    public function fieldCsrf(): string
    {
        $string = '';
        if (!empty($this->csrfField)) {
            foreach ($this->csrfField as $value) {
                $string .= '<input type="hidden" name="' . $value['name'] . '" value="' . $value['value'] . '" id="csrf-field">';
            }
        }
        return $string;
    }

    /**
     * Get CSRF input fields.
     *
     * @return array
     */
    public function getCsrfField(): array
    {
        return $this->csrfField;
    }

    /**
     * Set CSRF input fields.
     *
     * @param array $csrfParams
     *
     * @return void
     */
    public function setCsrfField(array $csrfParams): void
    {
        $this->csrfField = $csrfParams;
    }
}
