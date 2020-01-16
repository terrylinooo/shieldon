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

use function implode;
use function preg_match;
use function str_replace;
use function strtolower;
use function substr;
use function ucwords;

/**
 * Robot
 */
class Header extends ComponentProvider
{
    use IpTrait;

    const STATUS_CODE = 83;

    /**
     *  Very common requests from normal users.
     * 
     * @var string
     */
    protected $commonHeaderFileds = [
        'Accept-Language',
        'Accept-Encoding',
        'User-Agent',
        'Upgrade-Insecure-Requests',
        'Cache-Control',
        'Connection',
        'Host',
    ];

    /**
     * {@inheritDoc}
     */
    public function isDenied(): bool
    {
        $headers = $this->getHeaders();

        if (! empty($this->deniedList)) {
            if (preg_match('/(' . implode('|', $this->deniedList). ')/i', implode(',', $headers))) {
                return true;
            }
        }

        if ($this->strictMode) {

            foreach ($this->commonHeaderFileds as $fieldName) {
                // If strict mode is on, this value must be found.
                if (! isset($headers[$fieldName])) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * All request headers.
     *
     * @return array
     */
    public function getHeaders(): array
    {
        $headers = [];

        foreach ($_SERVER as $name => $value) {
            if (substr($name, 0, 5) == 'HTTP_') {

                // For example:
                // HTTP_ACCEPT_LANGUAGE => Accept Language
                $rawName = ucwords(strtolower(str_replace('_', ' ', substr($name, 5))));

                // Accept Language => Accept-Language
                $key = str_replace(' ', '-', $rawName);
                $headers[$key] = $value;
            }
        }

        return $headers;
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