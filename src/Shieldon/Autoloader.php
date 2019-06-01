<?php

/*
 * This file is part of the Shieldon package.
 *
 * (c) Terry L. <contact@terryl.in>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Shieldon;

class Autoloader
{
    private $dir;
    private $namespace;

    // @codeCoverageIgnoreStart
    public function __construct()
    {
        $this->dir = __DIR__;
        $this->prefix = __NAMESPACE__ . '\\';
    }

    public static function register()
    {
        spl_autoload_register(array(new self(), 'autoload'), true, false);
    }
    // @codeCoverageIgnoreEnd

    public function autoload($className)
    {
        if (0 === strpos($className, $this->prefix)) {
            $parts = explode('\\', substr($className, strlen($this->prefix)));
            $filepath = $this->dir . '/' . implode('/', $parts) . '.php';

            if (is_file($filepath)) {
                require $filepath;
            }
        }
    }
}
