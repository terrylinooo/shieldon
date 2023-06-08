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
use Shieldon\Firewall\Component\DeniedTrait;
use Shieldon\Firewall\IpTrait;
use function Shieldon\Firewall\get_request;

use function implode;
use function preg_match;

/**
 * UserAgent component.
 */
class UserAgent extends ComponentProvider
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
    const STATUS_CODE = 84;

    /**
     * Robot's user-agent text.
     *
     * @var string
     */
    private $userAgent = '';

    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->userAgent = get_request()->getHeaderLine('user-agent');

        /**
         * Those robots are considered as bad behavior.
         * Therefore we list them here.
         */
        $this->deniedList = [

            // Backlink crawlers
            'Ahrefs',     // http://ahrefs.com/robot/
            'roger',      // rogerbot (SEOMOZ)
            'moz.com',    // SEOMOZ crawlers
            'MJ12bot',    // Majestic crawlers
            'findlinks',  // http://wortschatz.uni-leipzig.de/findlinks
            'Semrush',    // http://www.semrush.com/bot.html
    
            // Web information crawlers
            'domain',     // Domain name information crawlers.
            'copyright',  // Copyright information crawlers.
    
            // Others
            'archive',    // Wayback machine
        ];
    }

    /**
     * {@inheritDoc}
     *
     * @return bool
     */
    public function isDenied(): bool
    {
        if (!empty($this->deniedList)) {
            if (preg_match('/(' . implode('|', $this->deniedList). ')/i', $this->userAgent)) {
                return true;
            }
        }

        if ($this->strictMode) {
            // If strict mode is on, this value can not be empty.
            if (empty($this->userAgent)) {
                return true;
            }
        }

        return false;
    }

    /**
     * {@inheritDoc}
     *
     * @return int
     */
    public function getDenyStatusCode(): int
    {
        return self::STATUS_CODE;
    }
}
