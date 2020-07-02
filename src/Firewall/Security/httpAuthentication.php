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

namespace Shieldon\Firewall\Security;

use function Shieldon\Firewall\get_request;
use function header;
use function password_verify;
use function strpos;

 /**
  * WWW-Authenticate
  */
class httpAuthentication
{
    /**
     * The URL list that you want to protect.
     *
     * @var array
     */
    protected $protectedUrlList = [
        [
            // Begin-with URL 
            'url' => '/wp-amdin', 

            // Username
            'user' => 'wp_shieldon_user',

            // Password encrypted by `password_hash()` function.
            // In this case, the uncrypted string is `wp_shieldon_pass`.
            'pass' => '$2y$10$eA/S6rH3JDkYV9nrrUvuMOTh8Q/ts33DdCerbNAUpdwtSl3Xq9cQq'  
        ]
    ];

    /**
     * The text displays on prompted window.
     * Most modern browsers won't show this anymore. You can ignore that!?
     *
     * @var string
     */
    protected $realm = 'Welcome to area 51.';

    /**
     * Constructor.
     * 
     * @param array  $protectedUrlList
     *
     * @return void
     */
    public function __construct(array $protectedUrlList = [])
    {
        $this->set($protectedUrlList);
    }

    /**
     * Set up the URL list that you want to protect.
     * 
     * @param $protectedUrlList
     *
     * @return void
     */
    public function set(array $protectedUrlList = []): void
    {
        if (!empty($protectedUrlList)) {
            $this->protectedUrlList = $protectedUrlList;
        }
    }

    // @codeCoverageIgnoreStart

    /**
     * Identify the username and password for proected URL.
     *
     * @return void
     *
     */
    public function check(): void
    {
        $currentUrl = get_request()->getUri()->getPath();
        $serverParams = get_request()->getServerParams();

        foreach ($this->protectedUrlList as $urlInfo) {

            // If we have set the protection for current URL.
            if (0 === strpos($currentUrl, $urlInfo['url'])) {

                // Prompt a window to ask for username and password.
                if (!isset($serverParams['PHP_AUTH_USER']) || !isset($serverParams['PHP_AUTH_PW'])) {
                    header('WWW-Authenticate: Basic realm="' . $this->realm . '"');
                    header('HTTP/1.0 401 Unauthorized');
                    die('Permission required.');
                }
                
                // Identify the username and password for current URL.
                if (
                    $urlInfo['user'] === $serverParams['PHP_AUTH_USER'] &&
                    password_verify($serverParams['PHP_AUTH_PW'], $urlInfo['pass'])
                ) {
                    // nothing to do right now.
                } else {
                    header('HTTP/1.0 401 Unauthorized');
                    unset($_SERVER['PHP_AUTH_USER'], $_SERVER['PHP_AUTH_PW']);
                    die('Permission required.');
                }
            }
        }
    }

    // @codeCoverageIgnoreEnd
}
