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
     * Change the user's language of the UI.
     *
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function changeLocale(): ResponseInterface
    {
        $langCode = get_request()->getQueryParams()['langCode'] ?? 'en';
        get_session()->set('shieldon_panel_lang', $langCode);

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
        $getParams = get_request()->getQueryParams();
        $serverParams = get_request()->getServerParams();
        $serverName = $serverParams['SERVER_NAME'] ?? gethostname();
        $moduleName = $getParams['module'] ?? '';

        $data = [];
        $data['status'] = 'error';
        $data['result']['moduleName'] = $moduleName;

        $testMsgTitle = __('panel', 'test_msg_title', 'Testing Message from Host: ') . $serverName;
        $testMsgBody = __('panel', 'test_msg_body', 'Messenger module "{0}" has been tested and confirmed successfully.', [$moduleName]);
    
        // @codeCoverageIgnoreStart

        switch($moduleName) {

            case 'telegram':
                $apiKey = $getParams['apiKey'] ?? '';
                $channel = $getParams['channel'] ?? '';
                if (!empty($apiKey) && !empty($channel)) {
                    $messenger = new Messenger\Telegram($apiKey, $channel);
                    if ($messenger->send($testMsgBody)) {
                        $data['status'] = 'success';
                    }
                }
                break;

            case 'line-notify':
                $accessToken = $getParams['accessToken'] ?? '';
                if (!empty($accessToken)) {
                    $messenger = new Messenger\LineNotify($accessToken);
                    if ($messenger->send($testMsgBody)) {
                        $data['status'] = 'success';
                    }
                }
                break;

            case 'slack':
                $botToken = $getParams['botToken'] ?? '';
                $channel = $getParams['channel'] ?? '';
                if (!empty($botToken) && !empty($channel)) {
                    $messenger = new Messenger\Slack($botToken, $channel);
                    if ($messenger->send($testMsgBody)) {
                        $data['status'] = 'success';
                    }
                }
                break;

            case 'slack-webhook':
                $webhookUrl = $getParams['webhookUrl'] ?? '';
                if (!empty($webhookUrl)) {
                    $messenger = new Messenger\SlackWebhook($webhookUrl);
                    if ($messenger->send($testMsgBody)) {
                        $data['status'] = 'success';
                    }
                }
                break;

            case 'rocket-chat':
                $serverUrl = $getParams['serverUrl'] ?? '';
                $userId = $getParams['userId'] ?? '';
                $accessToken = $getParams['accessToken'] ?? '';
                $channel = $getParams['channel'] ?? '';

                if (
                    !empty($serverUrl) &&
                    !empty($userId) &&
                    !empty($accessToken) &&
                    !empty($channel)
                ) {
                    $messenger = new Messenger\RocketChat($accessToken, $userId, $serverUrl, $channel);
                    if ($messenger->send($testMsgBody)) {
                        $data['status'] = 'success';
                    }
                }
                break;

            case 'smtp':
                $type = $getParams['type'] ?? '';
                $host = $getParams['host'] ?? '';
                $user = $getParams['user'] ?? '';
                $pass = $getParams['pass'] ?? '';
                $port = $getParams['port'] ?? '';

                $sender = $getParams['sender'] ?? '';
                $recipients = $getParams['recipients'] ?? '';

                if (
                    (
                        !filter_var($host, FILTER_VALIDATE_IP) && 
                        !filter_var($host, FILTER_VALIDATE_DOMAIN)
                    ) || 
                    !is_numeric($port) || 
                    empty($user) || 
                    empty($pass) 
                ) {
                    $data['result']['message'] = 'Invalid fields.';
                    $output = json_encode($data);
                    return $this->respondJson($output);
                }

                if ('ssl' === $type || 'tls' === $type) {
                    $host = $type . '://' . $host;
                }

                if (!empty($sender) && $recipients) {
                    $recipients = str_replace("\r", '|', $recipients);
                    $recipients = str_replace("\n", '|', $recipients);
                    $recipients = explode('|', $recipients);

                    $messenger = new Messenger\Smtp($user, $pass, $host, (int) $port);

                    foreach($recipients as $recipient) {
                        if (filter_var($recipient, FILTER_VALIDATE_EMAIL)) {
                            $messenger->addRecipient($recipient);
                        }
                    }

                    if (filter_var($sender, FILTER_VALIDATE_EMAIL)) {
                        $messenger->addSender($sender);
                    }

                    $messenger->setSubject($testMsgTitle);

                    if ($messenger->send($testMsgBody)) {
                        $data['status'] = 'success';
                    }
                }
                break;

            case 'native-php-mail':
                $sender = $getParams['sender'] ?? '';
                $recipients = $getParams['recipients'] ?? '';

                if (!empty($sender) && !empty($recipients)) {
                    $recipients = str_replace("\r", '|', $recipients);
                    $recipients = str_replace("\n", '|', $recipients);
                    $recipients = explode('|', $recipients);

                    $messenger = new Messenger\Mail();

                    foreach($recipients as $recipient) {
                        if (filter_var($recipient, FILTER_VALIDATE_EMAIL)) {
                            $messenger->addRecipient($recipient);
                        }
                    }

                    if (filter_var($sender, FILTER_VALIDATE_EMAIL)) {
                        $messenger->addSender($sender);
                    }

                    $messenger->setSubject($testMsgTitle);

                    if ($messenger->send($testMsgBody)) {
                        $data['status'] = 'success';
                    }
                }
                break;

            case 'sendgrid':
                $apiKey = $getParams['apiKey'] ?? '';
                $sender = $getParams['sender'] ?? '';
                $recipients = $getParams['recipients'] ?? '';

                if (!empty($sender) && !empty($recipients) && !empty($apiKey)) {
                    $recipients = str_replace("\r", '|', $recipients);
                    $recipients = str_replace("\n", '|', $recipients);
                    $recipients = explode('|', $recipients);

                    $messenger = new Messenger\Sendgrid($apiKey);

                    foreach($recipients as $recipient) {
                        if (filter_var($recipient, FILTER_VALIDATE_EMAIL)) {
                            $messenger->addRecipient($recipient);
                        }
                    }

                    if (filter_var($sender, FILTER_VALIDATE_EMAIL)) {
                        $messenger->addSender($sender);
                    }

                    $messenger->setSubject($testMsgTitle);

                    if ($messenger->send($testMsgBody)) {
                        $data['status'] = 'success';
                    }
                }
                break;

            case 'mailgun':
                $apiKey = $getParams['apiKey'] ?? '';
                $domain = $getParams['domain'] ?? '';
                $sender = $getParams['sender'] ?? '';
                $recipients = $getParams['recipients'] ?? '';

                if (!empty($sender) && !empty($recipients) && !empty($apiKey) && !empty($domain)) {
                    $recipients = str_replace("\r", '|', $recipients);
                    $recipients = str_replace("\n", '|', $recipients);
                    $recipients = explode('|', $recipients);

                    $messenger = new Messenger\Mailgun($apiKey, $domain);

                    foreach($recipients as $recipient) {
                        if (filter_var($recipient, FILTER_VALIDATE_EMAIL)) {
                            $messenger->addRecipient($recipient);
                        }
                    }

                    if (filter_var($sender, FILTER_VALIDATE_EMAIL)) {
                        $messenger->addSender($sender);
                    }

                    $messenger->setSubject($testMsgTitle);

                    if ($messenger->send($testMsgBody)) {
                        $data['status'] = 'success';
                    }
                }
                break;

            default:
                $data['status'] = 'undefined';
        }

        $moduleName = str_replace('-', '_', $moduleName);
        $postKey = 'messengers__' . $moduleName . '__confirm_test';

        if ('success' === $data['status']) {
            $postParams[$postKey] = 'on';
            $this->saveConfig();
        } elseif ('error' === $data['status']) {
            $postParams[$postKey] = 'off';
            $this->saveConfig();
        }

        // @codeCoverageIgnoreStart

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

