<?php

namespace Solital\Core\Course\Route;

use Solital\Core\Http\Request;
use Solital\Core\Course\Route\RouteGroup;
use Solital\Core\Course\Route\PartialGroupRouteInterface;

class RoutePartialGroup extends RouteGroup implements PartialGroupRouteInterface
{

    /**
     * RoutePartialGroup constructor.
     */
    public function __construct()
    {
        $this->urlRegex = '/^%s\/?/u';
    }

    /**
     * Method called to check if route matches
     *
     * @param string $url
     * @param Request $request
     * @return bool
     */
    public function matchRoute($url, Request $request): bool
    {
        if ($this->getGroup() !== null && $this->getGroup()->matchRoute($url, $request) === false) {
            return false;
        }

        if ($this->prefix !== null) {
            /* Parse parameters from current route */
            $parameters = $this->parseParameters($this->prefix, $url);

            /* If no custom regular expression or parameters was found on this route, we stop */
            if ($parameters === null) {
                return false;
            }

            /* Set the parameters */
            $this->setParameters((array)$parameters);
        }

        return $this->matchDomain($request);
    }
}
