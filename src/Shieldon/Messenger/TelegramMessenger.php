<?php declare(strict_types=1);
/*
 * This file is part of the Shieldon package.
 *
 * (c) Terry L. <contact@terryl.in>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Shieldon\Messenger;

use RuntimeException;

use function curl_init;
use function curl_setopt;
use function json_decode;

/**
 * TelegramMessenger
 * 
 * @since 3.3.0
 */
class TelegramMessenger implements MessengerInterface
{
    /**
     * API key.
     *
     * Add `BotFather` to start a conversation.
     * Type command `/newbot` to obtain your api key.
     *
     * @var string
     */
    private $apiKey;

    /**
     * Telegram channel name.
     *
     * For example, @your_channel_name, and remember, make your channel type public.
     * If you want to send message to your private channel, googling will find solutions.
     *
     * @var string
     */
    private $channel;

    /**
     * The connection timeout when calling Telegram API.
     *
     * @var integer
     */
    private $timeout = 5;

    /**
     * @param string $apiKey  Telegram bot access token provided by BotFather
     * @param string $channel Telegram channel name
     */
    public function __construct(string $apiKey, string $channel, int $timeout = 5)
    {
        $this->apiKey = $apiKey;
        $this->channel = $channel;
        $this->timeout = $timeout;
    }

    /**
     * @inheritDoc
     */
    public function send(string $message, array $logData = []): void
    {
        if (! empty($logData)) {
            $message .= "\n";

            foreach ($logData as $key => $value) {
                $message .= $key . ': ' . $value . "\n";
            }
        }

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $this->getApiURL());
        curl_setopt($ch, CURLOPT_TIMEOUT, $this->timeout);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query([
            'text' => $message,
            'chat_id' => $this->channel,
        ]));
    
        $result = curl_exec($ch);

        if (! curl_errno($ch)) {
            $result = json_decode($result, true);

            if (false === $result['ok']) {
                throw new RuntimeException('An error occurred when accessing Telegram API. (' . $result['description'] . ')');
            }
        }
    }

    /**
     * Telegram API URL.
     *
     * @return string
     */
    private function getApiURL(): string
    {
        return 'https://api.telegram.org/bot' . $this->apiKey . '/SendMessage';
    }
}