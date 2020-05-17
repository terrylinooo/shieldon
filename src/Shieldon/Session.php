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
class Session
{
    /**
     * @var string
     */
    public $id;

    /**
     * @var array
     */
    protected $sessionParams;

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

        $this->sessionParams = $_SESSION;

        Container::set('session', $this);

        return $this->id;
    }

    /**
     * Get session value from $_SESSION by key.
     *
     * @param string $name
     *
     * @return string|array
     */
    public function get($name)
    {
        return $this->sessionParams[$name] ?? '';
    }

    /**
     * To store data in the session.
     *
     * @param string $name
     *
     * @return void
     */
    public function save($name, $value)
    {
        $this->sessionParams[$name] = $value;
    }

    /**
     * To delete data in the session.
     *
     * @param string $name
     *
     * @return void
     */
    public function delete($name)
    {
        if (isset($this->sessionParams[$name])) {
            unset($this->sessionParams[$name]);
        }
    }

    /**
     * To determine if an item is present in the session.
     *
     * @param string $name
     *
     * @return bool
     */
    public function has($name)
    {
        return isset($this->sessionParams[$name]);
    }
}
