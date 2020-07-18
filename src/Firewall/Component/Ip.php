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
use Shieldon\Firewall\Component\AllowedTrait;
use Shieldon\Firewall\Kernel\IpTrait;

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
    use IpTrait;
    use AllowedTrait;

    const STATUS_CODE = 81;

    /**
     * Constant
     */
    const REASON_INVALID_IP = 40;
    const REASON_DENY_IP    = 41;
    const REASON_ALLOW_IP   = 42;

    /**
     * Only allow IPs in allowedList, then deny all.
     * 
     * @param bool
     */
    protected $isDenyAll = false;

    /**
     * Check an IP if it exists in Anti-Scraping allow/deny list.
     *
     * @param string $ip
     *
     * @return array If data entry exists, it will return an array structure:
     *               - status: ALLOW | DENY
     *               - code: status identification code.
     *
     *               if nothing found, it will return an empty array instead.
     */
    public function check(string $ip = ''): array
    {
        if ('' !== $ip) {
            $this->setIp($ip);
        }

        if (!filter_var($this->ip, FILTER_VALIDATE_IP)) {
            return [
                'status' => 'deny',
                'code' => self::REASON_INVALID_IP,
                'comment' => 'Invalid IP.',
            ];
        }

        if ($this->isAllowed()) {
            return [
                'status' => 'allow',
                'code' => self::REASON_ALLOW_IP,
                'comment' => 'IP is in allowed list.',
            ];
        }

        if ($this->isDenied()) {
            return [
                'status' => 'deny',
                'code' => self::REASON_DENY_IP,
                'comment' => 'IP is in denied list.',
            ];
        }

        if ($this->isDenyAll) {
            return [
                'status' => 'deny',
                'code' => self::REASON_DENY_IP,
                'comment' => 'Deny all in strict mode.',
            ];
        }

        return [];
    }

    /**
     * Check if a given IP is in a network
     *
     * modified from: https://gist.github.com/tott/7684443
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
     * @param  string $ip    IP to check in IPV4 and IPV6 format
     * @param  string $range IP/CIDR netmask eg. 127.0.0.0/24, also 127.0.0.1 is accepted and /32 assumed
     *
     * @return bool true if the ip is in this range / false if not.
     */
    public function inRange(string $ip, string $ipRange): bool
    {
        if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) {

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

        } elseif (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6)) {

            $ip = $this->decimalIpv6($ip);

            $pieces = explode('/', $ipRange, 2);
            $leftPiece = $pieces[0];

            // Extract out the main IP pieces
            $ipPieces = explode('::', $leftPiece, 2);
            $mainIpPiece = $ipPieces[0];
            $lastIpPiece = $ipPieces[1];

            // Pad out the shorthand entries.
            $mainIpPieces = explode(':', $mainIpPiece);

            foreach ($mainIpPieces as $key => $val) {
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

        return false;
    }

    /**
     * Calculate an IP/CIDR to it's range.
     * 
     * For example:
     * 
     * '69.63.176.0/20' => [
     *    0 => '69.63.176.0',    (min)
     *    1 => '69.63.191.255',  (max)
     * ];
     *
     * @param string $ip4Range  IP/CIDR
     * @param bool   $isDecimal Return IP string to decimal.
     *
     * @return array
     */
    public function ipv4range($ip4Range, $isDecimal = false): array
    {
        $result = [];

        $ipData = explode('/', $ip4Range);

        $ip = $ipData[0];
        $cidr = (int) $ipData[1] ?? 32;

		$result[0] = long2ip((ip2long($ip)) & ((-1 << (32 - $cidr))));
        $result[1] = long2ip((ip2long($ip)) + pow(2, (32 - $cidr)) - 1);

        if ($isDecimal) {
            $result[0] = ip2long($result[0]);
            $result[1] = ip2long($result[1]);
        }

		return $result;
    }

    /**
     * Get the ipv6 full format and return it as a decimal value.
     *
     * @param string $ip
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
     * @param string $ip
     * @return string
     */
    public function _decimalIpv6($ip): string
    {
        $binNum = '';
        foreach (unpack('C*', inet_pton($ip)) as $byte) {
            $binNum .= str_pad(decbin($byte), 8, "0", STR_PAD_LEFT);
        }
        return base_convert(ltrim($binNum, '0'), 2, 10);
    }

    /**
     * {@inheritDoc}
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