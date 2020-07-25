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
use Shieldon\Firewall\IpTrait;

use function gethostbyname;
use function implode;
use function preg_match;

/**
 * Rdns component.
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
     * 
     * @return bool
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
