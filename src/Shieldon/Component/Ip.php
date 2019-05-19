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

use Shieldon\Driver\DriverProvider;
use function base_convert;
use function filter_var;
use function in_array;
use function strpos;
use function explode;
use function substr_count;

/**
 * Ip
 */
class Ip implements ComponentInterface
{
    /**
     * Constant
     */
    public const CODE_INVAILD_IP = 99;
    public const CODE_DENY_IP = 11;
    public const CODE_DENY_IP_RANGE = 12;
    public const CODE_DENY_IP_RULE = 13;
    public const CODE_ALLOW_IP = 21;
    public const CODE_ALLOW_IP_RANGE = 22;
    public const CODE_ALLOW_IP_RULE = 23;

    /**
     * Data pool for hard whitelist.
     *
     * @var array
     */
    protected $allowedList = [];

    /**
     * Data pool for hard blacklist.
     *
     * @var array
     */
    protected $deniedList = [];

    /**
     * Check an IP if it exists in Anti-Scraping allow/deny list.
     *
     * @param string $ip   IP address you want to check.
     * @param mixed  $rule The callback function to do a finall check for the IP address.
     *
     * @return array If data entry exists, it will return an array structure:
     *               - status: ALLOW | DENY
     *               - code: status identification code.
     *
     *               if nothing found, it will return an empty array instead.
     */
    public function check(string $ip, $rule = null): array
    {
        if (! filter_var($ip, FILTER_VALIDATE_IP)) {
            return [
                'status' => 'deny',
                'code' => self::CODE_INVAILD_IP,
                'comment' => 'Invalid IP.',
            ];
        }

        if (in_array($ip, $this->deniedList)) {
            return [
                'status' => 'deny',
                'code' => self::CODE_DENY_IP,
                'comment' => 'IP is in denied list.',
            ];
        }

        if (in_array($ip, $this->allowedList)) {
            return [
                'status' => 'allow',
                'code' => self::CODE_ALLOW_IP,
                'comment' => 'IP is in allowed list.',
            ];
        }

        foreach ($this->deniedList as $deniedIp) {
            if (strpos($deniedIp, '/') !== false) {
                if ($this->inRange($ip, $deniedIp)) {
                    return [
                        'status' => 'deny',
                        'code' => self::CODE_DENY_IP_RANGE,
                        'comment' => 'IP is denied list. (IP range)',
                    ];
                }
            }
        }

        foreach ($this->allowedList as $allowedIp) {
            if (strpos($allowedIp, '/') !== false) {
                if ($this->inRange($ip, $allowedIp)) {
                    return [
                        'status' => 'allow',
                        'code' => self::CODE_DENY_IP_RANGE,
                        'comment' => 'IP is allowed list. (IP range)',
                    ];
                }
            }
        }

        if (is_callable($rule)) {

            $result = call_user_func($rule);

            if (
                   isset($result['ip'])
                && isset($result['type'])
                && isset($result['reason'])
            ) {
    
                if (1 === (int) $result['type']) {
                    return [
                        'status' => 'allow',
                        'code' => self::CODE_ALLOW_IP_RULE,
                        'comment' => 'IP is allowed in rule table.',
                        'reason' => $result['reason'],
                    ];
                }
    
                if (0 === (int) $result['type']) {
                    return [
                        'status' => 'deny',
                        'code' => self::CODE_DENY_IP_RULE,
                        'comment' => 'IP is denied in rule table.',
                        'reason' => $result['reason'],
                    ];
                }
            }
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

            $ip = $this->ip2long6($ip);

            $pieces = explode('/', $ipRange, 2);
            $left_piece = $pieces[0];

            // Extract out the main IP pieces
            $ipPieces = explode('::', $left_piece, 2);
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
                $last_piece = str_pad($lastIpPiece, 4, '0', STR_PAD_LEFT);

                // Build the full form of the IPV6 address considering the last IP block set
                for ($i = $size; $i < 7; $i++) {
                    $first[$i] = '0000';
                    $last[$i] = 'ffff';
                }

                $mainIpPieces[7] = $last_piece;
    
            } else {
    
                // Build the full form of the IPV6 address
                for ($i = $size; $i < 8; $i++) {
                    $first[$i] = '0000';
                    $last[$i] = 'ffff';
                }
            }

            // Rebuild the final long form IPV6 address
            $first = $this->ip2long6(implode(':', $first));
            $last = $this->ip2long6(implode(':', $last));

            return ($ip >= $first && $ip <= $last);
        }

        return false;
    }

    /**
     * Get the ipv6 full format and return it as a decimal value.
     *
     * @param string $ip
     * @return string
     */
    public function ip2long6(string $ip): string
    {
        if (substr_count($ip, '::')) {
            $ip = str_replace('::', str_repeat(':0000', 8 - substr_count($ip, ':')) . ':', $ip);
        }

        $ip = explode(':', $ip);
        $rIp = '';

        foreach ($ip as $v) {
            $rIp .= str_pad(base_convert($v, 16, 2), 16, 0, STR_PAD_LEFT);
        }

        return base_convert($rIp, 2, 10);
    }

    /**
     * Add an IP address to the whitelist pool.
     *
     * @param string $ip
     * @return bool
     */
    public function setAllowedList(string $ip): bool
    {
        if (! in_array($this->allowedList, $ip)) {
            array_push($this->allowedList, $ip);
        }

        // This method will also remove an IP address from blacklist pool, if exists.
        $this->removeIp($ip, 'deny');
    }

    /**
     * Add an IP address to the whitelist pool.
     *
     * @param string $ip
     * @return bool
     */
    public function setDeniedList(string $ip): bool
    {
        if (! in_array($this->deniedList, $ip)) {
            array_push($this->deniedList, $ip);
        }

        // This method will also remove an IP address from whitelist pool, if exists.
        $this->removeIp($ip, 'allow');
    }

    /**
     * Remove an IP from the Pool
     *
     * @param string $ip   IP address.
     * @param string $type
     *
     * @return void
     */
    public function removeIp(string $ip, string $type): void
    {
        if ('allow' === $type) {
            if (($key = array_search($ip, $this->allowedList)) !== false) {
                unset($this->allowedList[$key]);
            }
        }

        if ('deny' === $type) {
            if (($key = array_search($ip, $this->deniedList)) !== false) {
                unset($this->deniedList[$key]);
            }
        }
    }
}