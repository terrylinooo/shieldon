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

namespace Shieldon\Psr7;

use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\UriInterface;
use Shieldon\Psr7\Message;
use Shieldon\Psr7\Uri;
use InvalidArgumentException;

use function in_array;
use function is_string;

// https://github.com/slimphp/Slim-Psr7
// https://github.com/slimphp/Slim-Http
// https://github.com/guzzle/psr7
// https://www.php-fig.org/psr/psr-7/

/*
 * Representation of an outgoing, client-side request.
 */
class Request extends Message implements RequestInterface
{
    /**
     * The HTTP method of the outgoing request.
     *
     * @var string
     */
    protected $method;

    /**
     * The target URL of the outgoing request.
     *
     * @var string
     */
    protected $requestTarget;

    /**
     * A UriInterface object.
     *
     * @var UriInterface
     */
    protected $uri;

    /**
     * Request constructor.
     *
     * @param string                 $method   Request HTTP method
     * @param UriInterface|string    $uri      Request URI object URI or URL
     * @param StreamInterface|string $body     Request body
     * @param array                  $headers  Request headers
     * @param string                 $version  Request protocol version
     */
    public function __construct(
        string $method  = 'GET',
               $uri     = ''   ,
               $body    = ''   ,
        array  $headers = []   ,
        string $version = '1.1'
    ) {
        $this->assertMethod($method);
        $this->assertProtocolVersion($version);

        if ($uri instanceof UriInterface) {
            $this->uri = $uri;
        } else  {
            if (is_string($uri)) {
                $this->uri = new Uri($uri);
            } else {
                throw new InvalidArgumentException(
                    'URI should be a string or an instance of UriInterface.'
                );
            }
        }

        if (! empty($headers)) {
            $this->setHeaders($headers);
        }

        if (! empty($body)) {
            $this->setBody($body);
        }

        $this->protocolVersion = $version;
    }

    /**
     * {@inheritdoc}
     */
    public function getRequestTarget(): string
    {
        if ($this->requestTarget) {
            return $this->requestTarget;
        }

        $path = $this->uri->getPath();
        $query = $this->uri->getQuery();

        if (empty($path)) {
            $path = '/';
        }

        if (! empty($query)) {
            $path .= '?' . $query;
        }

        return $path;
    }

    /**
     * {@inheritdoc}
     */
    public function withRequestTarget($requestTarget)
    {
        if (! is_string($requestTarget)) {
            throw new InvalidArgumentException(
                'A request target must be a string.'
            );
        }

        if (preg_match('/\s/', $requestTarget)) {
            throw new InvalidArgumentException(
                'A request target cannot contain whitespace.'
            );
        }

        $clone = clone $this;
        $clone->requestTarget = $requestTarget;

        return $clone;
    }

    /**
     * {@inheritdoc}
     */
    public function getMethod()
    {
        return $this->method;
    }

    /**
     * {@inheritdoc}
     */
    public function withMethod($method)
    {
        $this->assertMethod($method);

        $new = clone $this;
        $new->method = strtoupper($method);

        return $new;
    }

    /**
     * {@inheritdoc}
     */
    public function getUri(): UriInterface
    {
        return $this->uri;
    }

    /**
     * {@inheritdoc}
     */
    public function withUri(UriInterface $uri, $preserveHost = false)
    {
        $host = $uri->getHost();

        if (
            // This method MUST update the Host header of the returned request by
            // default if the URI contains a host component.
            (! $preserveHost && $host !== '') ||

            // When `$preserveHost` is set to `true`.
            // If the Host header is missing or empty, and the new URI contains
            // a host component, this method MUST update the Host header in the returned
            // request.
            ($preserveHost && ! $this->hasHeader('Host') && $host !== '')
        ) {
            $clone = clone $this;
            $clone->uri = $uri;

            $headers = $this->getHeaders();
            $headers['host'] = $host;
            $clone->setHeaders($headers);
            return $clone;
        }

        return $this;
    }

    /*
    |--------------------------------------------------------------------------
    | Non PSR-7 Methods.
    |--------------------------------------------------------------------------
    */

    /**
     * Check out whether a method defined in RFC 7231 request methods.
     * https://tools.ietf.org/html/rfc7231
     *
     * @param string $method Http methods
     * 
     * @return void
     * 
     * @throws InvalidArgumentException
     */
    protected function assertMethod(string $method): void
    {
        $requestMethods = [

            // The HEAD method asks for a response identical to that of a GET
            // request, but without the response body.
            'HEAD',

            // The GET method requests a representation of the specified 
            // resource. Requests using GET should only retrieve data.
            'GET',

            // The POST method is used to submit an entity to the specified 
            // resource, often causing a change in state or side effects on the
            // server.
            'POST', 
            
            // The PUT method replaces all current representations of the target
            // resource with the request payload.
            'PUT', 

            // The DELETE method deletes the specified resource.
            'DELETE',

            // The PATCH method is used to apply partial modifications to a 
            // resource.
            'PATCH',

            // The CONNECT method establishes a tunnel to the server identified
            // by the target resource.
            'CONNECT',

            //The OPTIONS method is used to describe the communication options
            // for the target resource.
            'OPTIONS',

            // The TRACE method performs a message loop-back test along the
            // path to the target resource.
            'TRACE',
        ];

        $this->method = strtoupper($method);

        if (! in_array($this->method, $requestMethods)) {
            throw new InvalidArgumentException(
                sprintf(
                    'Unsupported HTTP method. It must be compatible with RFC-7231 request method, but %s provided.',
                    $method
                )
            );
        }
    }

    /**
     * Check out whether a protocol version number is supported.
     *
     * @param string $version HTTP protocol version.
     * 
     * @return void
     * 
     * @throws InvalidArgumentException
     */
    protected function assertProtocolVersion(string $version): void
    {
        if (! in_array($version, ['1.1', '2.0', '3.0'])) {
            throw new InvalidArgumentException(
                sprintf(
                    'Unsupported HTTP protocol version number. %s provided.',
                    $version
                )
            );
        }
    }
}
