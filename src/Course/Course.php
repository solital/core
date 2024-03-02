<?php

namespace Solital\Core\Course;

use Solital\Core\Exceptions\InvalidArgumentException;
use Solital\Core\Http\{Uri, Request, Response, Middleware\BaseCsrfVerifier};

use Solital\Core\Course\{
    Router,
    RouterBootManagerInterface,
    Route\RouteUrl,
    Route\RouteGroup,
    Route\RouteResource,
    Route\RouteInterface,
    Route\RouteController,
    Route\RoutePartialGroup,
    Route\GroupRouteInterface,
    Route\PartialGroupRouteInterface,
    Handlers\EventHandlerInterface
};

class Course
{
    /**
     * Default namespace added to all routes
     * 
     * @var string|null
     */
    protected static $defaultNamespace;

    /**
     * The response object
     * 
     * @var Response
     */
    protected static $response;

    /**
     * Router instance
     * 
     * @var Router
     */
    protected static $router;

    /**
     * Default basepath added to all urls
     * 
     * @var string|null
     */
    protected static $basePath;

    /**
     * Start routing
     *
     * @throws \Solital\Course\Exceptions\NotFoundHttpException
     * @throws \Solital\Http\Middleware\Exceptions\TokenMismatchException
     * @throws HttpException
     * @throws \Exception
     */
    public static function start(bool $send_console = false): void
    {
        echo static::router()->start($send_console);
    }

    /**
     * Set default namespace which will be prepended to all routes.
     *
     * @param string $defaultNamespace
     */
    public static function setDefaultNamespace(string $defaultNamespace): void
    {
        static::$defaultNamespace = $defaultNamespace;
    }

    /**
     * Set default basepath which will be prepended to all urls.
     *
     * @param string $basePath
     */
    public static function setDefaultBasepath(string $basePath): void
    {
        static::$basePath = $basePath;
    }

    /**
     * Base CSRF verifier
     *
     * @param BaseCsrfVerifier $baseCsrfVerifier
     */
    public static function csrfVerifier(BaseCsrfVerifier $baseCsrfVerifier): void
    {
        static::router()->setCsrfVerifier($baseCsrfVerifier);
    }

    /**
     * Add new event handler to the router
     *
     * @param EventHandlerInterface $eventHandler
     */
    public static function addEventHandler(EventHandlerInterface $eventHandler): void
    {
        static::router()->addEventHandler($eventHandler);
    }

    /**
     * Boot managers allows you to alter the routes before the routing occurs.
     * Perfect if you want to load pretty-urls from a file or database.
     *
     * @param RouterBootManagerInterface $bootManager
     */
    public static function addBootManager(RouterBootManagerInterface $bootManager): void
    {
        static::router()->addBootManager($bootManager);
    }

    /**
     * Redirect to when route matches.
     *
     * @param string $where
     * @param string $to
     * @param int $httpCode
     * @return RouteInterface
     */
    public static function redirect($where, $to, $httpCode = 301): RouteInterface
    {
        return static::get($where, function () use ($to, $httpCode) {
            static::response()->redirect($to, $httpCode);
        });
    }

    /**
     * Route the given url to your callback on GET request method.
     *
     * @param string $url
     * @param string|\Closure $callback
     * @param array|null $settings
     *
     * @return RouteUrl
     */
    public static function get(string $url, $callback, array $settings = null): RouteInterface
    {
        return static::match(['get'], $url, $callback, $settings);
    }

    /**
     * Route the given url to your callback on POST request method.
     *
     * @param string $url
     * @param string|\Closure $callback
     * @param array|null $settings
     * @return RouteUrl
     */
    public static function post(string $url, $callback, array $settings = null): RouteInterface
    {
        return static::match(['post'], $url, $callback, $settings);
    }

    /**
     * Route the given url to your callback on PUT request method.
     *
     * @param string $url
     * @param string|\Closure $callback
     * @param array|null $settings
     * @return RouteUrl
     */
    public static function put(string $url, $callback, array $settings = null): RouteInterface
    {
        return static::match(['put'], $url, $callback, $settings);
    }

    /**
     * Route the given url to your callback on PATCH request method.
     *
     * @param string $url
     * @param string|\Closure $callback
     * @param array|null $settings
     * @return RouteUrl
     */
    public static function patch(string $url, $callback, array $settings = null): RouteInterface
    {
        return static::match(['patch'], $url, $callback, $settings);
    }

    /**
     * Route the given url to your callback on OPTIONS request method.
     *
     * @param string $url
     * @param string|\Closure $callback
     * @param array|null $settings
     * @return RouteUrl
     */
    public static function options(string $url, $callback, array $settings = null): RouteInterface
    {
        return static::match(['options'], $url, $callback, $settings);
    }

    /**
     * Route the given url to your callback on DELETE request method.
     *
     * @param string $url
     * @param string|\Closure $callback
     * @param array|null $settings
     * @return RouteUrl
     */
    public static function delete(string $url, $callback, array $settings = null): RouteInterface
    {
        return static::match(['delete'], $url, $callback, $settings);
    }

    /**
     * Groups allows for encapsulating routes with special settings.
     *
     * @param array $settings
     * @param \Closure $callback
     * @return RouteGroup
     * @throws InvalidArgumentException
     */
    public static function group(array $settings, \Closure $callback): GroupRouteInterface
    {
        http_response_code(200);
        if (\is_callable($callback) === false) {
            throw new InvalidArgumentException('Invalid callback provided. Only functions or methods supported');
        }

        $group = new RouteGroup();
        $group->setCallback($callback);
        $group->setSettings($settings);

        static::router()->addRoute($group);

        return $group;
    }

    /**
     * Special group that has the same benefits as group but supports
     * parameters and which are only rendered when the url matches.
     *
     * @param string $url
     * @param \Closure $callback
     * @param array $settings
     * @return RoutePartialGroup
     * @throws InvalidArgumentException
     */
    public static function partialGroup(string $url, \Closure $callback, array $settings = []): PartialGroupRouteInterface
    {
        http_response_code(200);
        if (\is_callable($callback) === false) {
            throw new InvalidArgumentException('Invalid callback provided. Only functions or methods supported');
        }

        $settings['prefix'] = $url;

        $group = new RoutePartialGroup();
        $group->setSettings($settings);
        $group->setCallback($callback);

        static::router()->addRoute($group);

        return $group;
    }

    /**
     * Alias for the form method
     *
     * @param string $url
     * @param callable $callback
     * @param array|null $settings
     * @see Course::form
     * @return RouteUrl
     */
    public static function basic(string $url, $callback, array $settings = null): RouteInterface
    {
        return static::match(['get', 'post'], $url, $callback, $settings);
    }

    /**
     * This type will route the given url to your callback on the provided request methods.
     * Route the given url to your callback on POST and GET request method.
     *
     * @param string $url
     * @param string|\Closure $callback
     * @param array|null $settings
     * @see Course::form
     * @return RouteUrl
     */
    public static function form(string $url, $callback, array $settings = null): RouteInterface
    {
        return static::match(['get', 'post'], $url, $callback, $settings);
    }

    /**
     * This type will route the given url to your callback on the provided request methods.
     *
     * @param array $requestMethods
     * @param string $url
     * @param string|\Closure $callback
     * @param array|null $settings
     * @return RouteUrl|RouteInterface
     */
    public static function match(array $requestMethods, string $url, $callback, array $settings = null)
    {
        http_response_code(200);
        $route = new RouteUrl(static::$basePath . $url, $callback);
        $route->setRequestMethods($requestMethods);
        $route->setControllerName($callback);
        $route = static::addDefaultNamespace($route);

        if ($settings !== null) {
            $route->setSettings($settings);
        }

        if (!isset($_SERVER["REMOTE_ADDR"])) {
            $_SERVER["REQUEST_URI"] = $url;
        }

        return static::router()->addRoute($route);
    }

    /**
     * This type will route the given url to your callback and allow any type of request method
     *
     * @param string $url
     * @param string|\Closure $callback
     * @param array|null $settings
     * @return RouteUrl|RouteInterface
     */
    public static function all(string $url, $callback, array $settings = null)
    {
        http_response_code(200);
        $route = new RouteUrl(static::$basePath . $url, $callback);
        $route = static::addDefaultNamespace($route);

        if ($settings !== null) {
            $route->setSettings($settings);
        }

        return static::router()->addRoute($route);
    }

    /**
     * This route will route request from the given url to the controller.
     *
     * @param string $url
     * @param string $controller
     * @param array|null $settings
     * @return RouteController|RouteInterface
     */
    public static function controller(string $url, $controller, array $settings = null)
    {
        http_response_code(200);
        $route = new RouteController($url, $controller);
        $route = static::addDefaultNamespace($route);

        if ($settings !== null) {
            $route->setSettings($settings);
        }

        return static::router()->addRoute($route);
    }

    /**
     * This type will route all REST-supported requests to different methods in the provided controller.
     *
     * @param string $url
     * @param string $controller
     * @param array|null $settings
     * @return RouteResource|RouteInterface
     */
    public static function resource(string $url, $controller, array $settings = null)
    {
        http_response_code(200);
        $route = new RouteResource($url, $controller);
        $route = static::addDefaultNamespace($route);

        if ($settings !== null) {
            $route->setSettings($settings);
        }

        return static::router()->addRoute($route);
    }

    /**
     * @param bool $redirect
     * @param string $url
     */
    public static function error(bool $redirect, string $url)
    {
        if ($redirect == true) {
            Router::setUrlRedirect($url, $redirect);
        }
    }

    /**
     * Get url for a route by using either name/alias, class or method name.
     *
     * The name parameter supports the following values:
     * - Route name
     * - Controller/resource name (with or without method)
     * - Controller class name
     *
     * When searching for controller/resource by name, you can use this syntax "route.name@method".
     * You can also use the same syntax when searching for a specific controller-class "MyController@home".
     * If no arguments is specified, it will return the url for the current loaded route.
     *
     * @param string|null $name
     * @param string|array|null $parameters
     * @param array|null $getParams
     * @return Uri
     */
    public static function getUri(?string $name = null, $parameters = null, ?array $getParams = null): Uri
    {
        try {
            return static::router()->getUri($name, $parameters, $getParams);
        } catch (\Exception $e) {
            try {
                return new Uri('/');
            } catch (\Exception $e) {
                echo $e->getMessage();
            }
        }

        // This will never happen...
        //return null;
    }

    /**
     * Get the request
     *
     * @return Request
     */
    public static function request(): Request
    {
        return static::router()->getRequest();
    }

    /**
     * Get the response object
     *
     * @return Response
     */
    public static function response(): Response
    {
        if (static::$response === null) {
            static::$response = new Response(static::request(), 'php://memory', 200);
        }

        return static::$response;
    }

    /**
     * Returns the router instance
     *
     * @return Router
     */
    public static function router(): Router
    {
        if (static::$router === null) {
            static::$router = new Router();
        }

        return static::$router;
    }

    /**
     * Prepends the default namespace to all new routes added.
     *
     * @param RouteInterface $route
     * @return RouteInterface
     */
    public static function addDefaultNamespace(RouteInterface $route): RouteInterface
    {
        if (static::$defaultNamespace !== null) {

            $callback = $route->getCallback();

            if (empty($callback)) {
                throw new \Exception("Callback not found", 404);
            }

            /* Only add default namespace on relative callbacks */
            if ($callback === null || (\is_string($callback) === true && $callback[0] !== '\\')) {

                $namespace = static::$defaultNamespace;

                $currentNamespace = $route->getNamespace();

                if ($currentNamespace !== null) {
                    $namespace .= '\\' . $currentNamespace;
                }

                $route->setDefaultNamespace($namespace);
            }
        }

        return $route;
    }

    /**
     * Get default namespace
     * 
     * @return string|null
     */
    public static function getDefaultNamespace(): ?string
    {
        return static::$defaultNamespace;
    }
}
