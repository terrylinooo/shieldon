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

namespace Shieldon\Firewall\Component;

use Shieldon\Firewall\Component\ComponentProvider;
use Shieldon\Firewall\Component\AllowedTrait;
use Shieldon\Firewall\Component\DeniedTrait;
use Shieldon\Firewall\IpTrait;

use function Shieldon\Firewall\get_request;

use function array_column;
use function array_unique;
use function gethostbyname;
use function implode;
use function preg_match;
use function strstr;

/**
 * TrustedBot component.
 */
class TrustedBot extends ComponentProvider
{
    /**
     *   Public methods       | Desctiotion
     *  ----------------------|---------------------------------------------
     *   setIp                | Set an IP address.
     *   getIp                | Get current set IP.
     *   setRdns              | Set a RDNS record for the check.
     *   getRdns              | Get IP resolved hostname.
     *  ----------------------|---------------------------------------------
     */
    use IpTrait;

    /**
     *   Public methods       | Desctiotion
     *  ----------------------|---------------------------------------------
     *   setAllowedItems      | Add items to the whitelist pool.
     *   setAllowedItem       | Add an item to the whitelist pool.
     *   getAllowedItems      | Get items from the whitelist pool.
     *   getAllowedItem       | Get an item from the whitelist pool.
     *   removeAllowedItem    | Remove an allowed item if exists.
     *   removeAllowedItems   | Remove all allowed items.
     *   hasAllowedItem       | Check if an allowed item exists.
     *   getAllowByPrefix     | Check if an allowed item exists have the same prefix.
     *   removeAllowByPrefix  | Remove allowed items with the same prefix.
     *   isAllowed            | Check if an item is allowed?
     *  ----------------------|---------------------------------------------
     */
    use AllowedTrait;

    /**
     *   Public methods       | Desctiotion
     *  ----------------------|---------------------------------------------
     *   setDeniedItems       | Add items to the blacklist pool.
     *   setDeniedItem        | Add an item to the blacklist pool.
     *   getDeniedItems       | Get items from the blacklist pool.
     *   getDeniedItem        | Get items from the blacklist pool.
     *   removeDeniedItem     | Remove a denied item if exists.
     *   removeDeniedItems    | Remove all denied items.
     *   hasDeniedItem        | Check if a denied item exists.
     *   getDenyWithPrefix    | Check if a denied item exists have the same prefix.
     *   removeDenyWithPrefix | Remove denied items with the same prefix.
     *   isDenied             | Check if an item is denied?
     *  ----------------------|---------------------------------------------
     */
    use DeniedTrait;

    /**
     * Constant
     */
    const STATUS_CODE = 85;

    /**
     * Robot's user-agent text.
     *
     * @var string
     */
    private $userAgent = '';

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

        $this->allowedList = [

            // Search engline: Google.
            'google_1' => [
                'userAgent' => 'google',
                'rdns'      => '.googlebot.com',
            ],
    
            'google_2' => [
                'userAgent' => 'google',
                'rdns'      => '.google.com',
            ],
    
            // Search engline: Mircosoft.
            'bing_1' => [
                'userAgent' => 'live',
                'rdns'      => '.live.com',
            ],
    
            'bing_2' => [
                'userAgent' => 'msn',
                'rdns'      => '.msn.com',
            ],
    
            'bing_3' => [
                'userAgent' => 'bing',
                'rdns'      => '.bing.com',
            ],
    
            // Search engline: Yahoo.
            'yahoo_1' => [
                'userAgent' => 'inktomisearch',
                'rdns'      => '.inktomisearch.com',
            ],
    
            'yahoo_2' => [
                'userAgent' => 'yahoo',
                'rdns'      => '.yahoo.com',
            ],
    
            'yahoo_3' => [
                'userAgent' => 'yahoo',
                'rdns'      => '.yahoo.net',
            ],
    
            // Search engine: Yandex.
            'yandex_1' => [
                'userAgent' => 'yandex',
                'rdns'      => '.yandex.com',
            ],
    
            'yandex_2' => [
                'userAgent' => 'yandex',
                'rdns'      => '.yandex.net',
            ],
    
            'yandex_3' => [
                'userAgent' => 'yandex',
                'rdns'      => '.yandex.ru',
            ],
    
            // Facebook crawlers.
            'facebook' => [
                'userAgent' => 'facebook',
                'rdns'      => '.fbsv.net',
            ],
    
            // Twitter crawlers.
            'twitter' => [
                'userAgent' => 'Twitterbot',
                'rdns'      => '.twttr.com', // (not twitter.com)
            ],
    
            // W3C validation services.
            'w3' => [
                'userAgent' => 'w3.org',
                'rdns'      => '.w3.org',
            ],
    
            // Ask.com crawlers.
            'ask' => [
                'userAgent' => 'ask',
                'rdns'      => '.ask.com',
            ],
        ];

        $this->deniedList = [];
    }

    /**
     * Check the user-agent string and rdns in the trusted list.
     *
     * @return bool
     */
    public function isAllowed(): bool
    {
        $userAgent = array_unique(
            array_column($this->allowedList, 'userAgent')
        );

        if (!preg_match('/(' . implode('|', $userAgent) . ')/i', $this->userAgent)) {
            // Okay, current request's user-agent string doesn't contain our truested bots' infroamtion.
            // Ignore it.
            return false;
        }

        $rdns = array_unique(
            array_column($this->allowedList, 'rdns')
        );

        $rdnsCheck = false;

        // We will check the RDNS record to see if it is in the whitelist.
        if (preg_match('/(' . implode('|', $rdns) . ')/i', $this->rdns)) {
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

            if ($rdnsCheck) {
                $ip = gethostbyname($this->rdns);

                if ($this->strictMode) {
                    if ($ip !== $this->ip) {
                        // If the IP is different as hostname's resolved IP. It might be a fake bot.
                        $this->isFake = true;
                        return false;
                    }
                }
            } else {
                // We can identify that current access uses a fake RDNS record.
                $this->isFake = true;
                return false;
            }

            return true;
        }

        // Here, once a request uses a user-agent that contains search engine information,
        // but it does't pass the RDNS check.
        // We can identify it is fake.
        $this->isFake = true;

        return false;
    }

    /**
     * {@inheritDoc}
     *
     * @return bool
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
     *
     * @return bool
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
     *
     * @return bool
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

    /**
     * Add new items to the allowed list.
     *
     * @param string $name      The key for this inforamtion.
     * @param string $useragent A piece of user-agent string that can identify.
     * @param string $rdns      The RDNS inforamtion of the bot.
     *
     * @return void
     */
    public function addTrustedBot(string $name, string $useragent, string $rdns)
    {
        $this->setAllowedItem(
            [
                'userAgent' => $useragent,
                'rdns' => $rdns,
            ],
            $name
        );
    }
}
