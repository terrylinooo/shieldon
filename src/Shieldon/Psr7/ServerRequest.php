<?php 

declare(strict_types=1);

/*
 * This file is part of the Shieldon package.
 *
 * (c) Terry L. <contact@terryl.in>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Shieldon\Psr7;

use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\UploadFile;

/*
 * Representation of an incoming, server-side HTTP request.
 */
class ServerRequest extends Request implements ServerRequestInterface
{
    /**
     * Typically derived from PHP's $_SERVER superglobal.
     * 
     * @var array
     */
    protected $serverParams;

    /**
     * Typically derived from PHP's $_COOKIE superglobal.
     * 
     * @var array
     */
    protected $cookieParams;

    /**
     * Typically derived from PHP's $_POST superglobal.
     * 
     * @var array
     */
    protected $parsedBody;

    /**
     * Typically derived from PHP's $_GET superglobal.
     * 
     * @var array
     */
    protected $queryParams;

    /**
     * Typically derived from PHP's $_FILES superglobal.
     * A collection of uploadFileInterface instances.
     * 
     * @var array
     */
    protected $uploadedFiles;

    /**
     * ServerRequest constructor.
     *
     * @param string                 $method       Request HTTP method
     * @param UriInterface|string    $uri          Request URI object URI or URL
     * @param StreamInterface|string $body         Request body
     * @param array                  $headers      Request headers
     * @param string                 $version      Request protocol version
     * @param array                  $serverParams Typically $_SERVER superglobal
     * @param array                  $cookieParams Typically $_COOKIE superglobal
     * @param array                  $postParams   Typically $_POST superglobal
     * @param array                  $getParams    Typically $_GET superglobal
     * @param array                  $filesParams  Typically $_FILES superglobal
     */
    public function __construct(
        string $method       = 'GET',
               $uri          = ''   ,
               $body         = ''   ,
        array  $headers      = []   ,
        string $version      = '1.1',
        array  $serverParams = []   ,
        array  $cookieParams = []   ,
        array  $postParams   = []   ,
        array  $getParams    = []   ,
        array  $filesParams  = []
    ) {
        parent::__construct($method, $uri, $body, $headers, $version);

        $this->serverParams = $serverParams;
        $this->cookieParams = $cookieParams;
        $this->parsedBody   = $postParams;
        $this->queryParams  = $getParams;

        $this->uploadedFiles = UploadFile::specParser($filesParams);
    }

    /**
     * {@inheritdoc}
     */
    public function getServerParams(): array
    {
        return $this->serverParams;
    }

    /**
     * {@inheritdoc}
     */
    public function getCookieParams(): array
    {
        return $this->cookieParams;
    }

    /**
     * {@inheritdoc}
     */
    public function withCookieParams(array $cookies)
    {
        $clone = clone $this;
        $clone->cookieParams = $cookies;

        return $clone;
    }

    /**
     * {@inheritdoc}
     */
    public function getQueryParams(): array
    {
        return $this->queryParams;
    }

    /**
     * {@inheritdoc}
     */
    public function withQueryParams(array $query)
    {
        $clone = clone $this;
        $clone->queryParams = $query;

        return $clone;
    }

    /**
     * Retrieve normalized file upload data.
     *
     * This method returns upload metadata in a normalized tree, with each leaf
     * an instance of Psr\Http\Message\UploadedFileInterface.
     *
     * These values MAY be prepared from $_FILES or the message body during
     * instantiation, or MAY be injected via withUploadedFiles().
     *
     * @return array An array tree of UploadedFileInterface instances; an empty
     *     array MUST be returned if no data is present.
     */
    public function getUploadedFiles()
    {

    }

    /**
     * Create a new instance with the specified uploaded files.
     *
     * This method MUST be implemented in such a way as to retain the
     * immutability of the message, and MUST return an instance that has the
     * updated body parameters.
     *
     * @param array $uploadedFiles An array tree of UploadedFileInterface instances.
     * @return static
     * @throws \InvalidArgumentException if an invalid structure is provided.
     */
    public function withUploadedFiles(array $uploadedFiles)
    {

    }

    /**
     * Retrieve any parameters provided in the request body.
     *
     * If the request Content-Type is either application/x-www-form-urlencoded
     * or multipart/form-data, and the request method is POST, this method MUST
     * return the contents of $_POST.
     *
     * Otherwise, this method may return any results of deserializing
     * the request body content; as parsing returns structured content, the
     * potential types MUST be arrays or objects only. A null value indicates
     * the absence of body content.
     *
     * @return null|array|object The deserialized body parameters, if any.
     *     These will typically be an array or object.
     */
    public function getParsedBody()
    {
        return $this->parsedBody;
    }

    /**
     * Return an instance with the specified body parameters.
     *
     * These MAY be injected during instantiation.
     *
     * If the request Content-Type is either application/x-www-form-urlencoded
     * or multipart/form-data, and the request method is POST, use this method
     * ONLY to inject the contents of $_POST.
     *
     * The data IS NOT REQUIRED to come from $_POST, but MUST be the results of
     * deserializing the request body content. Deserialization/parsing returns
     * structured data, and, as such, this method ONLY accepts arrays or objects,
     * or a null value if nothing was available to parse.
     *
     * As an example, if content negotiation determines that the request data
     * is a JSON payload, this method could be used to create a request
     * instance with the deserialized parameters.
     *
     * This method MUST be implemented in such a way as to retain the
     * immutability of the message, and MUST return an instance that has the
     * updated body parameters.
     *
     * @param null|array|object $data The deserialized body data. This will
     *     typically be in an array or object.
     * @return static
     * @throws \InvalidArgumentException if an unsupported argument type is
     *     provided.
     */
    public function withParsedBody($data)
    {

    }

    /**
     * Retrieve attributes derived from the request.
     *
     * The request "attributes" may be used to allow injection of any
     * parameters derived from the request: e.g., the results of path
     * match operations; the results of decrypting cookies; the results of
     * deserializing non-form-encoded message bodies; etc. Attributes
     * will be application and request specific, and CAN be mutable.
     *
     * @return mixed[] Attributes derived from the request.
     */
    public function getAttributes()
    {

    }

    /**
     * Retrieve a single derived request attribute.
     *
     * Retrieves a single derived request attribute as described in
     * getAttributes(). If the attribute has not been previously set, returns
     * the default value as provided.
     *
     * This method obviates the need for a hasAttribute() method, as it allows
     * specifying a default value to return if the attribute is not found.
     *
     * @see getAttributes()
     * @param string $name The attribute name.
     * @param mixed $default Default value to return if the attribute does not exist.
     * @return mixed
     */
    public function getAttribute($name, $default = null)
    {

    }

    /**
     * Return an instance with the specified derived request attribute.
     *
     * This method allows setting a single derived request attribute as
     * described in getAttributes().
     *
     * This method MUST be implemented in such a way as to retain the
     * immutability of the message, and MUST return an instance that has the
     * updated attribute.
     *
     * @see getAttributes()
     * @param string $name The attribute name.
     * @param mixed $value The value of the attribute.
     * @return static
     */
    public function withAttribute($name, $value)
    {

    }

    /**
    

     */
    public function withoutAttribute($name) 
    {

    }

    /*
    |--------------------------------------------------------------------------
    | Non-PSR-7 Methods.
    |--------------------------------------------------------------------------
    */

    public function createFromGlobal()
    {
        
    }

 /*
            An example might be:

            array (size=2)
                'avatar' => 
                    array (size=5)
                        'name' => string '96748198_2682544731965506_2382408600925503488_n.jpg' (length=51)
                        'type' => string 'image/jpeg' (length=10)
                        'tmp_name' => string 'C:\xampp\tmp\php343C.tmp' (length=24)
                        'error' => int 0
                        'size' => int 125100
                'tool' => 
                    array (size=5)
                        'name' => 
                            array (size=2)
                            'ok' => string '100762132_549865972343521_985861218256289792_o.jpg' (length=50)
                            'pk' => string '97436269_3088634491223163_5026246187506728960_o.jpg' (length=51)
                        'type' => 
                            array (size=2)
                            'ok' => string 'image/jpeg' (length=10)
                            'pk' => string 'image/jpeg' (length=10)
                        'tmp_name' => 
                            array (size=2)
                            'ok' => string 'C:\xampp\tmp\php344D.tmp' (length=24)
                            'pk' => string 'C:\xampp\tmp\php344E.tmp' (length=24)
                        'error' => 
                            array (size=2)
                            'ok' => int 0
                            'pk' => int 0
                        'size' => 
                            array (size=2)
                            'ok' => int 137976
                            'pk' => int 136956

        */
    public static function uploadedFileFormater()
    {
        

    }
}
