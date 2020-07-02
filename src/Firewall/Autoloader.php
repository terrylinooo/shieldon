<?php
/*
 * This file is part of the Shieldon package.
 *
 * (c) Terry L. <contact@terryl.in>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

// @codeCoverageIgnoreStart

namespace Shieldon\Firewall;

/**
 * Autoloader
 * 
 * A PRS-4 autoloader for the developers who don't want to use PHP Composer.
 */
class Autoloader
{
    /**
     * Current directory.
     *
     * @var string
     */
    private $dir;

    /**
     * The namespace prefix.
     *
     * @var string
     */
    private $prefix;

    public function __construct()
    {
        $this->dir = __DIR__;
        $this->prefix = __NAMESPACE__ . '\\';
    }

    /**
     * Register
     *
     * @return void
     */
    public static function register()
    {
        spl_autoload_register([new self(), 'autoload'], true, false);
    }

    /**
     * Autoload
     *
     * @param string $className
     * 
     * @return void
     */
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

// @codeCoverageIgnoreEnd