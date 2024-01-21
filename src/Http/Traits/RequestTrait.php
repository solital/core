<?php

declare(strict_types=1);

namespace Solital\Core\Http\Traits;

use Psr\Http\Message\UriInterface;
use Psr\Http\Message\RequestInterface;
use Solital\Core\Http\Uri;

trait RequestTrait
{
    use MessageTrait;

    /**
     * The HTTP request-target
     *
     * @var string
     */
    private $requestTarget;

    /**
     * THe HTTP request method.
     *
     * @var string
     */
    protected string $method = '';

    /**
     * Available valid HTTP methods.
     *
     * @var array
     */
    private static $validMethods = [
        'HEAD',
        'GET',
        'POST',
        'PUT',
        'PATCH',
        'DELETE',
        'PURGE',
        'OPTIONS',
        'TRACE',
        'CONNECT',
    ];

    /**
     * @var \Psr\Http\Message\UriInterface
     */
    private UriInterface $uri_interface;

    /**
     * @param string|null                                       $method
     * @param string|null|\Psr\Http\Message\UriInterface        $uri
     * @param string|resource|\Psr\Http\Message\StreamInterface $body
     * @param array                                             $headers
     *
     * @throws \InvalidArgumentException for any invalid value.
     */
    private function initialize(
        string $method = null,
        mixed $uri = null,
        mixed $body = 'php://memory',
        array $headers = []
    ) {
        $this->method = $this->sanitizeMethod($method);
        $this->setUriInstance($uri);
        $this->setStreamInstance($body);
        $this->setHeaders($headers);
    }

    /**
     * @return string
     */
    public function getRequestTarget(): string
    {
        if ($this->requestTarget) {
            return $this->requestTarget;
        }

        $path = $this->uri_interface->getPath();

        if (empty($path)) {
            return '/';
        }

        if ($this->uri_interface->getQuery()) {
            $path .= '?' . $this->uri_interface->getQuery();
        }

        return $path;
    }

    /**
     * @param mixed $requestTarget
     *
     * @return static
     *
     * @throws \InvalidArgumentException for invalid request targets.
     */
    public function withRequestTarget($requestTarget): RequestInterface
    {
        if (preg_match('#\s#', $requestTarget)) {
            throw new \InvalidArgumentException("Invalid request target provided. Must be a string without whitespace", 400);
        }

        $clone = clone $this;
        $clone->requestTarget = $requestTarget;

        return $clone;
    }

    /**
     * @return string Returns the request method.
     */
    public function getMethod(): string
    {
        return $this->method;
    }

    /**
     * @param string $method Case-sensitive method.
     *
     * @return static
     *
     * @throws \InvalidArgumentException for invalid HTTP methods.
     */
    public function withMethod(string $method): RequestInterface
    {
        $method = $this->sanitizeMethod($method);

        $clone = clone $this;
        $clone->method = $method;

        return $clone;
    }

    /**
     * @param mixed $method
     *
     * @return string
     *
     * @throws \InvalidArgumentException for invalid HTTP methods.
     */
    private function sanitizeMethod($method): string
    {
        if ($method === null) {
            return '';
        }

        if (!is_string($method)) {
            throw new \InvalidArgumentException("Invalid HTTP method. Must be a string, received " . (is_object($method) ? get_class($method) : gettype($method)));
        }

        $method = strtoupper($method);

        if (!in_array($method, self::$validMethods, true)) {
            throw new \InvalidArgumentException("Invalid HTTP method. Must be " . implode(', ', self::$validMethods));
        }

        return $method;
    }

    /**
     * Set a new uri instance.
     *
     * @param string|null|\Psr\Http\Message\UriInterface $uri
     *
     * @throws \InvalidArgumentException When the provided URI is invalid.
     */
    private function setUriInstance($uri)
    {
        if ($uri instanceof UriInterface) {
            $this->uri_interface = $uri;
        } elseif (is_string($uri)) {
            $this->uri_interface = new Uri($uri);
        } elseif ($uri === null) {
            $this->uri_interface = new Uri;
        } else {
            throw new \InvalidArgumentException("Invalid URI provided. Must be null, a string or a Psr\Http\Message\UriInterface instance");
        }
    }

    /**
     * @return UriInterface Returns a UriInterface instance representing the URI of the request.
     */
    public function getUri(): UriInterface
    {
        return $this->uri_interface;
    }

    /**
     * @param UriInterface $uri          New request URI to use.
     * @param bool         $preserveHost Preserve the original state of the Host header.
     *
     * @return static
     */
    public function withUri(UriInterface $uri, bool $preserveHost = false): RequestInterface
    {
        $clone = clone $this;
        $clone->uri = $uri;

        $host = $uri->getHost();

        if ($uri->getPort()) {
            $host .= ':' . $uri->getPort();
        }

        // @todo: I'm not very happy with this solution right now :(
        if ($preserveHost) {
            if ($host !== '' && (!$this->hasHeader('Host') || $this->getHeaderLine('Host') === '')) {
                $clone->headerNames['host'] = 'Host';
                $clone->headers['Host'] = [$host];
            }
        } elseif ($host !== '') {
            $clone->headerNames['host'] = 'Host';
            $clone->headers['Host'] = [$host];
        }

        return $clone;
    }
}
