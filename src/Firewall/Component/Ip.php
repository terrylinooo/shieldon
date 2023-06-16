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

use function array_keys;
use function base_convert;
use function count;
use function explode;
use function filter_var;
use function ip2long;
use function pow;
use function str_pad;
use function strpos;
use function substr_count;
use function unpack;

/**
 * Ip component.
 */
class Ip extends ComponentProvider
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
    const STATUS_CODE = 81;

    const REASON_INVALID_IP_DENIED = 40;
    const REASON_DENY_IP_DENIED    = 41;
    const REASON_ALLOW_IP_DENIED   = 42;

    /**
     * Only allow IPs in allowedList, then deny all.
     *
     * @param bool
     */
    protected $isDenyAll = false;

    /**
     * Check an IP if it exists in Anti-Scraping allow/deny list.
     *
     * @param string $ip The IP address.
     *
     * @return array If data entry exists, it will return an array structure:
     *               - status: ALLOW | DENY
     *               - code: status identification code.
     *
     *               if nothing found, it will return an empty array instead.
     */
    public function check(string $ip): array
    {
        $this->setIp($ip);

        if (!filter_var($this->ip, FILTER_VALIDATE_IP)) {
            return [
                'status' => 'deny',
                'code' => self::REASON_INVALID_IP_DENIED,
                'comment' => 'Invalid IP.',
            ];
        }

        if ($this->isAllowed()) {
            return [
                'status' => 'allow',
                'code' => self::REASON_ALLOW_IP_DENIED,
                'comment' => 'IP is in allowed list.',
            ];
        }

        if ($this->isDenied()) {
            return [
                'status' => 'deny',
                'code' => self::REASON_DENY_IP_DENIED,
                'comment' => 'IP is in denied list.',
            ];
        }

        if ($this->isDenyAll) {
            return [
                'status' => 'deny',
                'code' => self::REASON_DENY_IP_DENIED,
                'comment' => 'Deny all in strict mode.',
            ];
        }

        return [];
    }

    /**
     * Check if a given IP is in a network
     *
     * This method is modified from: https://gist.github.com/tott/7684443
     *                https://github.com/cloudflare/CloudFlare-Tools/blob/master/cloudflare/inRange.php
     * We can it test here: http://jodies.de/ipcalc
     *
     * -------------------------------------------------------------------------------
     *  Netmask          Netmask (binary)                    CIDR  Notes
     * -------------------------------------------------------------------------------
     *  255.255.255.255  11111111.11111111.11111111.11111111  /32  Host (single addr)
     *  255.255.255.254  11111111.11111111.11111111.11111110  /31  Unuseable
     *  255.255.255.252  11111111.11111111.11111111.11111100  /30  2   useable
     *  255.255.255.248  11111111.11111111.11111111.11111000  /29  6   useable
     *  255.255.255.240  11111111.11111111.11111111.11110000  /28  14  useable
     *  255.255.255.224  11111111.11111111.11111111.11100000  /27  30  useable
     *  255.255.255.192  11111111.11111111.11111111.11000000  /26  62  useable
     *  255.255.255.128  11111111.11111111.11111111.10000000  /25  126 useable
     *  255.255.255.0    11111111.11111111.11111111.00000000  /24  Class C 254 useable
     *  255.255.254.0    11111111.11111111.11111110.00000000  /23  2   Class C's
     *  255.255.252.0    11111111.11111111.11111100.00000000  /22  4   Class C's
     *  255.255.248.0    11111111.11111111.11111000.00000000  /21  8   Class C's
     *  255.255.240.0    11111111.11111111.11110000.00000000  /20  16  Class C's
     *  255.255.224.0    11111111.11111111.11100000.00000000  /19  32  Class C's
     *  255.255.192.0    11111111.11111111.11000000.00000000  /18  64  Class C's
     *  255.255.128.0    11111111.11111111.10000000.00000000  /17  128 Class C's
     *  255.255.0.0      11111111.11111111.00000000.00000000  /16  Class B
     *  255.254.0.0      11111111.11111110.00000000.00000000  /15  2   Class B's
     *  255.252.0.0      11111111.11111100.00000000.00000000  /14  4   Class B's
     *  255.248.0.0      11111111.11111000.00000000.00000000  /13  8   Class B's
     *  255.240.0.0      11111111.11110000.00000000.00000000  /12  16  Class B's
     *  255.224.0.0      11111111.11100000.00000000.00000000  /11  32  Class B's
     *  255.192.0.0      11111111.11000000.00000000.00000000  /10  64  Class B's
     *  255.128.0.0      11111111.10000000.00000000.00000000  /9   128 Class B's
     *  255.0.0.0        11111111.00000000.00000000.00000000  /8   Class A
     *  254.0.0.0        11111110.00000000.00000000.00000000  /7
     *  252.0.0.0        11111100.00000000.00000000.00000000  /6
     *  248.0.0.0        11111000.00000000.00000000.00000000  /5
     *  240.0.0.0        11110000.00000000.00000000.00000000  /4
     *  224.0.0.0        11100000.00000000.00000000.00000000  /3
     *  192.0.0.0        11000000.00000000.00000000.00000000  /2
     *  128.0.0.0        10000000.00000000.00000000.00000000  /1
     *  0.0.0.0          00000000.00000000.00000000.00000000  /0   IP space
     * -------------------------------------------------------------------------------
     *
     * @param string $ip      IP to check in IPV4 and IPV6 format
     * @param string $ipRange IP/CIDR netmask eg. 127.0.0.0/24, also 127.0.0.1
     *                        is accepted and /32 assumed
     *
     * @return bool true if the ip is in this range / false if not.
     */
    public function inRange(string $ip, string $ipRange): bool
    {
        if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) {
            return $this->inRangeIp4($ip, $ipRange);
        } elseif (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6)) {
            return $this->inRangeIp6($ip, $ipRange);
        }
        return false;
    }

    /**
     * A child function of inRange(), check for IPv4
     *
     * @param string $ip      IP to check in IPV4 and IPV6 format
     * @param string $ipRange IP/CIDR netmask eg. 127.0.0.0/24, also 127.0.0.1
     *                        is accepted and /32 assumed
     *
     * @return bool
     */
    protected function inRangeIp4(string $ip, string $ipRange): bool
    {
        if (strpos($ipRange, '/') === false) {
            $ipRange .= '/32';
        }

        // $range is in IP/CIDR format eg 127.0.0.1/24
        list($ipRange, $netmask) = explode('/', $ipRange, 2);

        $rangeDecimal = ip2long($ipRange);
        $ipDecimal = ip2long($ip);
        $wildcardDecimal = pow(2, (32 - $netmask)) - 1;

        // Bits that are set in $wildcardDecimal are not set, and vice versa.
        // Bitwise Operators:
        // https://www.php.net/manual/zh/language.operators.bitwise.php

        $netmaskDecimal = ~ $wildcardDecimal;

        return (($ipDecimal & $netmaskDecimal) === ($rangeDecimal & $netmaskDecimal));
    }

    /**
     * A child function of inRange(), check for IPv6
     *
     * @param string $ip      IP to check in IPV4 and IPV6 format
     * @param string $ipRange IP/CIDR netmask eg. 127.0.0.0/24, also 127.0.0.1
     *                        is accepted and /32 assumed
     *
     * @return bool
     */
    protected function inRangeIp6(string $ip, string $ipRange): bool
    {
        $ip = $this->decimalIpv6($ip);

        $pieces = explode('/', $ipRange, 2);
        $leftPiece = $pieces[0];

        // Extract out the main IP pieces
        $ipPieces = explode('::', $leftPiece, 2);
        $mainIpPiece = $ipPieces[0];
        $lastIpPiece = $ipPieces[1];

        // Pad out the shorthand entries.
        $mainIpPieces = explode(':', $mainIpPiece);

        foreach (array_keys($mainIpPieces) as $key) {
            $mainIpPieces[$key] = str_pad($mainIpPieces[$key], 4, '0', STR_PAD_LEFT);
        }

        // Create the first and last pieces that will denote the IPV6 range.
        $first = $mainIpPieces;
        $last = $mainIpPieces;

        // Check to see if the last IP block (part after ::) is set
        $size = count($mainIpPieces);

        if (trim($lastIpPiece) !== '') {
            $lastPiece = str_pad($lastIpPiece, 4, '0', STR_PAD_LEFT);

            // Build the full form of the IPV6 address considering the last IP block set
            for ($i = $size; $i < 7; $i++) {
                $first[$i] = '0000';
                $last[$i] = 'ffff';
            }

            $mainIpPieces[7] = $lastPiece;
        } else {
            // Build the full form of the IPV6 address
            for ($i = $size; $i < 8; $i++) {
                $first[$i] = '0000';
                $last[$i] = 'ffff';
            }
        }

        // Rebuild the final long form IPV6 address
        $first = $this->decimalIpv6(implode(':', $first));
        $last = $this->decimalIpv6(implode(':', $last));

        return ($ip >= $first && $ip <= $last);
    }

    /**
     * Get the ipv6 full format and return it as a decimal value.
     *
     * @param string $ip The IP address.
     *
     * @return string
     */
    public function decimalIpv6(string $ip): string
    {
        if (substr_count($ip, '::')) {
            $ip = str_replace('::', str_repeat(':0000', 8 - substr_count($ip, ':')) . ':', $ip);
        }

        $ip = explode(':', $ip);
        $rIp = '';

        foreach ($ip as $v) {
            $rIp .= str_pad(base_convert($v, 16, 2), 16, '0', STR_PAD_LEFT);
        }
        return base_convert($rIp, 2, 10);
    }

    /**
     * Get the ipv6 full format and return it as a decimal value. (Confirmation version)
     *
     * @param string $ip The IP address.
     *
     * @return string
     */
    public function decimalIpv6Confirm($ip): string
    {
        $binNum = '';
        foreach (unpack('C*', inet_pton($ip)) as $byte) {
            $binNum .= str_pad(decbin($byte), 8, "0", STR_PAD_LEFT);
        }
        return base_convert(ltrim($binNum, '0'), 2, 10);
    }

    /**
     * {@inheritDoc}
     *
     * @return bool
     */
    public function isDenied(): bool
    {
        foreach ($this->deniedList as $deniedIp) {
            if (strpos($deniedIp, '/') !== false) {
                if ($this->inRange($this->ip, $deniedIp)) {
                    return true;
                }
            } else {
                if ($deniedIp === $this->ip) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * {@inheritDoc}
     *
     * @return bool
     */
    public function isAllowed(): bool
    {
        foreach ($this->allowedList as $allowedIp) {
            if (strpos($allowedIp, '/') !== false) {
                if ($this->inRange($this->ip, $allowedIp)) {
                    return true;
                }
            } else {
                if ($allowedIp === $this->ip) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * Only allow IPs in allowedList, then deny all.
     *
     * @return bool
     */
    public function denyAll(): bool
    {
        return $this->isDenyAll = true;
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
