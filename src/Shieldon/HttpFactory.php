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

namespace Shieldon;

use Shieldon\Psr17\ServerRequestFactory;
use Shieldon\Psr17\ResponseFactory;
use Shieldon\Psr7\ServerRequest;
use Shieldon\Psr7\Response;
use Shieldon\Utils\Session;
use Shieldon\Utils\Collection;

/*
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
     * Create a Session collection from superglobal.
     * This method is not a PSR-7 pattern.
     *
     * @param string $id Session ID
     *
     * @return Collection
     */
    public static function createSession($id = ''): Collection
    {
        $session = new Session($id);

        return $session->createFromGlobal();
    }
}