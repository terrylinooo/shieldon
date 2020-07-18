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

namespace Shieldon\Firewall\Component;

use Shieldon\Firewall\Component\ComponentProvider;
use Shieldon\Firewall\Kernel\IpTrait;
use function Shieldon\Firewall\get_request;

use function implode;
use function preg_match;

/**
 * UserAgent component.
 */
class UserAgent extends ComponentProvider
{
    use IpTrait;

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
     */
    public function getDenyStatusCode(): int
    {
        return self::STATUS_CODE;
    }
}
