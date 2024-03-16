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
    protected ?Uri $uri = null;

    /**
     * Current request url
     * 
     * @var Uri
     */
    protected ?Uri $uri_clone = null;

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
     * 
     * @param string $method
     * @param mixed $uri
     * @param mixed $body
     * @param array $headers
     */
    public function __construct(string $method, mixed $uri, mixed $body = 'php://memory', array $headers = [])
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

        //$header = $this->isConsole('http-host');

        $this->setHost($this->scheme . $this->getHeaderValue('http-host'));

        $method_server = $this->getHeaderValue('request-method');
        $this->method = strtolower($method_server);
        $this->inputHandler = new InputHandler($this);
        $this->method = strtolower($this->inputHandler->value('_method', $method_server));
    }

    /* public function isConsole(string $name, bool $value = false)
    {
        if ($value == true) {
            return $this->getHeader($name);
        } else {
            return $this->getHeaderValue($name);
        }
    } */

    /**
     * @return bool
     */
    public function isSecure(): bool
    {
        return $this->getHeaderValue('http-x-forwarded-proto') === 'https' || $this->getHeaderValue('https') !== null || $this->getHeaderValue('server-port') === 443;
    }

    /**
     * @return Uri
     */
    public function getUri(): Uri
    {
        if ($this->uri == null) {
            $this->setUrl(new Uri($this->getHeaderValue('request-uri')));
        }

        return $this->uri;
    }

    /**
     * @param Uri $url
     */
    public function setUrl(Uri $url): void
    {
        $this->uri = $url;

        if ($this->uri->getHost() === null) {
            if ($this->uri->getScheme() !== null) {
                $this->uri->setHost((string)$this->getHost());
            }

            $this->uri->setHost((string)$this->getHost());
        }

        $this->uri_clone = clone $this->uri;
    }

    /**
     * Copy url object
     *
     * @return Uri
     */
    public function getUrlCopy(): Uri
    {
        if (is_null($this->uri_clone)) {
            $this->getUri();
        }

        return $this->uri_clone;
    }

    /**
     * @return string|null
     */
    public function getHost(): ?string
    {
        return $this->host;
    }

    /**
     * @return string
     */
    public function getMethod(): string
    {
        return $this->method;
    }

    /**
     * Get http basic auth user
     * @return string|null
     */
    public function getUser(): ?string
    {
        return $this->getHeaderValue('php-auth-user');
    }

    /**
     * Get http basic auth password
     * @return string|null
     */
    public function getPassword(): ?string
    {
        return $this->getHeaderValue('php-auth-pw');
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
            $header = $this->getHeaderValue($header);
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
        return $this->getHeaderValue('http-referer');
    }

    /**
     * Get user agent
     * @return string|null
     */
    public function getUserAgent(): ?string
    {
        return $this->getHeaderValue('http-user-agent');
    }

    /**
     * Get header array
     *
     * @param string $name
     *
     * @return array
     */
    public function getHeader(string $name): array
    {
        if ($this->headers == null || array_key_exists($name, $this->headers) == false) {
            $res[$name] = [];
        } else {
            $res[$name] = $this->headers[$name];
        }

        return $res;
    }

    /**
     * Get header value by name
     *
     * @param string $name
     *
     * @return string|null
     */
    public function getHeaderValue($name): ?string
    {
        $value = $this->getHeader($name);

        if (empty($value[$name])) {
            return null;
        } else {
            return $value[$name];
        }
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
    /* public function isFormatAccepted($format): bool
    {
        return ($this->getHeaderValue('http-accept') !== null && stripos($this->getHeaderValue('http-accept'), $format) !== false);
    } */

    /**
     * Returns true if the request is made through Ajax
     *
     * @return bool
     */
    public function isAjax(): bool
    {
        return (strtolower($this->getHeaderValue('http-x-requested-with')) === 'xmlhttprequest');
    }

    /**
     * Get accept formats
     * @return array
     */
    public function getAcceptFormats(): array
    {
        return explode(',', $this->getHeaderValue('http-accept'));
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
