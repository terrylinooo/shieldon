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

namespace Shieldon;

/**
 * Autoloader
 * 
 * A PRS-4 autoloader for the developers who don't want to use PHP Composer.
 *
 * @since 1.0.0
 */
class Autoloader
{
    /**
     * Current level directory.
     *
     * @var string
     */
    private $dir;

    /**
     * The prefix of classes applied to autoloader.
     *
     * @var string
     */
    private $prefix;

    /**
     * Autoloader constructor.
     */
    public function __construct()
    {
        $this->dir = __DIR__;
        $this->prefix = __NAMESPACE__ . '\\';
    }

    /**
     * Register to autoloader.
     *
     * @return void
     */
    public static function register(): void
    {
        spl_autoload_register(
            [
                new self(), 'autoload'
            ], 
            true,
            false
        );
    }

    /**
     * The rule of autoloader.
     *
     * @param string $className The class name, may include namespace.
     *
     * @return void
     */
    public function autoload(string $className): void
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
