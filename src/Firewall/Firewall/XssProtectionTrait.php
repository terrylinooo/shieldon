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

namespace Shieldon\Firewall\Firewall;

use Shieldon\Security\Xss;
use function array_keys;
use function array_search;

/*
 * Xss Protection Trait is loaded in Firewall instance only.
 */
trait XssProtectionTrait
{
    /**
     * Get options from the configuration file.
     * This method is same as `$this->getConfig()` but returning value from array directly.
     *
     * @param string $option  The option of the section in the the configuration.
     * @param string $section The section in the configuration.
     *
     * @return mixed
     */
    abstract protected function getOption(string $option, string $section = '');

    /**
     * Refresh / refetch the server request if needed.
     *
     * @return void
     */
    abstract protected function refreshRequest(): void;

    /**
     * Set up the XSS protection.
     *
     * @return void
     */
    protected function setupXssProtection(): void
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

        $this->refreshRequest();
    }

    /**
     * Clean the $_POST superglobal.
     *
     * @param array $enable The option to enable filtering $_POST.
     * @param Xss   $xss    The Xss instance.
     *
     * @return void
     */
    private function cleanPost(array $enable, Xss $xss): void
    {
        if ($enable['post']) {
            $this->kernel->setClosure(
                'xss_post',
                function () use ($xss) {
                    if (!empty($_POST)) {
                        foreach (array_keys($_POST) as $k) {
                            $_POST[$k] = $xss->clean($_POST[$k]);
                        }
                    }
                }
            );
        }
    }

    /**
     * Clean the $_GET superglobal.
     *
     * @param array $enable The option to enable filtering $_GET.
     * @param Xss   $xss    The Xss instance.
     *
     * @return void
     */
    private function cleanGet(array $enable, Xss $xss): void
    {
        if ($enable['get']) {
            $this->kernel->setClosure(
                'xss_get',
                function () use ($xss) {
                    if (!empty($_GET)) {
                        foreach (array_keys($_GET) as $k) {
                            $_GET[$k] = $xss->clean($_GET[$k]);
                        }
                    }
                }
            );
        }
    }

    /**
     * Clean the $_COOKIE superglobal.
     *
     * @param array $enable The option to enable filtering $_COOKIE.
     * @param Xss   $xss    The Xss instance.
     *
     * @return void
     */
    private function cleanCookie(array $enable, Xss $xss): void
    {
        if ($enable['cookie']) {
            $this->kernel->setClosure(
                'xss_cookie',
                function () use ($xss) {
                    if (!empty($_COOKIE)) {
                        foreach (array_keys($_COOKIE) as $k) {
                            $_COOKIE[$k] = $xss->clean($_COOKIE[$k]);
                        }
                    }
                }
            );
        }
    }

    /**
     * Clean the specific protected varibles.
     *
     * @param array $protectedList The specific variables to be filtered.
     * @param Xss   $xss           The Xss instance.
     *
     * @return void
     */
    private function cleanProtectedList(array $protectedList, Xss $xss): void
    {
        if (!empty($protectedList)) {
            $this->kernel->setClosure(
                'xss_protection',
                function () use ($xss, $protectedList) {
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
