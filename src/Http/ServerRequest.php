<?php

declare(strict_types=1);

namespace Solital\Core\Http;

use Solital\Core\Http\Traits\RequestTrait;
use Solital\Core\Exceptions\InvalidArgumentException;
use Psr\Http\Message\{StreamInterface, ServerRequestInterface};

class ServerRequest implements ServerRequestInterface
{
    use RequestTrait;

    /**
     * The server environment variables.
     *
     * @var array
     */
    private array $serverParams;

    /**
     * The request cookies.
     *
     * @var array
     */
    private array $cookieParams;

    /**
     * THe request query string params.
     *
     * @var array
     */
    private array $queryParams;

    /**
     * The uplaoded files.
     *
     * @var array
     */
    private array $uploadedFiles;

    /**
     * The request body parsed  into a PHP array or object.
     *
     * @var null|array|object
     */
    private $parsedBody;

    /**
     * The request attributes.
     *
     * @var array
     */
    private array $attributes = [];

    /**
     * Create a new server request instance.
     *
     * @param string|null                                $method
     * @param null|string|\Psr\Http\Message\UriInterface $uri
     * @param string|resource|StreamInterface            $body
     * @param array                                      $serverParams
     * @param array                                      $cookieParams
     * @param array                                      $queryParams
     * @param array                                      $uploadedFiles
     * @param array                                      $headers
     * @param string                                     $protocol
     *
     * @throws \InvalidArgumentException for any invalid value.
     */
    public function __construct(
        string $method = null,
        $uri = null,
        $body = 'php://memory',
        array $serverParams = [],
        array $cookieParams = [],
        array $queryParams = [],
        array $uploadedFiles = [],
        array $headers = [],
        $protocol = '1.1'
    ) {
        $this->validateUploadedFiles($uploadedFiles);
        $this->validateProtocolVersion($protocol);
        $this->initialize($method, $uri, $body, $headers);
        $this->serverParams = $serverParams;
        $this->cookieParams = $cookieParams;
        $this->queryParams = $queryParams;
        $this->uploadedFiles = $uploadedFiles;
        $this->protocolVersion = $protocol;
    }

    /**
     * @return array
     */
    public function getServerParams(): array
    {
        return $this->serverParams;
    }

    /**
     * @return array
     */
    public function getCookieParams(): array
    {
        return $this->cookieParams;
    }

    /**
     * @param array $cookies Array of key/value pairs representing cookies.
     *
     * @return static
     */
    #[\Override]
    public function withCookieParams(array $cookies): static
    {
        return $this->cloneWithProperty('cookieParams', $cookies);
    }

    /**
     * @return array
     */
    public function getQueryParams(): array
    {
        return $this->queryParams;
    }

    /**
     * @param array $query Array of query string arguments, typically from $_GET.
     * @return static
     */
    #[\Override]
    public function withQueryParams(array $query): static
    {
        return $this->cloneWithProperty('queryParams', $query);
    }

    /**
     * @return array An array tree of UploadedFileInterface instances; an empty array MUST be returned if no data is
     *               present.
     */
    public function getUploadedFiles(): array
    {
        return $this->uploadedFiles;
    }

    /**
     * @param array $uploadedFiles An array tree of UploadedFileInterface instances.
     *
     * @return static
     * @throws \InvalidArgumentException if an invalid structure is provided.
     */
    #[\Override]
    public function withUploadedFiles(array $uploadedFiles): static
    {
        $this->validateUploadedFiles($uploadedFiles);

        return $this->cloneWithProperty('uploadedFiles', $uploadedFiles);
    }

    /**
     * @param array $uploadedFiles
     *
     * @throws InvalidArgumentException if any leaf is not an UploadedFileInterface instance.
     */
    private function validateUploadedFiles(array $uploadedFiles)
    {
        foreach ($uploadedFiles as $file) {
            if (is_array($file)) {
                $this->validateUploadedFiles($file);
                continue;
            }

            /*if (! $file instanceof UploadedFileInterface) {
                InvalidArgumentException::alertMessage(400, 
                    'Invalid uploaded files structure. ' .
                    'Each file must be an instance of Psr\Http\Message\UploadedFileInterface'
                );
            }*/
        }
    }

    /**
     * @return null|array|object The deserialized body parameters, if any. These will typically be an array or object.
     */
    public function getParsedBody()
    {
        return $this->parsedBody;
    }

    /**
     * @param null|array|object $data The deserialized body data. This will typically be in an array or object.
     *
     * @return static
     *
     * @throws \InvalidArgumentException if an unsupported argument type is provided.
     */
    #[\Override]
    public function withParsedBody($data): static
    {
        if ($data !== null && !is_object($data) && !is_array($data)) {
            throw new InvalidArgumentException("Parsed body value must be an array, object or null", 400);
        }

        return $this->cloneWithProperty('parsedBody', $data);
    }

    /**
     * @return array Attributes derived from the request.
     */
    public function getAttributes(): array
    {
        return $this->attributes;
    }

    /**
     * @see getAttributes()
     *
     * @param string $name    The attribute name.
     * @param mixed  $default Default value to return if the attribute does not exist.
     *
     * @return mixed
     */
    public function getAttribute($name, $default = null)
    {
        if (isset($this->attributes[$name])) {
            return $this->attributes[$name];
        }

        return $default;
    }

    /**
     * @see getAttributes()
     *
     * @param string $name  The attribute name.
     * @param mixed  $value The value of the attribute.
     *
     * @return static
     */
    #[\Override]
    public function withAttribute(string $name, $value): static
    {
        $clone = clone $this;
        $clone->attributes[$name] = $value;

        return $clone;
    }

    /**
     * @see getAttributes()
     *
     * @param string $name The attribute name.
     *
     * @return static
     */
    #[\Override]
    public function withoutAttribute(string $name): static
    {
        $clone = clone $this;
        unset($clone->attributes[$name]);

        return $clone;
    }
}
