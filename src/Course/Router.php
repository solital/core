<?php

namespace Solital\Core\Course;

use Psr\Log\LogLevel;
use Solital\Core\Http\{Uri, Request, Middleware\BaseCsrfVerifier};
use Solital\Core\Course\Handlers\{EventHandler, EventHandlerInterface};
use Solital\Core\Course\ClassLoader\{ClassLoader, ClassLoaderInterface};
use Solital\Core\Exceptions\{InvalidArgumentException, ExceptionHandlerInterface, RuntimeException};

use Solital\Core\Course\Route\{
    RouteInterface,
    GroupRouteInterface,
    LoadableRouteInterface,
    ControllerRouteInterface,
    PartialGroupRouteInterface
};

use Solital\Core\Logger\Logger;
use Solital\Core\Logger\Handler\LogfileHandler;

class Router
{

    /**
     * Current request
     * 
     * @var Request
     */
    protected $request;

    /**
     * Defines if a route is currently being processed.
     * 
     * @var bool
     */
    protected $isProcessingRoute;

    /**
     * All added routes
     * 
     * @var array
     */
    protected array $routes = [];

    /**
     * List of processed routes
     * 
     * @var array
     */
    protected array $processedRoutes = [];

    /**
     * Stack of routes used to keep track of sub-routes added
     * when a route is being processed.
     * 
     * @var array
     */
    protected array $routeStack = [];

    /**
     * List of added bootmanagers
     * 
     * @var array
     */
    protected array $bootManagers = [];

    /**
     * Csrf verifier class
     * 
     * @var BaseCsrfVerifier
     */
    protected $csrfVerifier;

    /**
     * Get exception handlers
     * 
     * @var array
     */
    protected array $exceptionHandlers = [];

    /**
     * List of loaded exception that has been loaded.
     * Used to ensure that exception-handlers aren't loaded twice when rewriting route.
     *
     * @var array
     */
    protected array $loadedExceptionHandlers = [];

    /**
     * Contains any registered event-handler.
     * 
     * @var array
     */
    protected array $eventHandlers = [];

    /**
     * Class loader instance
     * 
     * @var ClassLoader
     */
    protected $classLoader;

    /**
     * @var array
     */
    private $url;

    /**
     * @var bool
     */
    private bool $send_console;

    /**
     * @var string
     */
    private static string $url_redirect;

    /**
     * @var bool
     */
    private static bool $redirect = false;

    /**
     * @var Logger
     */
    private Logger $logger;

    /**
     * Router constructor.
     */
    public function __construct()
    {
        $this->logger = new Logger('router');
        $this->reset();
    }

    /**
     * Resets the router by reloading request and clearing all routes and data.
     */
    public function reset(): void
    {
        $this->isProcessingRoute = false;

        try {
            $this->request = new Request($_SERVER["REQUEST_METHOD"], $_SERVER["REQUEST_URI"], 'php://memory');
        } catch (\Exception $e) {
            throw new \Exception("Invalid request-uri url: " . $e->getMessage());
        }

        $this->routes = [];
        $this->bootManagers = [];
        $this->routeStack = [];
        $this->processedRoutes = [];
        $this->exceptionHandlers = [];
        $this->loadedExceptionHandlers = [];
        $this->eventHandlers = [];
        $this->csrfVerifier = null;
        $this->classLoader = new ClassLoader();
    }

    /**
     * Add route
     * 
     * @param RouteInterface $route
     * @return RouteInterface
     */
    public function addRoute(RouteInterface $route): RouteInterface
    {
        $this->fireEvents(EventHandler::EVENT_ADD_ROUTE, [
            'route' => $route,
        ]);

        /*
         * If a route is currently being processed, that means that the route being added are rendered from the parent
         * routes callback, so we add them to the stack instead.
         */
        if ($this->isProcessingRoute === true) {
            $this->routeStack[] = $route;
        } else {
            $this->routes[] = $route;
        }

        return $route;
    }

    /**
     * Render and process any new routes added.
     *
     * @param RouteInterface $route
     * @throws NotFoundHttpException
     */
    protected function renderAndProcess(RouteInterface $route): void
    {
        $this->isProcessingRoute = true;

        $route->renderRoute($this->request, $this);
        $this->isProcessingRoute = false;

        if (\count($this->routeStack) !== 0) {

            /* Pop and grab the routes added when executing group callback earlier */
            $stack = $this->routeStack;
            $this->routeStack = [];

            /* Route any routes added to the stack */
            $this->processRoutes($stack, ($route instanceof GroupRouteInterface) ? $route : null);
        }
    }

    /**
     * Process added routes.
     *
     * @param array $routes
     * @param GroupRouteInterface|null $group
     * @throws NotFoundHttpException
     */
    protected function processRoutes(array $routes, ?GroupRouteInterface $group = null): void
    {
        // Loop through each route-request
        $exceptionHandlers = [];

        // Stop processing routes if no valid route is found.
        if ($this->request->getRewriteRoute() === null && $this->request->getUri() === null) {
            return;
        }

        $url = $this->request->getRewriteUrl() ?? $this->request->getUri()->getPath();

        /* @var $route RouteInterface */
        foreach ($routes as $route) {
            if ($group !== null) {
                /* Add the parent group */
                $route->setGroup($group);
            }

            /* @var $route GroupRouteInterface */
            if ($route instanceof GroupRouteInterface) {

                if ($this->send_console == true) {
                    $values[] = [
                        'url' => ($route->getPrefix() . "/(GroupRoute)"),
                        'name' => '',
                        'method' => 'GET',
                        'controller' => 'Closure'
                    ];
                }

                if ($route->matchRoute($url, $this->request) === true) {
                    /* Add exception handlers */
                    if (\count($route->getExceptionHandlers()) !== 0) {
                        /** @noinspection AdditionOperationOnArraysInspection */
                        $exceptionHandlers += $route->getExceptionHandlers();
                    }

                    /* Only render partial group if it matches */
                    if ($route instanceof PartialGroupRouteInterface === true) {
                        $this->renderAndProcess($route);
                    }
                }

                if ($route instanceof PartialGroupRouteInterface === false) {
                    $this->renderAndProcess($route);
                }

                continue;
            }

            if ($route instanceof LoadableRouteInterface === true) {

                /* Add the route to the map, so we can find the active one when all routes has been loaded */
                $this->processedRoutes[] = $route;
            }

            $this->url[] = $route->url;
            $routeDuplicate = array_unique(array_diff_assoc($this->url, array_unique($this->url)));

            if ($this->send_console == true) {
                $values[] = [
                    'url' => ($route->url ?? ''),
                    'name' => $route->getName(),
                    'method' => strtoupper(implode("|", $route->getRequestMethods())),
                    'controller' => $route->getControllerName()
                ];
            }
        }

        if ($this->send_console == true) {
            $this->sendToConsole($values);
        }

        if (isset($routeDuplicate)) {
            foreach ($routeDuplicate as $duplicate) {
                throw new RuntimeException("Duplicate '$duplicate' route", 404);
            }
        }

        $this->exceptionHandlers = array_merge($exceptionHandlers, $this->exceptionHandlers);
    }

    /**
     * Load routes
     * @throws NotFoundHttpException
     * @return void
     */
    public function loadRoutes(): void
    {
        $this->fireEvents(EventHandler::EVENT_BOOT, [
            'bootmanagers' => $this->bootManagers,
        ]);

        /* Initialize boot-managers */

        /* @var $manager RouterBootManagerInterface */
        foreach ($this->bootManagers as $manager) {
            $this->fireEvents(EventHandler::EVENT_RENDER_BOOTMANAGER, [
                'bootmanagers' => $this->bootManagers,
                'bootmanager'  => $manager,
            ]);

            /* Render bootmanager */
            $manager->boot($this, $this->request);
        }

        $this->fireEvents(EventHandler::EVENT_LOAD_ROUTES, [
            'routes' => $this->routes,
        ]);

        /* Loop through each route-request */
        $this->processRoutes($this->routes);
    }

    /**
     * Start the routing
     *
     * @return string|null
     * @throws \Solital\Course\Exceptions\NotFoundHttpException
     * @throws \Solital\Http\Middleware\Exceptions\TokenMismatchException
     * @throws HttpException
     * @throws \Exception
     */
    public function start(bool $send_console = false)
    {
        $this->send_console = $send_console;

        $this->fireEvents(EventHandler::EVENT_INIT);
        $this->loadRoutes();

        if ($this->csrfVerifier !== null) {

            $this->fireEvents(EventHandler::EVENT_RENDER_CSRF, [
                'csrfVerifier' => $this->csrfVerifier,
            ]);

            /* Verify csrf token for request */
            $this->csrfVerifier->handle($this->request);
        }

        $output = $this->routeRequest();

        $this->fireEvents(EventHandler::EVENT_LOAD, [
            'loadedRoutes' => $this->getRequest()->getLoadedRoutes(),
        ]);

        return $output;
    }

    /**
     * Routes the request
     *
     * @return string|null
     * @throws HttpException
     * @throws \Exception
     */
    public function routeRequest(): ?string
    {
        try {
            $url = $this->request->getRewriteUrl() ?? $this->request->getUri()->getPath();

            /* @var $route LoadableRouteInterface */
            foreach ($this->processedRoutes as $key => $route) {

                /* If the route matches */
                if ($route->matchRoute($url, $this->request) === true) {

                    $this->fireEvents(EventHandler::EVENT_MATCH_ROUTE, [
                        'route' => $route,
                    ]);

                    /* Check if request method matches */
                    if (\count($route->getRequestMethods()) !== 0 && \in_array($this->request->getMethod(), $route->getRequestMethods(), true) === false) {
                        throw new \Exception("Route '" . $this->request->getUri()->getPath() . "' or method '" . strtoupper($this->request->getMethod()) . "' not allowed", 403);

                        continue;
                    }

                    $this->fireEvents(EventHandler::EVENT_RENDER_MIDDLEWARES, [
                        'route'       => $route,
                        'middlewares' => $route->getMiddlewares(),
                    ]);

                    $route->loadMiddleware($this->request, $this);
                    $output = $this->handleRouteRewrite($key, $url);

                    if ($output !== null) {
                        return $output;
                    }

                    $this->request->addLoadedRoute($route);

                    $this->fireEvents(EventHandler::EVENT_RENDER_ROUTE, [
                        'route' => $route,
                    ]);

                    $output = $route->renderRoute($this->request, $this);

                    if ($output !== null) {
                        return $output;
                    }

                    $output = $this->handleRouteRewrite($key, $url);

                    if ($output !== null) {
                        return $output;
                    }
                }
            }
        } catch (\Exception $e) {
            $this->handleException($e);
        }

        if (\count($this->request->getLoadedRoutes()) === 0) {

            $rewriteUrl = $this->request->getRewriteUrl();

            $this->logger->addHandler(
                LogLevel::INFO,
                new LogfileHandler('course'),
            );

            if ($rewriteUrl !== null) {
                $this->redirectRoute();

                $this->logger->warning("Route '" . $rewriteUrl . "' not found (rewrite from: '" . $this->request->getUri()->getPath() . "')");
                throw new RuntimeException("Route '" . $rewriteUrl . "' not found (rewrite from: '" . $this->request->getUri()->getPath() . "')", 404);
            } else {
                $this->redirectRoute();

                $this->logger->warning("Route '" . $this->request->getUri()->getPath() . "' not found");
                throw new RuntimeException("Route '" . $this->request->getUri()->getPath() . "' not found", 404);
            }
        }

        return null;
    }

    /**
     * @param string $url
     * @param bool $redirect
     */
    public static function setUrlRedirect(string $url, bool $redirect)
    {
        self::$url_redirect = $url;
        self::$redirect = $redirect;
    }

    /**
     * @return void
     */
    private function redirectRoute(): void
    {
        if (self::$redirect == true) {
            response()->redirect(self::$url_redirect);
            exit;
        }
    }

    /**
     * Handle route-rewrite
     *
     * @param string $key
     * @param string $url
     * @return string|null
     * @throws HttpException
     * @throws \Exception
     */
    protected function handleRouteRewrite($key, string $url): ?string
    {
        /* If the request has changed */
        if ($this->request->hasPendingRewrite() === false) {
            return null;
        }

        $route = $this->request->getRewriteRoute();

        if ($route !== null) {
            /* Add rewrite route */
            $this->processedRoutes[] = $route;
        }

        if ($this->request->getRewriteUrl() !== $url) {

            unset($this->processedRoutes[$key]);

            $this->request->setHasPendingRewrite(false);

            $this->fireEvents(EventHandler::EVENT_REWRITE, [
                'rewriteUrl'   => $this->request->getRewriteUrl(),
                'rewriteRoute' => $this->request->getRewriteRoute(),
            ]);

            return $this->routeRequest();
        }

        return null;
    }

    /**
     * @param \Exception $e
     * @throws HttpException
     * @throws \Exception
     * @return string|null
     */
    protected function handleException(\Exception $e): ?string
    {
        $this->fireEvents(EventHandler::EVENT_LOAD_EXCEPTIONS, [
            'exception'         => $e,
            'exceptionHandlers' => $this->exceptionHandlers,
        ]);

        /* @var $handler ExceptionHandlerInterface */
        foreach ($this->exceptionHandlers as $key => $handler) {

            if (\is_object($handler) === false) {
                $handler = new $handler();
            }

            $this->fireEvents(EventHandler::EVENT_RENDER_EXCEPTION, [
                'exception'         => $e,
                'exceptionHandler'  => $handler,
                'exceptionHandlers' => $this->exceptionHandlers,
            ]);

            if (($handler instanceof ExceptionHandlerInterface) === false) {
                throw new \Exception("Exception handler must implement the ExceptionHandlerInterface interface.", 500);
            }

            try {
                $handler->handleError($this->request, $e);

                if (isset($this->loadedExceptionHandlers[$key]) === false && $this->request->hasPendingRewrite() === true) {

                    $this->loadedExceptionHandlers[$key] = $handler;

                    $this->fireEvents(EventHandler::EVENT_REWRITE, [
                        'rewriteUrl'   => $this->request->getRewriteUrl(),
                        'rewriteRoute' => $this->request->getRewriteRoute(),
                    ]);

                    if ($this->request->getRewriteRoute() !== null) {
                        $this->processedRoutes[] = $this->request->getRewriteRoute();
                    }

                    return $this->routeRequest();
                }
            } catch (\Exception $e) {
                echo "<strong>Exceptionnnnnnn</strong>" . $e->getMessage();
            }
        }

        throw $e;
    }

    /**
     * Find route by alias, class, callback or method.
     *
     * @param string $name
     * @return LoadableRouteInterface|null
     */
    public function findRoute(string $name): ?LoadableRouteInterface
    {
        $this->fireEvents(EventHandler::EVENT_FIND_ROUTE, [
            'name' => $name,
        ]);

        /* @var $route LoadableRouteInterface */
        foreach ($this->processedRoutes as $route) {

            /* Check if the name matches with a name on the route. Should match either router alias or controller alias. */
            if ($route->hasName($name) === true) {
                return $route;
            }

            /* Direct match to controller */
            if ($route instanceof ControllerRouteInterface && strtoupper($route->getController()) === strtoupper($name)) {
                return $route;
            }

            /* Using @ is most definitely a controller@method or alias@method */
            if (\is_string($name) === true && strpos($name, '@') !== false) {
                [$controller, $method] = array_map('strtolower', explode('@', $name));

                if ($controller === strtolower($route->getClass()) && $method === strtolower($route->getMethod())) {
                    return $route;
                }
            }

            /* Check if callback matches (if it's not a function) */
            $callback = $route->getCallback();
            if (\is_string($name) === true && \is_string($callback) === true && strpos($name, '@') !== false && strpos($callback, '@') !== false && \is_callable($callback) === false) {

                /* Check if the entire callback is matching */
                if (strpos($callback, $name) === 0 || strtolower($callback) === strtolower($name)) {
                    return $route;
                }

                /* Check if the class part of the callback matches (class@method) */
                if (strtolower($name) === strtolower($route->getClass())) {
                    return $route;
                }
            }
        }

        return null;
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
     * @throws InvalidArgumentException
     * @throws \Solital\Http\Exceptions\MalformedUrlException
     */
    public function getUri(?string $name = null, $parameters = null, ?array $getParams = null): Uri
    {
        $this->fireEvents(EventHandler::EVENT_GET_URL, [
            'name'       => $name,
            'parameters' => $parameters,
            'getParams'  => $getParams,
        ]);

        if ($getParams !== null && \is_array($getParams) === false) {
            throw new InvalidArgumentException('Invalid type for getParams. Must be array or null');
        }

        if ($name === '' && $parameters === '') {
            return new Uri('/');
        }

        /* Only merge $_GET when all parameters are null */
        $getParams = ($name === null && $parameters === null && $getParams === null) ? $_GET : (array)$getParams;

        /* Return current route if no options has been specified */
        if ($name === null && $parameters === null) {
            return $this->request
                ->getUrlCopy()
                ->setParams($getParams);
        }

        $loadedRoute = $this->request->getLoadedRoute();

        /* If nothing is defined and a route is loaded we use that */
        if ($name === null && $loadedRoute !== null) {
            return $this->request
                ->getUrlCopy()
                ->setPath($loadedRoute->findUrl($loadedRoute->getMethod(), $parameters, $name))
                ->setParams($getParams);
        }

        /* We try to find a match on the given name */
        $route = $this->findRoute($name);

        if ($route !== null) {
            return $this->request
                ->getUrlCopy()
                ->setPath($route->findUrl($route->getMethod(), $parameters, $name))
                ->setParams($getParams);
        }

        /* Using @ is most definitely a controller@method or alias@method */
        if (\is_string($name) === true && strpos($name, '@') !== false) {
            [$controller, $method] = explode('@', $name);

            /* Loop through all the routes to see if we can find a match */

            /* @var $route LoadableRouteInterface */
            foreach ($this->processedRoutes as $processedRoute) {

                /* Check if the route contains the name/alias */
                if ($processedRoute->hasName($controller) === true) {
                    return $this->request
                        ->getUrlCopy()
                        ->setPath($processedRoute->findUrl($method, $parameters, $name))
                        ->setParams($getParams);
                }

                /* Check if the route controller is equal to the name */
                /* if ($processedRoute instanceof ControllerRouteInterface && strtolower($processedRoute->getController()) === strtolower($controller)) {
                    return $this->request
                        ->getUrlCopy()
                        ->setPath($processedRoute->findUrl($method, $parameters, $name))
                        ->setParams($getParams);
                } */
            }
        }

        /* No result so we assume that someone is using a hardcoded url and join everything together. */
        $url = trim(implode('/', array_merge((array)$name, (array)$parameters)), '/');
        $url = (($url === '') ? '/' : '/' . $url . '/');

        return $this->request
            ->getUrlCopy()
            ->setPath($url)
            ->setParams($getParams);
    }

    /**
     * Get BootManagers
     * @return array
     */
    public function getBootManagers(): array
    {
        return $this->bootManagers;
    }

    /**
     * Set BootManagers
     *
     * @param array $bootManagers
     * @return static
     */
    public function setBootManagers(array $bootManagers): self
    {
        $this->bootManagers = $bootManagers;

        return $this;
    }

    /**
     * Add BootManager
     *
     * @param RouterBootManagerInterface $bootManager
     * @return static
     */
    public function addBootManager(RouterBootManagerInterface $bootManager): self
    {
        $this->bootManagers[] = $bootManager;

        return $this;
    }

    /**
     * Get routes that has been processed.
     *
     * @return array
     */
    public function getProcessedRoutes(): array
    {
        return $this->processedRoutes;
    }

    /**
     * @return array
     */
    public function getRoutes(): array
    {
        return $this->routes;
    }

    /**
     * Set routes
     *
     * @param array $routes
     * @return static
     */
    public function setRoutes(array $routes): self
    {
        $this->routes = $routes;

        return $this;
    }

    /**
     * Get current request
     *
     * @return Request
     */
    public function getRequest(): Request
    {
        return $this->request;
    }

    /**
     * Get csrf verifier class
     * @return BaseCsrfVerifier
     */
    public function getCsrfVerifier(): ?BaseCsrfVerifier
    {
        return $this->csrfVerifier;
    }

    /**
     * Set csrf verifier class
     *
     * @param BaseCsrfVerifier $csrfVerifier
     */
    public function setCsrfVerifier(BaseCsrfVerifier $csrfVerifier): void
    {
        $this->csrfVerifier = $csrfVerifier;
    }

    /**
     * Set class loader
     *
     * @param ClassLoaderInterface $loader
     */
    public function setClassLoader(ClassLoaderInterface $loader): void
    {
        $this->classLoader = $loader;
    }

    /**
     * Get class loader
     *
     * @return ClassLoader
     */
    public function getClassLoader(): ClassLoaderInterface
    {
        return $this->classLoader;
    }

    /**
     * Register event handler
     *
     * @param EventHandlerInterface $handler
     */
    public function addEventHandler(EventHandlerInterface $handler): void
    {
        $this->eventHandlers[] = $handler;
    }

    /**
     * Get registered event-handler.
     *
     * @return array
     */
    public function getEventHandlers(): array
    {
        return $this->eventHandlers;
    }

    /**
     * Fire event in event-handler.
     *
     * @param string $name
     * @param array $arguments
     */
    protected function fireEvents($name, array $arguments = []): void
    {
        if (\count($this->eventHandlers) === 0) {
            return;
        }

        /* @var EventHandlerInterface $eventHandler */
        foreach ($this->eventHandlers as $eventHandler) {
            $eventHandler->fireEvents($this, $name, $arguments);
        }
    }

    /**
     * @param mixed $values
     * 
     * @return void
     */
    public function sendToConsole($values): void
    {
        CourseList::setRoutes($values)->getRoutes();
    }
}
