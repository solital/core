<?php

namespace Solital\Core\Course\Route;

use Solital\Core\Course\Router;
use Solital\Core\Http\{Request, Middleware\MiddlewareInterface};
use Solital\Core\Exceptions\RuntimeException;

abstract class Route implements RouteInterface
{
    protected const PARAMETERS_REGEX_FORMAT = '%s([\w]+)(\%s?)%s';
    protected const PARAMETERS_DEFAULT_REGEX = '[\w]+';

    public const REQUEST_TYPE_GET = 'get';
    public const REQUEST_TYPE_POST = 'post';
    public const REQUEST_TYPE_PUT = 'put';
    public const REQUEST_TYPE_PATCH = 'patch';
    public const REQUEST_TYPE_OPTIONS = 'options';
    public const REQUEST_TYPE_DELETE = 'delete';
    public const REQUEST_TYPE_HEAD = 'head';

    /**
     * @var array
     */
    public static array $requestTypes = [
        self::REQUEST_TYPE_GET,
        self::REQUEST_TYPE_POST,
        self::REQUEST_TYPE_PUT,
        self::REQUEST_TYPE_PATCH,
        self::REQUEST_TYPE_OPTIONS,
        self::REQUEST_TYPE_DELETE,
        self::REQUEST_TYPE_HEAD,
    ];

    /**
     * If enabled parameters containing null-value
     * will not be passed along to the callback.
     *
     * @var bool
     */
    protected bool $filterEmptyParams = true;

    /**
     * Default regular expression used for parsing parameters.
     * @var string|null
     */
    protected $defaultParameterRegex;
    protected $paramModifiers = '{}';
    protected $paramOptionalSymbol = '?';
    protected $urlRegex = '/^%s\/?$/u';
    protected $group;
    protected $parent;
    protected $callback;
    protected $defaultNamespace;
    protected $controller_name;

    /* Default options */
    protected $namespace;
    protected array $requestMethods = [];
    protected array $where = [];
    protected array $parameters = [];
    protected array $originalParameters = [];
    protected array $middlewares = [];

    /**
     * Render route
     *
     * @param Request $request
     * @param Router $router
     * 
     * @return string|null
     * @throws \Exception|RuntimeException
     */
    public function renderRoute(Request $request, Router $router): ?string
    {
        $callback = $this->getCallback();
        if ($callback === null) return null;
        $parameters = $this->getParameters();

        /* Filter parameters with null-value */
        if ($this->filterEmptyParams === true) {
            $parameters = array_filter($parameters, function ($var) {
                return ($var !== null);
            });
        }

        /* Render callback function */
        if (is_callable($callback) === true) {
            /* When the callback is a function */
            return $router->getClassLoader()->loadClosure($callback, $parameters);
        }

        /* When the callback is a class + method */
        $controller = explode('@', $callback);
        $this->setControllerName($controller);
        $namespace = $this->getNamespace();
        $className = ($namespace !== null && $controller[0][0] !== '\\') ? $namespace . '\\' . $controller[0] : $controller[0];
        $class = $router->getClassLoader()->loadClass($className);

        if (\count($controller) === 1) {
            $controller[1] = '__invoke';
        }

        $method = $controller[1];

        if (method_exists($class, $method) === false) {
            throw new RuntimeException("Method '$method' doesn't exist in namespace '$className'", 404);
        }

        try {
            return \call_user_func_array([$class, $method], $parameters);
        } catch (\Error) {
            try {
                return \call_user_func([$class, $method], $parameters);
            } catch (\Error $e) {
                throw new \Exception($e->getMessage());
            }
        }
    }

    /**
     * @param mixed $route
     * @param mixed $url
     * @param mixed $parameterRegex
     * 
     * @return mixed
     */
    protected function parseParameters($route, $url, $parameterRegex = null)
    {
        $regex = (strpos($route, $this->paramModifiers[0]) === false) ? null :
            sprintf(
                static::PARAMETERS_REGEX_FORMAT,
                $this->paramModifiers[0],
                $this->paramOptionalSymbol,
                $this->paramModifiers[1]
            );

        // Ensures that host names/domains will work with parameters
        $url = '/' . ltrim($url, '/');
        $urlRegex = '';
        $parameters = [];

        if ($regex === null || (bool)preg_match_all('/' . $regex . '/u', $route, $parameters) === false) {
            $urlRegex = preg_quote($route, '/');
        } else {

            foreach (preg_split('/((\-?\/?)\{[^}]+\})/', $route) as $key => $t) {

                $regex = '';

                if ($key < \count($parameters[1])) {

                    $name = $parameters[1][$key];

                    /* If custom regex is defined, use that */
                    if (isset($this->where[$name]) === true) {
                        $regex = $this->where[$name];
                    } else if ($parameterRegex !== null) {
                        $regex = $parameterRegex;
                    } else {
                        $regex = $this->defaultParameterRegex ?? static::PARAMETERS_DEFAULT_REGEX;
                    }

                    $regex = sprintf('((\/|\-)(?P<%2$s>%3$s))%1$s', $parameters[2][$key], $name, $regex);
                }

                $urlRegex .= preg_quote($t, '/') . $regex;
            }
        }

        if (trim($urlRegex) === '' || (bool)preg_match(sprintf($this->urlRegex, $urlRegex), $url, $matches) === false) {
            return null;
        }

        $values = [];

        if (isset($parameters[1]) === true) {

            /* Only take matched parameters with name */
            foreach ((array)$parameters[1] as $name) {
                $values[$name] = (isset($matches[$name]) === true && $matches[$name] !== '') ? $matches[$name] : null;
            }
        }

        return $values;
    }

    /**
     * Returns callback name/identifier for the current route based on the callback.
     * Useful if you need to get a unique identifier for the loaded route, for instance
     * when using translations etc.
     *
     * @return string
     */
    public function getIdentifier(): string
    {
        if (\is_string($this->callback) === true && str_contains($this->callback, '@')) {
            return $this->callback;
        }

        return 'function:' . md5($this->callback);
    }

    /**
     * Set allowed request methods
     *
     * @param array $methods
     * @return static
     */
    public function setRequestMethods(array $methods): RouteInterface
    {
        $this->requestMethods = $methods;

        return $this;
    }

    /**
     * Get allowed request methods
     *
     * @return array
     */
    public function getRequestMethods(): array
    {
        return $this->requestMethods;
    }

    /**
     * @return RouteInterface|null
     */
    public function getParent(): ?RouteInterface
    {
        return $this->parent;
    }

    /**
     * Get the group for the route.
     *
     * @return GroupRouteInterface|null
     */
    public function getGroup(): ?GroupRouteInterface
    {
        return $this->group;
    }

    /**
     * Set group
     *
     * @param GroupRouteInterface $group
     * @return static
     */
    public function setGroup(GroupRouteInterface $group): RouteInterface
    {
        $this->group = $group;

        /* Add/merge parent settings with child */
        return $this->setSettings($group->toArray(), true);
    }

    /**
     * Set parent route
     *
     * @param RouteInterface $parent
     * @return static
     */
    public function setParent(RouteInterface $parent): RouteInterface
    {
        $this->parent = $parent;

        return $this;
    }

    /**
     * Set callback
     *
     * @param string|callable $callback
     * @return static
     */
    public function setCallback($callback): RouteInterface
    {
        $this->callback = $callback;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getCallback()
    {
        return $this->callback;
    }

    /**
     * @return string|null
     */
    public function getMethod(): ?string
    {
        if (\is_string($this->callback) === true && str_contains($this->callback, '@')) {
            $tmp = explode('@', $this->callback);

            return $tmp[1];
        }

        return null;
    }

    /**
     * @return string|null
     */
    public function getClass(): ?string
    {
        if (\is_string($this->callback) === true && str_contains($this->callback, '@')) {
            $tmp = explode('@', $this->callback);

            return $tmp[0];
        }

        return null;
    }

    /**
     * @param string $method
     * 
     * @return RouteInterface
     */
    public function setMethod(string $method): RouteInterface
    {
        $this->callback = sprintf('%s@%s', $this->getClass(), $method);
        return $this;
    }

    /**
     * @param string $class
     * 
     * @return RouteInterface
     */
    public function setClass(string $class): RouteInterface
    {
        $this->callback = sprintf('%s@%s', $class, $this->getMethod());
        return $this;
    }

    /**
     * @param string $namespace
     * @return static
     */
    public function setNamespace(string $namespace): RouteInterface
    {
        $this->namespace = $namespace;
        return $this;
    }

    /**
     * @param string $namespace
     * @return static
     */
    public function setDefaultNamespace($namespace): RouteInterface
    {
        $this->defaultNamespace = $namespace;
        return $this;
    }

    public function getDefaultNamespace(): ?string
    {
        return $this->defaultNamespace;
    }

    /**
     * @return string|null
     */
    public function getNamespace(): ?string
    {
        return $this->namespace ?? $this->defaultNamespace;
    }

    /**
     * Export route settings to array so they can be merged with another route.
     *
     * @return array
     */
    public function toArray(): array
    {
        $values = [];

        if ($this->namespace !== null) {
            $values['namespace'] = $this->namespace;
        }

        if (\count($this->requestMethods) !== 0) {
            $values['method'] = $this->requestMethods;
        }

        if (\count($this->where) !== 0) {
            $values['where'] = $this->where;
        }

        if (\count($this->middlewares) !== 0) {
            $values['middleware'] = $this->middlewares;
        }

        if ($this->defaultParameterRegex !== null) {
            $values['defaultParameterRegex'] = $this->defaultParameterRegex;
        }

        return $values;
    }

    /**
     * Merge with information from another route.
     *
     * @param array $values
     * @param bool $merge
     * @return static
     */
    public function setSettings(array $values, bool $merge = false): RouteInterface
    {
        if ($this->namespace === null && isset($values['namespace']) === true) {
            $this->setNamespace($values['namespace']);
        }

        if (isset($values['method']) === true) {
            $this->setRequestMethods(array_merge($this->requestMethods, (array)$values['method']));
        }

        if (isset($values['where']) === true) {
            $this->setWhere(array_merge($this->where, (array)$values['where']));
        }

        if (isset($values['parameters']) === true) {
            $this->setParameters(array_merge($this->parameters, (array)$values['parameters']));
        }

        // Push middleware if multiple
        if (isset($values['middleware']) === true) {
            $this->setMiddlewares(array_merge((array)$values['middleware'], $this->middlewares));
        }

        if (isset($values['defaultParameterRegex']) === true) {
            $this->setDefaultParameterRegex($values['defaultParameterRegex']);
        }

        return $this;
    }

    /**
     * Get parameter names.
     *
     * @return array
     */
    public function getWhere(): array
    {
        return $this->where;
    }

    /**
     * Set parameter names.
     *
     * @param array $options
     * @return static
     */
    public function setWhere(array $options): RouteInterface
    {
        $this->where = $options;

        return $this;
    }

    /**
     * Add regular expression parameter match.
     * Alias for LoadableRoute::where()
     *
     * @see LoadableRoute::where()
     * @param array $options
     * @return static
     */
    public function where(array $options)
    {
        return $this->setWhere($options);
    }

    /**
     * Get parameters
     *
     * @return array
     */
    public function getParameters(): array
    {
        /* Sort the parameters after the user-defined param order, if any */
        $parameters = [];

        if (\count($this->originalParameters) !== 0) {
            $parameters = $this->originalParameters;
        }

        return array_merge($parameters, $this->parameters);
    }

    /**
     * Get parameters
     *
     * @param array $parameters
     * @return static
     */
    public function setParameters(array $parameters): RouteInterface
    {
        /*
         * If this is the first time setting parameters we store them so we
         * later can organize the array, in case somebody tried to sort the array.
         */
        if (\count($parameters) !== 0 && \count($this->originalParameters) === 0) {
            $this->originalParameters = $parameters;
        }

        $this->parameters = array_merge($this->parameters, $parameters);

        return $this;
    }

    /**
     * Add middleware class-name
     *
     * @deprecated This method is deprecated and will be removed in the near future.
     * @param mixed $middleware
     * @return static
     */
    public function setMiddleware(mixed $middleware)
    {
        $this->middlewares[] = $middleware;
        return $this;
    }

    /**
     * Add middleware class-name
     *
     * @param mixed $middleware
     * @return static
     */
    public function addMiddleware(mixed $middleware): RouteInterface
    {
        $this->middlewares[] = $middleware;
        return $this;
    }

    /**
     * Set middlewares array
     *
     * @param array $middlewares
     * @return static
     */
    public function setMiddlewares(array $middlewares): RouteInterface
    {
        $this->middlewares = $middlewares;
        return $this;
    }

    /**
     * @return array
     */
    public function getMiddlewares(): array
    {
        return $this->middlewares;
    }

    /**
     * Set default regular expression used when matching parameters.
     * This is used when no custom parameter regex is found.
     *
     * @param string $regex
     * @return static
     */
    public function setDefaultParameterRegex($regex)
    {
        $this->defaultParameterRegex = $regex;
        return $this;
    }

    /**
     * Get default regular expression used when matching parameters.
     *
     * @return string
     */
    public function getDefaultParameterRegex(): string
    {
        return $this->defaultParameterRegex;
    }

    /**
     * Get the value of controller_name
     */
    public function getControllerName()
    {
        return $this->controller_name;
    }

    /**
     * Set the value of controller_name
     *
     * @return  self
     */
    public function setControllerName($controller_name)
    {
        if ($controller_name instanceof \Closure) $controller_name = "Closure";
        $this->controller_name = $controller_name;
        return $this;
    }
}
