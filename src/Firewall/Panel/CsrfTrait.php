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
        $count = count($csrfparams);

        if (1 === $count) {
            foreach ($csrfparams as $key => $value) {
                $this->csrfField[] = [
                    'name' => $key,
                    'value' => $value,
                ];
            }

        } elseif (2 === $count) {

            if (!empty($csrfparams[0]) && is_string($csrfparams[0])) {
                $csrfKey = $csrfparams[0];
            }
    
            if (!empty($csrfparams[1]) && is_string($csrfparams[1])) {
                $csrfValue = $csrfparams[1];
            }

            if (!empty($csrfKey)) {
                $this->csrfField[] = [
                    'name' => $csrfKey,
                    'value' => $csrfValue,
                ];
            }
        }
    }

    /**
     * Output HTML input element with CSRF token.
     *
     * @return void
     */
    public function fieldCsrf(): void
    {
        if (!empty($this->csrfField)) {
            foreach ($this->csrfField as $value) {
                echo '<input type="hidden" name="' . $value['name'] . '" value="' . $value['value'] . '" id="csrf-field">';
            }
        }
    }
}
