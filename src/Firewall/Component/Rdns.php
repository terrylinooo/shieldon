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

use Shieldon\Firewall\IpTrait;

use function gethostbyname;
use function implode;
use function preg_match;

/**
 * Robot
 */
class Rdns extends ComponentProvider
{
    use IpTrait;

    const STATUS_CODE = 82;

    /**
     * Constructor.
     */
    public function __construct()
    {
        // RDNS for robot's IP address.
        $this->deniedList = [
            'unknown_1' => '.webcrawler.link',
        ]; 
    }

    /**
     * {@inheritDoc}
     */
    public function isDenied(): bool
    {
        if (!empty($this->deniedList)) {
            if (preg_match('/(' . implode('|', $this->deniedList). ')/i', $this->rdns)) {
                return true;
            }
        }

        if ($this->strictMode) {

            // If strict mode is on, this value can not be empty.
            if (empty($this->rdns)) {
                return true;
            }

            // If the RDNS is an IP adress, not a FQDN.
            if ($this->ip === $this->rdns) {
                return true;
            }

            // Not a valid domain name.
            if (!filter_var($this->rdns, FILTER_VALIDATE_DOMAIN, FILTER_FLAG_HOSTNAME)) {
                return true;
            }

            // confirm hostname's IP again
            $ip = gethostbyname($this->rdns);

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
        return self::STATUS_CODE;
    }
}