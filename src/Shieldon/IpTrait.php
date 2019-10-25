<?php declare(strict_types=1);
/*
 * This file is part of the Shieldon package.
 *
 * (c) Terry L. <contact@terryl.in>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Shieldon;

use function substr;
use function gethostbyaddr;

/*
 * @since 1.0.0
 */
trait IpTrait
{
    /**
     * IP address.
     *
     * @var string
     */
    protected $ip = '';

    /**
     * The RDNS recond of the Robot's IP address.
     * This is the most important value because that most popular search engines' IP can be resolved to
     * their domain name, except Baidu.
     *
     * @var string
     */
    protected $ipResolvedHostname = '';

    /**
     * Set an IP address.
     * If you want to deal with the proxy and CDN IPs.
     *
     * @param string $ip
     * @param bool   $queryRdns
     *
     * @return void
     */
    public function setIp(string $ip = '', $queryRdns = false): void
    {
        if (! empty($ip)) {
            $_SERVER['REMOTE_ADDR'] = $ip;
        }

        $this->ip = $_SERVER['REMOTE_ADDR'];

        if ($queryRdns) {

            // Check if your IP is from localhost, perhaps your are in development environment?
            if (
                (substr($this->ip, 0 , 8) === '192.168.') ||
                (substr($this->ip, 0 , 6) === '127.0.')
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
        $this->ipResolvedHostname = $rdns;
    }

    /**
     * Get IP resolved hostname.
     *
     * @return string
     */
    public function getRdns(): string
    {
        return $this->ipResolvedHostname;
    }
}
