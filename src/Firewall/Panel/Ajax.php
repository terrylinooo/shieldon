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
use Shieldon\Firewall\HttpFactory;
use function Shieldon\Firewall\__;
use function Shieldon\Firewall\get_request;
use function Shieldon\Firewall\get_response;
use function Shieldon\Firewall\get_session_instance;
use function Shieldon\Firewall\set_request;
use function array_map;
use function explode;
use function file_exists;
use function gethostname;
use function implode;
use function json_encode;

/**
 * User
 */
class Ajax extends BaseController
{
    /**
     *   Public methods       | Desctiotion
     *  ----------------------|---------------------------------------------
     *   __call               | The magic method.
     *   changeLocale         | Change the user's language of the UI.
     *   tryMessenger         | Test messenger modules.
     *  ----------------------|---------------------------------------------
     */

    /**
     * Constructor.
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Fallback for undefined methods.
     *
     * @param string $function The method name.
     * @param array  $args     The arguments.
     *
     * @return bool
     */
    public function __call($function, $args): bool
    {
        $className = 'Shieldon\Firewall\Panel\Sandbox\\' . $function;

        if (file_exists(__DIR__ . '/Sandbox/' . $function . '.php')) {
            $sandbox = new $className();
            return $sandbox($args);
        }
        return false;
    }

    /**
     * Change the user's language of the UI.
     *
     * @return ResponseInterface
     */
    public function changeLocale(): ResponseInterface
    {
        $langCode = get_request()->getQueryParams()['langCode'] ?? 'en';
        get_session_instance()->set('shieldon_panel_lang', $langCode);
        get_session_instance()->save();
        $data = [];

        $data['status'] = 'success';
        $data['lang_code'] = $langCode;
        $data['session_lang_code'] = $langCode;
 
        $output = json_encode($data);

        return $this->respondJson($output);
    }

    /**
     * Test messenger modules.
     *
     * @return ResponseInterface
     */
    public function tryMessenger(): ResponseInterface
    {
        $request = get_request();
        $message = [];

        $getParams = $request->getQueryParams();
        $serverParams = $request->getServerParams();

        $serverName = $serverParams['SERVER_NAME'] ?? gethostname();
        $moduleName = $getParams['module'] ?? '';

        $data = [];
        $data['status'] = 'undefined';
        $data['result']['moduleName'] = $moduleName;

        $message['title'] = __('panel', 'test_msg_title', 'Testing Message from Host: ') . $serverName;
        $message['body'] = __(
            'panel',
            'test_msg_body',
            'Messenger module "{0}" has been tested and confirmed successfully.',
            [$moduleName]
        );
    
        // @codeCoverageIgnoreStart

        // Name the testing method.
        $method = explode('-', $moduleName);
        $method = implode(
            '',
            array_map(
                function ($word) {
                    return ucwords($word);
                },
                $method
            )
        );

        $postParams = $request->getParsedBody();
        $postKey = 'messengers__' . $moduleName . '__confirm_test';

        // Call testing method if exists.
        $status = $this->{$method}($getParams, $message);

        if ($status) {
            $data['status'] = 'success';
            $postParams[$postKey] = 'on';
        } else {
            $data['status'] = 'error';
            $postParams[$postKey] = 'off';
        }

        set_request($request->withParsedBody($postParams));

        $this->saveConfig();

        // @codeCoverageIgnoreEnd

        $data['result']['postKey'] = $postKey;

        $output = json_encode($data);

        return $this->respondJson($output);
    }

    /**
     * Respond the JSON format result.
     *
     * @param string $output The string you want to output to the browser.
     *
     * @return ResponseInterface
     */
    private function respondJson($output): ResponseInterface
    {
        $response = get_response();

        $stream = HttpFactory::createStream();
        $stream->write($output);
        $stream->rewind();

        $response = $response->withHeader('Content-Type', 'application/json');
        $response = $response->withAddedHeader('Content-Type', 'charset=utf-8');
        $response = $response->withBody($stream);

        return $response;
    }
}
