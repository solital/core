<?php

namespace Solital\Core\Course\Route;

use Solital\Core\Http\Request;

class RouteUrl extends LoadableRoute
{
    /**
     * @param string $url
     * @param mixed $callback
     */
    public function __construct(string $url, mixed $callback)
    {
        $this->setUri($url);
        $this->setCallback($callback);
        $this->setControllerName($callback);
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

        if ($regexMatch === false) {
            return false;
        }

        /* Parse parameters from current route */
        $parameters = $this->parseParameters($this->url, $url);

        /* If no custom regular expression or parameters was found on this route, we stop */
        if ($regexMatch === null && $parameters === null) {
            return false;
        }

        /* Set the parameters */
        $this->setParameters((array)$parameters);

        return true;
    }
}
