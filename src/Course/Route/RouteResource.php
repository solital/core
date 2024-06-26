<?php

namespace Solital\Core\Course\Route;

use Solital\Core\Http\Request;
use Solital\Core\Course\Route\{RouteInterface, LoadableRouteInterface, ControllerRouteInterface};

class RouteResource extends LoadableRoute implements ControllerRouteInterface
{
    /**
     * @var array
     */
    protected array $urls = [
        'index'   => '',
        'create'  => 'create',
        'store'   => '',
        'show'    => '',
        'edit'    => 'edit',
        'update'  => '',
        'destroy' => '',
    ];

    /**
     * @var array
     */
    protected array $methodNames = [
        'index'   => 'index',
        'create'  => 'create',
        'store'   => 'store',
        'show'    => 'show',
        'edit'    => 'edit',
        'update'  => 'update',
        'destroy' => 'destroy',
    ];

    /**
     * @var array
     */
    protected array $names = [];

    /**
     * @var mixed
     */
    protected mixed $controller;

    /**
     * @param mixed $url
     * @param mixed $controller
     */
    public function __construct(mixed $url, mixed $controller)
    {
        $this->controller = $controller;
        $this->setName(trim(str_replace('/', '.', $url), '/'));
    }

    /**
     * Check if route has given name.
     *
     * @param string $name
     * @return bool
     */
    public function hasName(string $name): bool
    {
        if ($this->name === null) {
            return false;
        }

        if (strtolower($this->name) === strtolower($name)) {
            return true;
        }

        /* Remove method/type */
        if (strpos($name, '.') !== false) {
            $name = (string)substr($name, 0, strrpos($name, '.'));
        }

        return (strtolower($this->name) === strtolower($name));
    }

    /**
     * @param string|null $method
     * @param array|string|null $parameters
     * @param string|null $name
     * @return string
     */
    public function findUrl(?string $method = null, $parameters = null, ?string $name = null): string
    {
        $url = array_search($name, $this->names, false);

        if ($url !== false) {
            return rtrim($this->url . $this->urls[$url], '/') . '/';
        }

        return $this->url;
    }

    /**
     * @param string|null $method
     * 
     * @return bool
     */
    protected function call(?string $method): bool
    {
        $this->setCallback($this->controller . '@' . $method);
        return true;
    }

    /**
     * @param mixed $url
     * @param Request $request
     * 
     * @return bool
     */
    public function matchRoute(mixed $url, Request $request): bool
    {
        if ($this->getGroup() !== null && $this->getGroup()->matchRoute($url, $request) === false) {
            return false;
        }

        /* Match global regular-expression for route */
        $regexMatch = $this->matchRegex($request, $url);

        if ($regexMatch === false || (stripos($url, $this->url) !== 0 && strtoupper($url) !== strtoupper($this->url))) {
            return false;
        }

        $route = rtrim($this->url, '/') . '/{id?}/{action?}';

        $url = explode("/", $url);
        $filter_url = array_filter($url, [$this, 'isStringOrNumeric']);

        if (!empty($filter_url)) {
            unset($url[1]);
        }

        $url = implode("/", $url);

        /* Parse parameters from current route */
        $this->parameters = $this->parseParameters($route, $url);

        /* If no custom regular expression or parameters was found on this route, we stop */
        if ($regexMatch === null && $this->parameters === null) {
            return false;
        }

        if (!is_null($this->parameters['action'])) {
            $action = strtolower(trim($this->parameters['action']));
        } else {
            $action = 'index';
        }

        $id = $this->parameters['id'];

        // Remove action parameter
        unset($this->parameters['action']);

        $method = $request->getMethod();

        // Delete
        if ($method === static::REQUEST_TYPE_DELETE && $id !== null) {
            return $this->call($this->methodNames['destroy']);
        }

        // Update
        if ($id !== null && \in_array($method, [static::REQUEST_TYPE_PATCH, static::REQUEST_TYPE_PUT], true) === true) {
            return $this->call($this->methodNames['update']);
        }

        // Edit
        if ($method === static::REQUEST_TYPE_GET && $id !== null && $action === 'edit') {
            return $this->call($this->methodNames['edit']);
        }

        // Create
        if ($method === static::REQUEST_TYPE_GET && $action === 'create') {
            return $this->call($this->methodNames['create']);
        }

        // Save
        if ($method === static::REQUEST_TYPE_POST) {
            return $this->call($this->methodNames['store']);
        }

        // Show
        if ($method === static::REQUEST_TYPE_GET && is_numeric($id)) {
            return $this->call($this->methodNames['show']);
        }

        // Index
        return $this->call($this->methodNames['index']);
    }

    /**
     * @return string
     */
    public function getController(): string
    {
        return $this->controller;
    }

    /**
     * @param string $controller
     * @return static
     */
    public function setController(string $controller): ControllerRouteInterface
    {
        $this->controller = $controller;
        return $this;
    }

    /**
     * @param string $name
     * 
     * @return LoadableRouteInterface
     */
    public function setName(string $name): LoadableRouteInterface
    {
        $this->name = $name;
        $this->names = [
            'index'   => $this->name . '.index',
            'create'  => $this->name . '.create',
            'store'   => $this->name . '.store',
            'show'    => $this->name . '.show',
            'edit'    => $this->name . '.edit',
            'update'  => $this->name . '.update',
            'destroy' => $this->name . '.destroy',
        ];

        return $this;
    }

    /**
     * Define custom method name for resource controller
     *
     * @param array $names
     * @return static $this
     */
    public function setMethodNames(array $names)
    {
        $this->methodNames = $names;
        return $this;
    }

    /**
     * Get method names
     *
     * @return array
     */
    public function getMethodNames(): array
    {
        return $this->methodNames;
    }

    /**
     * Merge with information from another route.
     *
     * @param array $settings
     * @param bool $merge
     * @return static
     */
    public function setSettings(array $settings, bool $merge = false): RouteInterface
    {
        if (isset($settings['names']) === true) {
            $this->names = $settings['names'];
        }

        if (isset($settings['methods']) === true) {
            $this->methodNames = $settings['methods'];
        }

        return parent::setSettings($settings, $merge);
    }

    /**
     * Return true if $in is either a numeric
     *
     * @param mixed $in
     * 
     * @return bool
     */
    private function isStringOrNumeric(mixed $in): bool
    {
        return is_numeric($in);
    }
}
