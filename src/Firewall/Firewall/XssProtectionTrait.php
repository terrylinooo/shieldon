<?php
/*
 * This file is part of the Shieldon package.
 *
 * (c) Terry L. <contact@terryl.in>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Shieldon\Firewall\Firewall;

use Shieldon\Firewall\Security\Xss;

use function array_keys;

/*
 * Xss Protection Trait is loaded in Firewall instance only.
 */
trait XssProtectionTrait
{
    /**
     * Set XSS protection.
     *
     * @return void
     */
    protected function setXssProtection(): void
    {
        $xssProtectionOptions = $this->getOption('xss_protection');

        $xssFilter = new Xss();

        if ($xssProtectionOptions['post']) {
            $this->kernel->setClosure('xss_post', function() use ($xssFilter) {
                if (!empty($_POST)) {
                    foreach (array_keys($_POST) as $k) {
                        $_POST[$k] = $xssFilter->clean($_POST[$k]);
                    }
                }
            });
        }

        if ($xssProtectionOptions['get']) {
            $this->kernel->setClosure('xss_get', function() use ($xssFilter) {
                if (!empty($_GET)) {
                    foreach (array_keys($_GET) as $k) {
                        $_GET[$k] = $xssFilter->clean($_GET[$k]);
                    }
                }
            });
        }

        if ($xssProtectionOptions['cookie']) {
            $this->kernel->setClosure('xss_cookie', function() use ($xssFilter) {
                if (!empty($_COOKIE)) {
                    foreach (array_keys($_COOKIE) as $k) {
                        $_COOKIE[$k] = $xssFilter->clean($_COOKIE[$k]);
                    }
                }
            });
        }

        $xssProtectedList = $this->getOption('xss_protected_list');

        if (!empty($xssProtectedList)) {
        
            $this->kernel->setClosure('xss_protection', function() use ($xssFilter, $xssProtectedList) {

                foreach ($xssProtectedList as $v) {
                    $k = $v['variable'] ?? 'undefined';
    
                    switch ($v['type']) {

                        case 'get':

                            if (!empty($_GET[$k])) {
                                $_GET[$k] = $xssFilter->clean($_GET[$k]);
                            }
                            break;
    
                        case 'post':
    
                            if (!empty($_POST[$k])) {
                                $_POST[$k] = $xssFilter->clean($_POST[$k]);
                            }
                            break;
    
                        case 'cookie':

                            if (!empty($_COOKIE[$k])) {
                                $_COOKIE[$k] = $xssFilter->clean($_COOKIE[$k]);
                            }
                            break;
    
                        default:
                    }
                }
            });
        }
    }
}
