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

namespace Shieldon\Component;

use Shieldon\IpTrait;
use function Shieldon\Helper\get_request;

use function array_column;
use function array_merge;
use function array_unique;
use function gethostbyname;
use function implode;
use function preg_match;
use function strstr;

/**
 * TrustedBot
 */
class TrustedBot extends ComponentProvider
{
    use IpTrait;

    const STATUS_CODE = 85;

    /**
     * Robot's user-agent text.
     * 
     * @var string
     */
    private $userAgent = '';

    /**
     * Trusted bot list.
     *
     * @var array
     */
    private $trustedBotList = [

        // Search engline: Google.
        [
            'userAgent' => 'google',
            'rdns'      => '.googlebot.com',
        ],

        [
            'userAgent' => 'google',
            'rdns'      => '.google.com',
        ],

        // Search engline: Mircosoft.
        [
            'userAgent' => 'live',
            'rdns'      => '.live.com',
        ],

        [
            'userAgent' => 'msn',
            'rdns'      => '.msn.com',
        ],

        [
            'userAgent' => 'bing',
            'rdns'      => '.bing.com',
        ],

        // Search engline: Yahoo.
        [
            'userAgent' => 'inktomisearch',
            'rdns'      => '.inktomisearch.com',
        ],

        [
            'userAgent' => 'yahoo',
            'rdns'      => '.yahoo.com',
        ],

        [
            'userAgent' => 'yahoo',
            'rdns'      => '.yahoo.net',
        ],

        // Search engine: Yandex.
        [
            'userAgent' => 'yandex',
            'rdns'      => '.yandex.com',
        ],

        [
            'userAgent' => 'yandex',
            'rdns'      => '.yandex.net',
        ],

        [
            'userAgent' => 'yandex',
            'rdns'      => '.yandex.ru',
        ],

        // Facebook crawlers.
        [
            'userAgent' => 'facebook',
            'rdns'      => '.fbsv.net',
        ],

        // Twitter crawlers.
        [
            'userAgent' => 'Twitterbot',
            'rdns'      => '.twttr.com', // (not twitter.com)
        ],

        // W3C validation services.
        [
            'userAgent' => 'w3.org',
            'rdns'      => '.w3.org',
        ],

        // Ask.com crawlers.
        [
            'userAgent' => 'ask',
            'rdns'      => '.ask.com',
        ],
    ];
    
    /**
     * For testing purpse. (Unit test)
     *
     * @var bool
     */
    private $checkFakeRdns = true;

    /**
     * Is the current access a fake robot?
     *
     * @var bool
     */
    private $isFake = false;

    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->userAgent = get_request()->getHeaderLine('user-agent');
    }

    /**
     * {@inheritDoc}
     */
    public function isAllowed(): bool
    {
        if (!empty($this->trustedBotList)) {

            $userAgent = array_unique(array_column($this->trustedBotList, 'userAgent'));

            if (!preg_match('/(' . implode('|', $userAgent) . ')/i', $this->userAgent)) {
                // Okay, current request's user-agent string doesn't contain our truested bots' infroamtion.
                // Ignore it.
                return false;
            }

            $rdns = array_unique(array_column($this->trustedBotList, 'rdns'));

            $rdnsCheck = false;

            // We will check the RDNS record to see if it is in the whitelist.
            if (preg_match('/(' . implode('|', $rdns) . ')/i', $this->rdns)) {

                if ($this->checkFakeRdns) {

                    // To prevent "fake" RDNS such as "abc.google.com.fakedomain.com" pass thorugh our checking process.
                    // We need to check it one by one.
                    foreach ($rdns as $r) {

                        // For example:
                        // $x = strstr('abc.googlebot.com.fake', '.googlebot.com');
                        // $x will be `.googlebot.com.fake` so that we can identify this is a fake domain.
                        $x = strstr($this->rdns, $r);
            
                        // `.googlebot.com` === `.googlebot.com`
                        if ($x === $r) {
                            $rdnsCheck = true;
                        }
                    }
                }

                if ($rdnsCheck) {
                    $ip = gethostbyname($this->rdns);

                    if ($this->strictMode) {

                        // If the IP is different as hostname's resolved IP. It is maybe a fake bot.
                        if ($ip !== $this->ip) {
                            $this->isFake = true;
                            return false;
                        }
                    }
                }

                if ($this->checkFakeRdns) {

                    // We can identify that current access uses a fake RDNS record.
                    if (!$rdnsCheck) {
                        $this->isFake = true;
                        return false;
                    }
                }

                return true;
            }

            // Here, once a request uses a user-agent that contains search engine information, but it does't pass the RDNS check.
            // We can identify it is fake.
            $this->isFake = true;
        }

        return false;
    }

    /**
     * Add a trusted bot.
     *
     * @param string $userAgent
     *
     * @param string $rdns
     *
     * @return void
     */
    public function addItem(string $userAgent, string $rdns): void
    {
        $_rdns = '.' . trim($rdns, '.');

        $this->trustedBotList[] = [
            'userAgent' => $userAgent,
            'rdns'      => $_rdns,
        ];
    }

    /**
     * Add trusted bot list.
     *
     * @param array $list
     *
     * @return void
     */
    public function addList(array $list): void
    {
        if (!empty($list[0]['userAgent']) && !empty($list[0]['rdns']) && 2 === count($list[0])) {

            // Append the new list to the end.
            $this->trustedBotList = array_merge($this->trustedBotList, $list);
        }
    }

    /**
     * Get trusted list.
     *
     * @return array
     */
    public function getList(): array
    {
        return $this->trustedBotList;
    }

    /**
     * Set trusted list.
     *
     * @return void
     */
    public function setList(array $list): void
    {
        $this->trustedBotList = $list;
    }

    /**
     * Remove item.
     *
     * @param string $string
     *
     * @return void
     */
    public function removeItem(string $string): void
    {
        if (!empty($this->trustedBotList)) {
            foreach ($this->trustedBotList as $index => $list) {

                if ($list['userAgent'] === $string) {
                    unset($this->trustedBotList[$index]);
                }

                if ($list['rdns'] === $string) {
                    unset($this->trustedBotList[$index]);
                }
            }
        }
    }

    /**
     * {@inheritDoc}
     */
    public function isGoogle(): bool
    {
        if (preg_match('/(google.com|googlebot.com)/i', $this->rdns)) {
            return true;
        }

        return false;
    }

    /**
     * {@inheritDoc}
     */
    public function isYahoo(): bool
    {
        if (preg_match('/(yahoo.com|yahoo.net)/i', $this->rdns)) {
            return true;
        }

        return false;
    }

    /**
     * {@inheritDoc}
     */
    public function isBing(): bool
    {
        if (preg_match('/(msn.com|bing.com|live.com)/i', $this->rdns)) {
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

    /**
     * Check if the current access a fake robot.
     * To get real value from this method, execution must be after `isAllowed`.
     *
     * @return bool
     */
    public function isFakeRobot(): bool
    {
        return $this->isFake;
    }

    /**
     * Unique deny status code.
     *
     * @return int
     */
    public function getDenyStatusCode(): int
    {
        return self::STATUS_CODE;
    }
}