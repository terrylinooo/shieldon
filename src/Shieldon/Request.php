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

use function implode;
use function is_array;
use function parse_str;
use function str_replace;
use function strtolower;
use function substr;

/*
 * A HTTP request wrapper similar to PSR-7 manner.
 * 
 * @since 1.1.0
 */
class Request
{
    /**
     * @var array
     */
    protected $headers;

    /**
     * @var array
     */
    protected $parsedBody;

    /**
     * @var array
     */
    protected $cookieParams;

    /**
     * @var array
     */
    protected $serverParams;

    /**
     * @var array
     */
    protected $queryParams;

    /**
     * Constructor.
     */
    public function __construct()
    {
        if (! isset($this->headers)) {
            foreach ($this->getServerParams() as $name => $value) {
                if (substr($name, 0, 5) == 'HTTP_') {

                    // ex: HTTP_ACCEPT_LANGUAGE => accept-language
                    $key = strtolower(str_replace('_', '-', substr($name, 5)));
                    $this->headers[$key] = $value;
                }
            }
        }

        if (! isset($this->queryParams)) {
            if (! empty($_SERVER['QUERY_STRING'])) {
                parse_str($_SERVER['QUERY_STRING'] , $this->queryParams);
            } else {
                $this->queryParams = [];
            }
        }

        if (! isset($this->parsedBody)) {
            if (! empty($_POST)) {
                $this->parsedBody = $_POST;
            } else {
                $this->parsedBody = [];
            }
        }

        if (! isset($this->cookieParams)) {
            if (! empty($_COOKIE)) {
                $this->cookieParams = $_COOKIE;
            } else {
                $this->cookieParams = [];
            }
        }

        if (! isset($this->serverParams)) {
            if (! empty($_SERVER)) {
                $this->serverParams = $_SERVER;
            } else {
                $this->serverParams = [];
            }
        }

        Container::set('request', $this);
    }

    /*
    |--------------------------------------------------------------------------
    | PSR-7 Methods.
    |--------------------------------------------------------------------------
    */

    /**
     * Retrieves all message header values.
     *
     * @return array
     */
    public function getHeaders()
    {
        return $this->headers;
    }

    /**
     * Checks if a header exists by the given case-insensitive name.
     *
     * @param string $name $name Case-insensitive header field name.
     *
     * @return bool
     */
    public function hasHeader(string $name)
    {
        if (isset($this->headers[$name])) {
            return true;
        }

        return false;
    }

    /**
     * Retrieves a comma-separated string of the values for a single header.
     *
     * @param string $name Case-insensitive header field name.
     *
     * @return string
     */
    public function getHeaderLine(string $name)
    {
        if (isset($this->headers[$name])) {
            return (is_array($this->headers[$name]) ? implode(', ', $this->headers[$name]) : $this->headers[$name]);
        }

        return '';
    }

    /**
     * Retrieve query string arguments.
     *
     * @return array
     */
    public function  getQueryParams()
    {
        return $this->queryParams;
    }

    /**
     * Retrieve any parameters provided in the request body.
     *
     * @return array
     */
    public function getParsedBody()
    {
        return $this->parsedBody;
    }

    /**
     * Retrieve cookies.
     *
     * @return array
     */
    public function getCookieParams()
    {
        return $this->cookieParams;
    }

    /**
     * Retrieve server parameters.
     *
     * @return array
     */
    public function getServerParams()
    {
        return $this->serverParams;
    }

    /*
    |--------------------------------------------------------------------------
    | Non-PSR-7 Methods.
    |--------------------------------------------------------------------------
    */


}
