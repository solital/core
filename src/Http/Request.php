<?php

declare(strict_types=1);

namespace Solital\Core\Http;

use Psr\Http\Message\RequestInterface;
use Solital\Core\Resource\Session;
use Solital\Core\Http\{Uri, Input\InputHandler, Traits\RequestTrait};
use Solital\Core\Course\{Course, Route\RouteUrl, Route\LoadableRouteInterface};

class Request implements RequestInterface
{
    use RequestTrait;

    /**
     * Additional data
     *
     * @var array
     */
    private array $data = [];

    /**
     * Server headers
     * 
     * @var array
     */
    private array $headers = [];

    /**
     * Request host
     * 
     * @var string
     */
    protected ?string $host;

    /**
     * Current request url
     * 
     * @var Uri
     */
    protected Uri $url;

    /**
     * Current request url
     * 
     * @var string
     */
    protected string $scheme;

    /**
     * Input handler
     * 
     * @var InputHandler
     */
    protected InputHandler $inputHandler;

    /**
     * Defines if request has pending rewrite
     * 
     * @var bool
     */
    protected bool $hasPendingRewrite = false;

    /**
     * @var LoadableRouteInterface|null
     */
    protected ?LoadableRouteInterface $rewriteRoute = null;

    /**
     * Rewrite url
     * 
     * @var string|null
     */
    protected ?string $rewriteUrl = null;

    /**
     * @var array
     */
    protected array $loadedRoutes = [];

    /**
     * @var string
     */
    private string $server;

    /**
     * List of request body parsers (e.g., url-encoded, JSON, XML, multipart)
     *
     * @var array
     */
    protected array $bodyParsers = [];

    /**
     * Request constructor.
     */
    public function __construct(string $method, $uri, $body = 'php://memory', array $headers = [])
    {
        $this->headers = $_SERVER;
        $this->initialize($method, $uri, $body, $headers);

        foreach ($_SERVER as $key => $value) {
            $this->headers[\strtolower($key)] = $value;
            $this->headers[\strtolower(str_replace('_', '-', $key))] = $value;
        }

        if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') {
            $this->scheme = 'https://';
        } else {
            $this->scheme = 'http://';
        }

        $this->setHost($this->scheme . $this->getHeader('http-host'));

        // Check if special IIS header exist, otherwise use default.
        if ($this->getHeader('unencoded-url', $this->getHeader('request-uri'))) {
            $this->setUrl(new Uri($this->getFirstHeader(['unencoded-url', 'request-uri'])));
        }

        $method_server = $this->getHeader('request-method');
        $this->method = strtolower($method_server);
        $this->inputHandler = new InputHandler($this);
        $this->method = strtolower($this->inputHandler->value('_method', $method_server));
    }

    /**
     * @return bool
     */
    public function isSecure(): bool
    {
        return $this->getHeader('http-x-forwarded-proto') === 'https' || $this->getHeader('https') !== null || $this->getHeader('server-port') === 443;
    }

    /**
     * @return Uri
     */
    public function getUri(): Uri
    {
        return $this->url;
    }

    /**
     * @param Uri $url
     */
    public function setUrl(Uri $url): void
    {
        $this->url = $url;

        if ($this->url->getHost() === null) {
            if ($this->url->getScheme() !== null) {
                $this->url->setHost((string)$this->getHost());
            }

            $this->url->setHost((string)$this->getHost());
        }
    }

    /**
     * Copy url object
     *
     * @return Uri
     */
    public function getUrlCopy(): Uri
    {
        return clone $this->url;
    }

    /**
     * @return string|null
     */
    public function getHost(): ?string
    {
        return $this->host;
    }

    /**
     * @return string|null
     */
    public function getMethod(): ?string
    {
        return $this->method;
    }

    /**
     * Get http basic auth user
     * @return string|null
     */
    public function getUser(): ?string
    {
        return $this->getHeader('php-auth-user');
    }

    /**
     * Get http basic auth password
     * @return string|null
     */
    public function getPassword(): ?string
    {
        return $this->getHeader('php-auth-pw');
    }

    /**
     * Get all headers
     * @return array
     */
    public function getHeaders(): array
    {
        return $this->headers;
    }

    /**
     * Get id address
     * If $safe is false, this function will detect Proxys. But the user can edit this header to whatever he wants!
     * https://stackoverflow.com/questions/3003145/how-to-get-the-client-ip-address-in-php#comment-25086804
     * @param bool $safeMode When enabled, only safe non-spoofable headers will be returned. Note this can cause issues when using proxy.
     * @return string|null
     */
    public function getIp(bool $safeMode = false): ?string
    {
        $headers = ['remote-addr'];
        if ($safeMode === false) {
            $headers = array_merge($headers, [
                'http-cf-connecting-ip',
                'http-client-ip',
                'http-x-forwarded-for',
            ]);
        }

        return $this->getFirstHeader($headers);
    }

    /**
     * Will try to find first header from list of headers.
     *
     * @param array $headers
     * @param mixed|null $defaultValue
     * @return mixed|null
     */
    public function getFirstHeader(array $headers, $defaultValue = null)
    {
        foreach ($headers as $header) {
            $header = $this->getHeader($header);
            if ($header !== null) {
                return $header;
            }
        }

        return $defaultValue;
    }

    /**
     * Get remote address/ip
     *
     * @alias static::getIp
     * @return string|null
     */
    public function getRemoteAddr(): ?string
    {
        return $this->getIp();
    }

    /**
     * Get referer
     * @return string|null
     */
    public function getReferer(): ?string
    {
        return $this->getHeader('http-referer');
    }

    /**
     * Get user agent
     * @return string|null
     */
    public function getUserAgent(): ?string
    {
        return $this->getHeader('http-user-agent');
    }

    /**
     * Get header value by name
     *
     * @param string $name
     * @param string|null $defaultValue
     *
     * @return string|null
     */
    public function getHeader($name, $defaultValue = null): ?string
    {
        return $this->headers[$name] ?? $defaultValue;
    }

    /**
     * Get input class
     * @return InputHandler
     */
    public function getInputHandler(): InputHandler
    {
        return $this->inputHandler;
    }

    /**
     * Is format accepted
     *
     * @param string $format
     *
     * @return bool
     */
    public function isFormatAccepted($format): bool
    {
        return ($this->getHeader('http-accept') !== null && stripos($this->getHeader('http-accept'), $format) !== false);
    }

    /**
     * Returns true if the request is made through Ajax
     *
     * @return bool
     */
    public function isAjax(): bool
    {
        return (strtolower($this->getHeader('http-x-requested-with')) === 'xmlhttprequest');
    }

    /**
     * Get accept formats
     * @return array
     */
    public function getAcceptFormats(): array
    {
        return explode(',', $this->getHeader('http-accept'));
    }

    /**
     * @param string|null $host
     */
    public function setHost(?string $host): void
    {
        $this->host = $host;
    }

    /**
     * @param string $method
     */
    public function setMethod(string $method): void
    {
        $this->method = strtolower($method);
    }

    /**
     * Set rewrite route
     *
     * @param LoadableRouteInterface $route
     * @return static
     */
    public function setRewriteRoute(LoadableRouteInterface $route): self
    {
        $this->hasPendingRewrite = true;
        $this->rewriteRoute = Course::addDefaultNamespace($route);

        return $this;
    }

    /**
     * Get rewrite route
     *
     * @return LoadableRouteInterface|null
     */
    public function getRewriteRoute(): ?LoadableRouteInterface
    {
        return $this->rewriteRoute;
    }

    /**
     * Get rewrite url
     *
     * @return string|null
     */
    public function getRewriteUrl(): ?string
    {
        return $this->rewriteUrl;
    }

    /**
     * Set rewrite url
     *
     * @param string $rewriteUrl
     * @return static
     */
    public function setRewriteUrl(string $rewriteUrl): self
    {
        $this->hasPendingRewrite = true;
        $this->rewriteUrl = rtrim($rewriteUrl, '/') . '/';

        return $this;
    }

    /**
     * Set rewrite callback
     * @param string|\Closure $callback
     * @return static
     */
    public function setRewriteCallback($callback): self
    {
        $this->hasPendingRewrite = true;

        return $this->setRewriteRoute(new RouteUrl($this->getUri()->getPath(), $callback));
    }

    /**
     * Get loaded route
     * @return LoadableRouteInterface|null
     */
    public function getLoadedRoute(): ?LoadableRouteInterface
    {
        return (\count($this->loadedRoutes) > 0) ? end($this->loadedRoutes) : null;
    }

    /**
     * Get all loaded routes
     *
     * @return array
     */
    public function getLoadedRoutes(): array
    {
        return $this->loadedRoutes;
    }

    /**
     * Set loaded routes
     *
     * @param array $routes
     * @return static
     */
    public function setLoadedRoutes(array $routes): self
    {
        $this->loadedRoutes = $routes;

        return $this;
    }

    /**
     * Added loaded route
     *
     * @param LoadableRouteInterface $route
     * @return static
     */
    public function addLoadedRoute(LoadableRouteInterface $route): self
    {
        $this->loadedRoutes[] = $route;

        return $this;
    }

    /**
     * Returns true if the request contains a rewrite
     *
     * @return bool
     */
    public function hasPendingRewrite(): bool
    {
        return $this->hasPendingRewrite;
    }

    /**
     * Defines if the current request contains a rewrite.
     *
     * @param bool $boolean
     * @return Request
     */
    public function setHasPendingRewrite(bool $boolean): self
    {
        $this->hasPendingRewrite = $boolean;

        return $this;
    }

    /**
     * @param string $key
     * @param int $limit = 5
     * @param int $seconds = 60
     * 
     * @return bool
     */
    public static function limit(string $key, int $limit = 5, int $seconds = 60): bool
    {
        if (Session::has($key) && $_SESSION[$key]['time'] >= time() && $_SESSION[$key]['requests'] < $limit) {
            Session::set($key, [
                'time' => time() + $seconds,
                'requests' => $_SESSION[$key]['requests'] + 1
            ]);

            return false;
        }

        if (Session::has($key) && $_SESSION[$key]['time'] >= time() && $_SESSION[$key]['requests'] >= $limit) {
            return true;
        }

        Session::set($key, [
            'time' => time() + $seconds,
            'requests' => 1
        ]);

        return false;
    }

    /**
     * request_repeat
     *
     * @param string $key
     * @param string $value
     * 
     * @return bool
     */
    public static function repeat(string $key, string $value): bool
    {
        if (Session::has($key) && Session::get($key) == $value) {
            return true;
        }

        Session::set($key, $value);
        return false;
    }

    /**
     * @param string $name
     * 
     * @return bool
     */
    public function __isset($name)
    {
        return array_key_exists($name, $this->data) === true;
    }

    /**
     * @param string $name
     * @param null $value
     */
    public function __set($name, $value = null)
    {
        $this->data[$name] = $value;
    }

    /**
     * @param string $name
     */
    public function __get($name)
    {
        return $this->data[$name] ?? null;
    }
}
