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
use function gethostbyaddr;

/**
 * Robot
 */
class Robot implements ComponentInterface, RobotInterface
{
    use \Shieldon\IpTrait;

    /**
     * Data pool for Whitelist.
     *
     * @var array
     */
    protected $allowedList = [];

    /**
     * Data pool for Blacklist.
     *
     * @var array
     */
    protected $deniedList = [];

    /**
     * Data pool for search engline.
     *
     * @var array
     */
    protected $searchbotList = [];

    /**
     * Robot's user-agent text.
     * 
     * @var string
     */
    private $userAgentString = '';

    /**
     * If strict mode is on, `ipResolvedHostname` is to have to be set, otherwise
     * - isAllowed will return false
     * - isDenied will return true
     * and ignore other checking rules.
     *
     * @var boolean
     */
    private $strictMode = true;

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
        $this->allowedList = [

            // RDNS for robot's IP address.
            'rdns' => [
                '.googlebot.com',
                '.google.com',
                '.live.com',  // search.live.com
                '.msn.com',   // msnbot.msn.com, search.msn.com
                '.ask.com',
                '.bing.com',
                '.inktomisearch.com',
                '.yahoo.com',
                '.yahoo.net', // crawl.yahoo.net
                '.yandex.com',
                '.yandex.ru',
                '.w3.org',
            ],

            // Popular search engines' user-agent string.
            'agent' => [
                'google',
                'bing',
                'live',
                'msn',
                'ask',
                'inktomisearch',
                'yahoo',
                'yandex',
                'w3.org'
            ],

        ];

        // Those robots are considered as bad behavior. Therefore we list them here.
        $this->deniedList = [

            // RDNS for robot's IP address.
            'rdns' => [
                '.webcrawler.link',
            ],

            // Bad-behavir user-agent string.
            'agent' => [
                'archive.org', // Wayback machine.
                'ahrefs.com',
                'tweetmeme.com',
                'findlinks',
                'grapeshot.co.uk',
            ]

        ];

        $this->searchEnglineList = [

            // RDNS for robot's IP address.
            'rdns' => [
                '.googlebot.com',
                '.google.com',
                '.live.com',   // search.live.com
                '.msn.com',    // msnbot.msn.com, search.msn.com
                '.ask.com',
                '.bing.com',
                '.inktomisearch.com',
                '.yahoo.com',
                '.yahoo.net',  // crawl.yahoo.net
                '.yandex.com',
                '.yandex.ru',
            ],

            // The search engine robots' user-agent string.
            'agent' => [
                'google',
                'bing',
                'live',
                'msn',
                'ask',
                'inktomisearch',
                'yahoo',
                'yandex',
            ],
        ];

        if (! empty($_SERVER['HTTP_USER_AGENT'])) {
            $this->userAgentString = $_SERVER['HTTP_USER_AGENT'];
        }

        // Don't care about the proxy. Using proxy is considered bad behavior here.
        $this->ip = $this->setIp();
    }

    /**
     * Set a RDNS record for the check.
     *
     * @param bool $bool Set true to enble strict mode, false to disable it overwise.
     * 
     * @return void
     */
    public function setStrict($bool): void
    {
        $this->strictMode = $bool;
    }

    /**
     * {@inheritDoc}
     */
    public function isDenied(): bool
    {
        if (preg_match('/(' . implode('|', $this->deniedList['agent']). ')/i', $this->userAgentString)) {
            return true;
        }

        if (preg_match('/(' . implode('|', $this->deniedList['rdns']). ')/i', $this->ipResolvedHostname)) {
            return true;
        }

        if ($this->strictMode) {

            // If strict mode is on, this value can not be empty.
            if (empty($this->ipResolvedHostname)) {
                return true;
            }
        }

        return false;
    }

    /**
     * {@inheritDoc}
     */
    public function isAllowed(): bool
    {
        if (! preg_match('/(' . implode('|', $this->allowedList['agent']) . ')/i', $this->userAgentString)) {
            return false;
        }

        if ($this->strictMode) {

            // If strict mode is on, we will check the RDNS record to see if it is in the whitelist.
            if (preg_match('/(' . implode('|', $this->allowedList['rdns']) . ')/i', $this->ipResolvedHostname)) {

                // confirm hostname's IP again
                $ip = gethostbyname($this->ipResolvedHostname);
    
                // If the IP is the same as hostname's resolved IP.
                if ($ip === $this->ip) {
                    return true;
                } 
            } 
        } else {
            return true;
        }

        return false;
    }

    /**
     * {@inheritDoc}
     */
    public function isRobot(): bool
    {
        $isRobot = false;

        $robotHosts = array_merge(
            $this->allowedList['rdns'],
            $this->deniedList['rdns']
        );

        $robotAgents = array_merge(
            $this->allowedList['agent'],
            $this->deniedList['agent']
        );

        if (preg_match('/(' . implode('|', $robotHosts). ')/i', $this->ipResolvedHostname)) {
            $isRobot = true;
        }

        // If someone fakes his user-agent string, we consider it is a robot as well.
        if (preg_match('/(' . implode('|', $robotAgents). ')/i', $this->userAgentString)) {
            $isRobot = true;
        }

        return $isRobot;
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
     * {@inheritDoc}
     */
    public function isSearchbot(): bool
    {
        $isSearchbot = false;

        if ($this->strictMode) {

            // If strict mode is on, we will check the RDNS record to see if it is in the whitelist.
            if (preg_match('/(' . implode('|', $this->allowedList['rdns']) . ')/i', $this->ipResolvedHostname)) {

                // confirm hostname's IP again
                $ip = gethostbyname($this->ipResolvedHostname);
    
                // If the IP is the same as hostname's resolved IP.
                if ($ip === $this->ip) {
                    $isSearchbot = true;
                } 
            } 
        }

        return $isSearchbot;
    }

    /**
     * {@inheritDoc}
     */
    public function setUserAgent($string): void
    {
        $this->userAgentString = $string;
    }

    /**
     * {@inheritDoc}
     */
    public function getUserAgent(): string
    {
        return $this->userAgentString;
    }

    /**
     * {@inheritDoc}
     */
    public function setRdnsList(array $data, string $type = ''): void
    {
        switch($type) {
            case 'allow'    : $this->allowedList['rdns']       = $data; break;
            case 'deny'     : $this->deniedList['rdns']        = $data; break;
            case 'searchbot': $this->searchEnglineList['rdns'] = $data; break;
        }
    }

    /**
     * {@inheritDoc}
     */
    public function geRdnsList(array $data, string $type = ''): array
    {
        switch($type) {
            case 'allow'    : return $this->allowedList['rdns'];       break;
            case 'deny'     : return $this->deniedList['rdns'];        break;
            case 'searchbot': return $this->searchEnglineList['rdns']; break;
        }
    }

    /**
     * {@inheritDoc}
     */
    public function setAgentList(array $data, string $type = ''): void
    {
        switch($type) {
            case 'allow'    : $this->allowedList['agent']       = $data; break;
            case 'deny'     : $this->deniedList['agent']        = $data; break;
            case 'searchbot': $this->searchEnglineList['agent'] = $data; break;
        }
    }

    /**
     * {@inheritDoc}
     */
    public function geAgentList(array $data, string $type = ''): array
    {
        switch($type) {
            case 'allow'    : return $this->allowedList['agent'];       break;
            case 'deny'     : return $this->deniedList['agent'];        break;
            case 'searchbot': return $this->searchEnglineList['agent']; break;
        } 
    }
}