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
use Psr\Http\Message\ServerRequestInterface;
use Shieldon\HttpFactory;

use function implode;
use function preg_match;

/**
 * UserAgent
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
     * Those robots are considered as bad behavior. 
     * Therefore we list them here.
     *
     * @var array
     */
    protected $deniedList = [

        // Web information crawlers
        'domain',     // Domain name information crawlers.
        'copyright',  // Copyright information crawlers.

        // SEO backlink crawlers
        'Ahrefs',     // http://ahrefs.com/robot/
        'roger',      // rogerbot (SEOMOZ)
        'moz.com',    // SEOMOZ crawlers
        'MJ12bot',    // Majestic crawlers
        'findlinks',  // http://wortschatz.uni-leipzig.de/findlinks
        'Semrush',    // http://www.semrush.com/bot.html

        // Others
        'archive',    // Wayback machine
    ];

    /**
     * Constructor.
     */
    public function __construct(?ServerRequestInterface $request  = null)
    {
        parent::__construct($request);

        $this->userAgent = $this->request->getHeaderLine('user-agent');
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
     * Unique deny status code.
     *
     * @return int
     */
    public function getDenyStatusCode(): int
    {
        return self::STATUS_CODE;
    }
}
