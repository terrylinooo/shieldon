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

namespace Shieldon\Firewall\Panel;

use Psr\Http\Message\ResponseInterface;
use Shieldon\Firewall\Panel\BaseController;
use function Shieldon\Firewall\get_request;
use function Shieldon\Firewall\unset_superglobal;

use function array_push;
use function array_values;
use function ctype_alnum;
use function str_replace;

/**
 * Security
 */
class Security extends BaseController
{
    /**
     * Constructor
     */
    public function __construct() 
    {
        parent::__construct();
    }

    /**
     * WWW-Authenticate.
     *
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function authentication(): ResponseInterface
    {
        $postParams = get_request()->getParsedBody();

        if (
            isset($postParams['url']) && 
            isset($postParams['user']) && 
            isset($postParams['pass'])
        ) {

            $url = $postParams['url'] ?? '';
            $user = $postParams['user'] ?? '';
            $pass = $postParams['pass'] ?? '';
            $action = $postParams['action'] ?? '';
            $order = (int) $postParams['order'];

            $authenticatedList = $this->getConfig('www_authenticate');

            if ('add' === $action) {
                array_push($authenticatedList, [
                    'url' => $url,
                    'user' => $user,
                    'pass' => password_hash($pass, PASSWORD_BCRYPT),
                ]);

            } elseif ('remove' === $action) {
                unset($authenticatedList[$order]);
                $authenticatedList = array_values($authenticatedList);
            }

            $this->setConfig('www_authenticate', $authenticatedList);

            unset_superglobal('url', 'post');
            unset_superglobal('user', 'post');
            unset_superglobal('pass', 'post');
            unset_superglobal('action', 'post');
            unset_superglobal('order', 'post');

            $this->saveConfig();
        }

        $data['authentication_list'] = $this->getConfig('www_authenticate');

        $data['title'] = __('panel', 'title_web_authentication', 'Web Page Authentication');

        return $this->renderPage('panel/authentication', $data);
    }

    /**
     * XSS Protection.
     *
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function xssProtection(): ResponseInterface
    {
        $postParams = get_request()->getParsedBody();

        if (isset($postParams['xss'])) {
            unset_superglobal('xss', 'post');

            $type = $postParams['type'] ?? '';
            $variable = $postParams['variable'] ?? '';
            $action = $postParams['action'] ?? '';
            $order = (int) $postParams['order'];

            // Check variable name. Should be mixed with a-zA-Z and underscore.
            if (!ctype_alnum(str_replace('_', '', $variable))) {

                // Ignore the `add` process.
                $action = 'undefined';
            }

            $xssProtectedList = $this->getConfig('xss_protected_list');

            if (empty($xssProtectedList)) {
                $xssProtectedList = [];
            }

            if ('add' === $action) {

                switch ($type) {
                    case 'post':
                    case 'get':
                    case 'cookie':
                        array_push($xssProtectedList, ['type' => $type, 'variable' => $variable]);
                        break;

                    default:
                    // endswitch.
                }

            } elseif ('remove' === $xssProtectedList) {
                unset($xssProtectedList[$order]);
                $xssProtectedList = array_values($xssProtectedList);
            }

            $this->setConfig('xss_protected_list', $xssProtectedList);

            unset_superglobal('type', 'post');
            unset_superglobal('variable', 'post');
            unset_superglobal('action', 'post');
            unset_superglobal('order', 'post');

            $this->saveConfig();
        }

        $data['xss_protected_list'] = $this->getConfig('xss_protected_list');

        $data['title'] = __('panel', 'title_xss_protection', 'XSS Protection');

        return $this->renderPage('panel/xss_protection', $data);
    }
}

