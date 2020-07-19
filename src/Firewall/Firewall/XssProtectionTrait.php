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
        $enable = $this->getOption('xss_protection');
        $protectedList = $this->getOption('xss_protected_list');
        $key = array_search(true, $enable);

        if (empty($key) && empty($protectedList)) {
            return;
        }

        $xss = new Xss();

        $this->cleanPost($enable, $xss);
        $this->cleanGet($enable, $xss);
        $this->cleanCookie($enable, $xss);
        $this->cleanProtectedList($protectedList, $xss);
    }

    /**
     * Clean the $_POST superglobal.
     *
     * @param array $enable
     * @param Xss   $xss
     *
     * @return void
     */
    private function cleanPost(array $enable, Xss $xss): void
    {
        if ($enable['post']) {
            $this->kernel->setClosure('xss_post', function() use ($xss) {
                if (!empty($_POST)) {
                    foreach (array_keys($_POST) as $k) {
                        $_POST[$k] = $xss->clean($_POST[$k]);
                    }
                }
            });
        }
    }

    /**
     * Clean the $_GET superglobal.
     *
     * @param array $enable
     * @param Xss   $xss
     *
     * @return void
     */
    private function cleanGet(array $enable, Xss $xss): void
    {
        if ($enable['get']) {
            $this->kernel->setClosure('xss_get', function() use ($xss) {
                if (!empty($_GET)) {
                    foreach (array_keys($_GET) as $k) {
                        $_GET[$k] = $xss->clean($_GET[$k]);
                    }
                }
            });
        }
    }

    /**
     * Clean the $_COOKIE superglobal.
     *
     * @param array $enable
     * @param Xss   $xss
     *
     * @return void
     */
    private function cleanCookie(array $enable, Xss $xss): void
    {
        if ($enable['cookie']) {
            $this->kernel->setClosure('xss_cookie', function() use ($xss) {
                if (!empty($_COOKIE)) {
                    foreach (array_keys($_COOKIE) as $k) {
                        $_COOKIE[$k] = $xss->clean($_COOKIE[$k]);
                    }
                }
            });
        }
    }

    /**
     * Clean the specific protected varibles.
     *
     * @param array $protectedLis
     * @param Xss   $xss
     *
     * @return void
     */
    private function cleanProtectedList(array $protectedList, Xss $xss): void
    {
        if (!empty($protectedList)) {
            $this->kernel->setClosure('xss_protection', 
                function() use ($xss, $protectedList) {
                    foreach ($protectedList as $v) {
                        $k = $v['variable'] ?? 'undefined';
        
                        switch ($v['type']) {
                            case 'get':
                                if (!empty($_GET[$k])) {
                                    $_GET[$k] = $xss->clean($_GET[$k]);
                                }
                                break;
        
                            case 'post':
                                if (!empty($_POST[$k])) {
                                    $_POST[$k] = $xss->clean($_POST[$k]);
                                }
                                break;
        
                            case 'cookie':
                                if (!empty($_COOKIE[$k])) {
                                    $_COOKIE[$k] = $xss->clean($_COOKIE[$k]);
                                }
                                break;
                        }
                    }
                }
            );
        }
    }
}
