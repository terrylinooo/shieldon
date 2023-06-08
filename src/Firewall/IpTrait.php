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

namespace Shieldon\Firewall;

use function substr;
use function gethostbyaddr;
use function Shieldon\Firewall\set_ip;

/**
 * IP Trait
 */
trait IpTrait
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

    /**
     * IP address.
     *
     * @var string
     */
    protected $ip = '';

    /**
     * The RDNS recond of the Robot's IP address.
     * This is the most important value because that the IP of the most popular
     * search engines can be resolved to their domain name.
     *
     * @var string
     */
    protected $rdns = '';

    /**
     * Set an IP address.
     * If you want to deal with the proxy and CDN IPs.
     *
     * @param string $ip        The IP address.
     * @param bool   $queryRdns The option to query RDNS.
     *
     * @return void
     */
    public function setIp(string $ip, $queryRdns = false): void
    {
        $this->ip = $ip;
        
        set_ip($this->ip);

        if ($queryRdns) {
            // Check if your IP is from localhost, perhaps your are in development
            // environment?
            if (substr($this->ip, 0, 8) === '192.168.' ||
                substr($this->ip, 0, 6) === '127.0.'
            ) {
                $this->setRdns('localhost');
            } else {
                $this->setRdns(gethostbyaddr($this->ip));
            }
        }
    }

    /**
     * Get current set IP.
     *
     * @return string
     */
    public function getIp(): string
    {
        return $this->ip;
    }

    /**
     * Set a RDNS record for the check.
     *
     * @param string $rdns Reserve DNS record for that IP address.
     *
     * @return void
     */
    public function setRdns($rdns): void
    {
        $this->rdns = $rdns;
    }

    /**
     * Get IP resolved hostname.
     *
     * @return string
     */
    public function getRdns(): string
    {
        return $this->rdns;
    }
}
