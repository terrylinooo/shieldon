<?php declare(strict_types=1);
/*
 * This file is part of the Shieldon package.
 *
 * (c) Terry L. <contact@terryl.in>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Shieldon\Component;

use function preg_match;
use function implode;
use function gethostbyname;

/**
 * TrustedBot
 */
class TrustedBot extends ComponentProvider
{
    use \Shieldon\IpTrait;

    /**
     * Robot's user-agent text.
     * 
     * @var string
     */
    private $userAgentString = '';

    /**
     * Trusted bot list.
     *
     * @var array
     */
    private $trustedBotList = [];

    /**
     * Constructor.
     * 
     * It will implement default configuration settings here.
     * 
     * @return void
     */
    public function __construct()
    {
        // They are robots we welcome in this whitelist.
        $this->trustedBotList = [

            // User-agent   => RDNS
            'google'        => '.googlebot.com',
            'google'        => '.google.com',
            'live'          => '.live.com', 
            'msn'           => '.msn.com',
            'ask'           => '.ask.com',
            'bing'          => '.bing.com',
            'inktomisearch' => '.inktomisearch.com',
            'yahoo'         => '.yahoo.com',
            'yahoo'         => '.yahoo.net',
            'yandex'        => '.yandex.com',
            'yandex'        => '.yandex.ru',
            'w3.org'        => '.w3.org',
        ];

        if (! empty($_SERVER['HTTP_USER_AGENT'])) {
            $this->userAgentString = $_SERVER['HTTP_USER_AGENT'];
        }

        // Don't care about the proxy. Using proxy is considered bad behavior here.
        $this->ip = $this->setIp();
    }

    /**
     * {@inheritDoc}
     */
    public function isAllowed(): bool
    {
        if (! empty($this->trustedBotList)) {
            if (! preg_match('/(' . implode('|', array_keys($this->trustedBotList)) . ')/i', $this->userAgentString)) {
                return false;
            }
    
            // If strict mode is on, we will check the RDNS record to see if it is in the whitelist.
            if (preg_match('/(' . implode('|', $this->trustedBotList) . ')/i', $this->ipResolvedHostname)) {
                $ip = gethostbyname($this->ipResolvedHostname);
    
                // If the IP is different as hostname's resolved IP. It is maybe a fake bot.
                if ($ip !== $this->ip) {
                    return false;
                }
    
                return true;
            }
        }

        return false;
    }

    /**
     * Add a trusted bot.
     *
     * @param string $userAgentString
     * @param string $rdns
     *
     * @return void
     */
    public function addItem(string $userAgentString, string $rdns): void
    {
        $this->trustedBotList[$userAgentString] = '.' . trim($rdns, '.');
    }

    /**
     * {@inheritDoc}
     */
    public function isGoogle(): bool
    {
        if (preg_match('/(google.com|googlebot.com)/i', $this->ipResolvedHostname)) {
            return true;
        }

        return false;
    }

    /**
     * {@inheritDoc}
     */
    public function isYahoo(): bool
    {
        if (preg_match('/(yahoo.com|yahoo.net)/i', $this->ipResolvedHostname)) {
            return true;
        }

        return false;
    }

    /**
     * {@inheritDoc}
     */
    public function isBing(): bool
    {
        if (preg_match('/(msn.com|bing.com|live.com)/i', $this->ipResolvedHostname)) {
            return true;
        }

        return false;
    }

    /**
     * Not used in TrustedBots component.
     * 
     * @return bool always false.
     */
    public function isDenied(): bool
    {
        return false;
    }
}