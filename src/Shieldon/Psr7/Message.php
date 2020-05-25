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

use Psr\Http\Message\MessageInterface;
use Psr\Http\Message\StreamInterface;
use InvalidArgumentException;

/**
 * HTTP messages consist of requests from a client to a server and responses
 * from a server to a client.
 */
class Message implements MessageInterface
{
    /**
     * A HTTP protocol version number.
     *
     * @var string
     */
    protected $protocolVersion = '1.1';

    /**
     * An instance with the specified message body.
     *
     * @var StreamInterface
     */
    protected $body;

    /**
     * An array of mapping header information with `string => array[]` format.
     *
     * @var array
     */
    protected $headers = [];

    /**
     * {@inheritdoc}
     */
    public function getProtocolVersion(): string
    {
        return $this->protocolVersion;
    }

    /**
     * {@inheritdoc}
     */
    public function withProtocolVersion(string $version)
    {
        $clone = clone $this;
        $clone->protocolVersion = $version;

        return $clone;
    }

    /**
     * {@inheritdoc}
     */
    public function getHeaders(): array
    {
        return $this->headers;
    }

    /**
     * {@inheritdoc}
     */
    public function hasHeader(string $name): bool
    {
        $name = strtolower($name);

        return isset($this->headers[$name]);
    }

    /**
     * {@inheritdoc}
     */
    public function getHeader(string $name): array
    {
        $name = strtolower($name);

        if (isset($this->headers[$name])) {
            return $this->headers[$name];
        }

        return [];
    }

    /**
     * {@inheritdoc}
     */
    public function getHeaderLine(string $name): string
    {
        return implode(', ', $this->getHeader(strtolower($name)));
    }

    /**
     * {@inheritdoc}
     */
    public function withHeader(string $name, $value)
    {
        $this->assertHeader($name, $value);

        $name = strtolower($name);

        $clone = clone $this;
        $clone->headers[$name] = $value;

        return $clone;
    }

    /**
     * {@inheritdoc}
     */
    public function withAddedHeader(string $name, $value)
    {
        $this->assertHeader($name, $value);

        $name = strtolower($name);

        $clone = clone $this;

        if (isset($clone->headers[$name])) {
            $clone->headers[$name] = array_merge($this->headers[$name], $value);
        } else {
            $clone->headers[$name] = $value;
        }

        return $clone;
    }

    /**
     * {@inheritdoc}
     */
    public function withoutHeader(string $name)
    {
        $name = strtolower($name);

        $clone = clone $this;
        unset($clone->headers[$name]);

        return $clone;
    }

    /**
     * {@inheritdoc}
     */
    public function getBody(): StreamInterface
    {
        return $this->body;
    }

    /**
     * {@inheritdoc}
     */
    public function withBody(StreamInterface $body)
    {
        $this->assertBody($body);

        $clone = clone $this;
        $clone->body = $body;

        return $clone;
    }

    /*
    |--------------------------------------------------------------------------
    | Non PSR-7 Methods.
    |--------------------------------------------------------------------------
    */

    /**
     * Set headers to property $headers.
     *
     * @param array $headers A collection of header information.
     *
     * @return void
     */
    protected function setHeaders(array $headers): void
    {
        foreach ($headers as $name => $value) {
            assertHeader($name, $value);
        }

        $this->headers = $headers;
    }

    /**
     * Throw exception if the header is not compatible with RFC 7230.
     * 
     * @param string            $name  The header name.
     * @param string|array|null $value Check when it is an array or string.
     *
     * @return void
     * 
     * @throws InvalidArgumentException
     */
    protected function assertHeader(string $name, $value = null): void
    {
        // see https://tools.ietf.org/html/rfc7230#section-3.2.6
        // alpha  => a-zA-Z
        // digit  => 0-9
        // others => !#$%&\'*+-.^_`|~

        if (! preg_match('/^[a-zA-Z0-9!#$%&\'*+-.^_`|~]+$/', $name)) {
            throw new InvalidArgumentException(
                sprintf(
                    '"%s" is not valid header name, it must be an RFC 7230 compatible string.',
                    $name
                )
            );
        }

        if (! is_null($value)) {
            $items = is_array($value) ? $value : (is_string($value) ? [$value] : false);

            if ($items === false) {
                throw new InvalidArgumentException(
                    sprintf(
                        'The second argument only accepts string and array, but %s provied.',
                        gettype($value)
                    )
                );
            }

            if (empty($items)) {
                throw new InvalidArgumentException(
                    'A header value can not be empty.'
                );
            }

            foreach ($items as $item) {

                if (! is_scalar($item) || is_bool($item)) {
                    throw new InvalidArgumentException(
                        sprintf(
                            'The header values only accept string and number, but %s provied.',
                            gettype($value)
                        )
                    );
                }

                if (! preg_match('/^[ \t\x21-\x7E\x80-\xFF]+$/', $value)) {
                    throw new InvalidArgumentException(
                        sprintf(
                            '"%s" is not valid header value, it must be an RFC 7230 compatible string.',
                            $value
                        )
                    );
                }
            }
        }
    }

    /**
     * Throw exception when the body is not valid.
     * 
     * @param StreamInterface $body An instance of StreamInterface.
     * 
     * @return void
     * 
     * @throws InvalidArgumentException
     */
    protected function assertBody(StreamInterface $body)
    {

    }
}
