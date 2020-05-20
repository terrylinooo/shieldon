<?php declare(strict_types=1);
/*
 * This file is part of the Messenger package.
 *
 * (c) Terry L. <contact@terryl.in>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Shieldon\Mock;

/**
 * For unit-testing purpose.
 * 
 * @author Terry L. <contact@terryl.in>
 * @since 1.0.0
 */
class MockRequest
{
    /**
     * @var \Shieldon\Request
     */
    public $request;

    /**
     * @var \Shieldon\Session
     */
    public $session;

    /**
     * Mapping to $_POST
     *
     * @var \Shieldon\Collection
     */
    public $post;

    /**
     * Mapping to $_COOKIE
     *
     * @var \Shieldon\Collection
     */
    public $cookie;

    /**
     * Mapping to $_SERVER
     *
     * @var \Shieldon\Collection
     */
    public $server;

    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->apply();
    }

    public function apply()
    {
        foreach (['_POST', '_COOKIE', '_SERVER'] as $key => $value) {
            if (! isset(${$value}) || ! is_array(${$value})) {
                ${$value} = [];
            }
        }

        $this->request = new \Shieldon\Request();
        $this->session = new \Shieldon\Session();
        $this->post    = new \Shieldon\Collection($_POST);
        $this->cookie  = new \Shieldon\Collection($_COOKIE);
        $this->server  = new \Shieldon\Collection($_SERVER);
    }
}