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

use Shieldon\IpTrait;

use function gethostbyname;
use function implode;
use function preg_match;

/**
 * Robot
 */
class Rdns extends ComponentProvider
{
    use IpTrait;

    /**
     * Constructor.
     * 
     * @param bool $strictMode
     * 
     * @return void
     */
    public function __construct()
    {
        // RDNS for robot's IP address.
        $this->deniedList = [
            '.webcrawler.link',
        ];
    }

    /**
     * {@inheritDoc}
     */
    public function isDenied(): bool
    {
        if (! empty($this->deniedList)) {
            if (preg_match('/(' . implode('|', $this->deniedList). ')/i', $this->ipResolvedHostname)) {
                return true;
            }
        }

        if ($this->strictMode) {

            // If strict mode is on, this value can not be empty.
            if (empty($this->ipResolvedHostname)) {
                return true;
            }

            // confirm hostname's IP again
            $ip = gethostbyname($this->ipResolvedHostname);

            // If the IP is different as hostname's resolved IP.
            if ($ip !== $this->ip) {
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
        return 22;
    }
}