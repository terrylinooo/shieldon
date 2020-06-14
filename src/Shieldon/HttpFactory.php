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

use Shieldon\Psr7\Factory\ServerRequestFactory;
use Shieldon\Psr7\Factory\ResponseFactory;
use Shieldon\Psr7\ServerRequest;
use Shieldon\Psr7\Response;
use Shieldon\Utils\Session;

/*
 * An object-oriented layer for the HTTP specification.
 */
class HttpFactory
{
    /**
     * Create a server-side request.
     *
     * @return ServerRequestFactory
     */
    public function createRequest(): ServerRequest
    {
        $serverRequestFactory = new ServerRequestFactory(true);
        $serverRequest = $serverRequestFactory->createServerRequest('', '');

        return $serverRequest;
    }

    /**
     * Create a server response.
     *
     * @return Response
     */
    public function createResponse(): Response
    {
        $responseFactory = new ResponseFactory();
        $response = $responseFactory->createResponse();

        return $response;
    }

    /**
     * Create a Session collection from superglobal.
     *
     * @param $id Session ID
     *
     * @return Collection
     */
    public function createSession($id = ''): Session
    {
        return new Session($id);
    }
}