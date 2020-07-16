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
use Shieldon\Messenger as Messenger;
use function Shieldon\Firewall\__;
use function Shieldon\Firewall\get_request;
use function Shieldon\Firewall\get_response;
use function Shieldon\Firewall\get_session;
use function Shieldon\Firewall\set_request;
use function explode;
use function filter_var;
use function gethostname;
use function is_numeric;
use function json_encode;
use function str_replace;

/**
 * User
 */
class Ajax extends BaseController
{
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
    public function  __call($function , $args)
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
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function changeLocale(): ResponseInterface
    {
        $langCode = get_request()->getQueryParams()['langCode'] ?? 'en';
        get_session()->set('shieldon_panel_lang', $langCode);
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
     * @return \Psr\Http\Message\ResponseInterface
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
        $message['body'] = __('panel', 'test_msg_body', 'Messenger module "{0}" has been tested and confirmed successfully.', [$moduleName]);
    
        // @codeCoverageIgnoreStart

        // Name the testing method.
        $method = explode('-', $moduleName);
        $method = implode('', array_map(function($word) {
            return ucwords($word); 
        }, $method));

        // Call testing method if exists.
        if ($this->{$method}($getParams, $message)) {
            $data['status'] = 'success';
        }

        $postParams = $request->getParsedBody();
        $postKey = 'messengers__' . $moduleName . '__confirm_test';

        if ('success' === $data['status']) {
            $postParams[$postKey] = 'on';
            $this->saveConfig();

        } elseif ('error' === $data['status']) {
            $postParams[$postKey] = 'off';
            $this->saveConfig();
        }

        set_request($request->withParsedBody($postParams));

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
     * @return \Psr\Http\Message\ResponseInterface
     */
    private function respondJson($output): ResponseInterface
    {
        $response = get_response();

        $stream = $response->getBody();
        $stream->write($output);
        $stream->rewind();

        $response = $response->withHeader('Content-Type', 'application/json');
        $response = $response->withAddedHeader('Content-Type', 'charset=utf-8');
        $response = $response->withBody($stream);

        return $response;
    }
}

