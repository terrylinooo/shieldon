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

namespace Shieldon\Firewall\Panel;

use Psr\Http\Message\ResponseInterface;
use Shieldon\Firewall\Panel\BaseController;
use function Shieldon\Firewall\__;
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
     *   Public methods       | Desctiotion
     *  ----------------------|---------------------------------------------
     *   authentication       | The page for managing page authentication.
     *   actionLog            | The page for managing XSS protection.
     *  ----------------------|---------------------------------------------
     */

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
     * @return ResponseInterface
     */
    public function authentication(): ResponseInterface
    {
        $postParams = get_request()->getParsedBody();

        if ($this->checkPostParamsExist('url', 'user', 'pass', 'action')) {
            $url = $postParams['url'];
            $user = $postParams['user'];
            $pass = $postParams['pass'];
            $action = $postParams['action'];
            $order = (int) $postParams['order'];

            $authenticatedList = (array) $this->getConfig('www_authenticate');

            if ('add' === $action) {
                array_push(
                    $authenticatedList,
                    [
                        'url' => $url,
                        'user' => $user,
                        'pass' => password_hash($pass, PASSWORD_BCRYPT),
                    ]
                );
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

        $data = [];

        $data['authentication_list'] = $this->getConfig('www_authenticate');

        $data['title'] = __('panel', 'title_web_authentication', 'Web Page Authentication');

        return $this->renderPage('panel/authentication', $data);
    }

    /**
     * XSS Protection.
     *
     * @return ResponseInterface
     */
    public function xssProtection(): ResponseInterface
    {
        $postParams = get_request()->getParsedBody();

        if ($this->checkPostParamsExist('xss_form_1')) {
            unset_superglobal('xss_form_1', 'post');
            unset_superglobal('order', 'post');
            unset_superglobal('submit', 'post');

            $this->saveConfig();
        } elseif ($this->checkPostParamsExist('xss_form_2', 'type', 'action')) {
            $type     = $postParams['type'];
            $variable = $postParams['variable'];
            $action   = $postParams['action'];

            // The index number in the $xssProtectedList, see below.
            $order = (int) $postParams['order'];

            // Check variable name. Should be mixed with a-zA-Z and underscore.
            if (!ctype_alnum(str_replace('_', '', $variable))) {
                // @codeCoverageIgnoreStart
                // Ignore the `add` process.
                $action = 'undefined';
                // @codeCoverageIgnoreEnd
            }

            $xssProtectedList = (array) $this->getConfig('xss_protected_list');

            if ('add' === $action) {
                array_push(
                    $xssProtectedList,
                    [
                        'type'     => $type,
                        'variable' => $variable,
                    ]
                );
            } elseif ('remove' === $action) {
                unset($xssProtectedList[$order]);
                $xssProtectedList = array_values($xssProtectedList);
            }

            $this->setConfig('xss_protected_list', $xssProtectedList);

            unset_superglobal('xss_form_2', 'post');
            unset_superglobal('type', 'post');
            unset_superglobal('variable', 'post');
            unset_superglobal('action', 'post');
            unset_superglobal('order', 'post');
            unset_superglobal('submit', 'post');

            $this->saveConfig();
        }

        $data = [];

        $data['xss_protected_list'] = $this->getConfig('xss_protected_list');

        $data['title'] = __('panel', 'title_xss_protection', 'XSS Protection');

        return $this->renderPage('panel/xss_protection', $data);
    }
}
