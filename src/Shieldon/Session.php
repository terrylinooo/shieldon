<?php declare(strict_types=1);
/*
 * This file is part of the Shieldon package.
 *
 * (c) Terry L. <contact@terryl.in>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Shieldon;

use function php_sapi_name;
use function session_id;
use function session_start;
use function session_status;

/*
 * A simple SESSION wrapper.
 *
 * @since 1.1.0
 */
class Session extends Collection
{
    /**
     * @var string
     */
    public $id;

    /**
     * Constructor.
     * 
     * @param $id Session ID
     */
    public function __construct($id = '')
    {
        if ($id !== '') {
            $this->id = $id;

        } else {
            if ((php_sapi_name() !== 'cli')) {
                if (session_status() === PHP_SESSION_NONE) {
                    session_start();
                }
                if (! $this->id) {
                    $this->id = session_id();
                }
            }
        }

        // In CLI environment it will be null so that we give it a default value.
        if (! isset($_SESSION) || ! is_array($_SESSION)) {
            $_SESSION = [];
        }

        parent::__construct($_SESSION);

        Container::set('session', $this, true);

        return $this->id;
    }
}
