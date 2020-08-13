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

use Shieldon\Firewall\Session;
use Shieldon\Psr17\ResponseFactory;
use Shieldon\Psr17\ServerRequestFactory;
use Shieldon\Psr17\StreamFactory;
use Shieldon\Psr7\Response;
use Shieldon\Psr7\ServerRequest;
use Shieldon\Psr7\Stream;

/**
 * An object-oriented layer for the HTTP specification.
 */
class HttpFactory
{
    /**
     * Create a server-side request.
     *
     * @return ServerRequest
     */
    public static function createRequest(): ServerRequest
    {
        return ServerRequestFactory::fromGlobal();
    }

    /**
     * Create a server-side response
     *
     * @return Response
     */
    public static function createResponse(): Response
    {
        return ResponseFactory::fromNew();
    }

    /**
     * Create a server-side response
     *
     * @return Stream
     */
    public static function createStream(): Stream
    {
        return StreamFactory::fromNew();
    }

    /**
     * Create a session by using Shieldon's Session driver.
     *
     * @param string $id Session ID
     *
     * @return Session
     */
    public static function createSession($id = ''): Session
    {
        return new Session($id);
    }
}
