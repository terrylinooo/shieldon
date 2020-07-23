<?php
/*
 * This file is part of the Shieldon Firewall package.
 *
 * (c) Terry L. <contact@terryl.in>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

/**
 * Register to PSR-4 autoloader.
 *
 * @return void
 */
function shieldon_firewall_register()
{
    spl_autoload_register('shieldon_firewall_autoload', true, false);
}

/**
 * PSR-4 autoloader.
 *
 * @param string $className
 * 
 * @return void
 */
function shieldon_firewall_autoload($className)
{
    $prefix = 'Shieldon\\Firewall\\';
    $dir = __DIR__ . '/src/Firewall';

    if (0 === strpos($className, $prefix)) {
        $parts = explode('\\', substr($className, strlen($prefix)));
        $filepath = $dir . '/' . implode('/', $parts) . '.php';

        if (is_file($filepath)) {
            include $filepath;
        }
    }
}

shieldon_firewall_register();