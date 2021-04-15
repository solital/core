<?php

namespace Solital\Core\Course\Route;

use Solital\Core\Http\Request;
use Solital\Core\Course\Router;
use Solital\Core\Course\Route\Route;
use Solital\Core\Exceptions\NotFoundHttpException;
use Solital\Core\Course\Route\LoadableRouteInterface;
use Solital\Core\Http\Middleware\MiddlewareInterface;

abstract class LoadableRoute extends Route implements LoadableRouteInterface
{
    /**
     * @var string
     */
    public $url;

    /**
     * @var string
     */
    protected $name;

    /**
     * @var mixed
     */
    protected $regex;

    /**
     * Loads and renders middlewares-classes
     *
     * @param Request $request
     * @param Router $router
     * @throws NotFoundHttpException
     */

    public function loadMiddleware(Request $request, Router $router): void
    {
        $router->debug('Loading middlewares');

        foreach ($this->getMiddlewares() as $middleware) {
            $middleware = explode(':', $middleware);
            $class = $router->getClassLoader()->loadClass(array_shift($middleware));

            $router->debug('Loading middleware "%s"', $class);

            if (!method_exists($class, 'handle')) {
                NotFoundHttpException::alertMessage(404, "'handle' method not found in namespace " . $this->getMiddlewares()[0]);
            }

            call_user_func_array([$class, 'handle'], $middleware);

            $router->debug('Finished loading middleware "%s"', $class);
        }

        $router->debug('Finished loading middlewares');
    }

    /**
     * @param Request $request
     * @param mixed $url
     * 
     * @return bool|null
     */
    public function matchRegex(Request $request, $url): ?bool
    {
        /* Match on custom defined regular expression */

        if ($this->regex === null) {
            return null;
        }

        return ((bool)preg_match($this->regex, $request->getHost() . $url) !== false);
    }

    /**
     * Set url
     *
     * @param string $url
     * @return static
     */
    public function setUri(string $url): LoadableRouteInterface
    {
        $this->url = ($url === '/') ? '/' : '/' . trim($url, '/') . '/';

        if (strpos($this->url, $this->paramModifiers[0]) !== false) {

            $regex = sprintf(static::PARAMETERS_REGEX_FORMAT, $this->paramModifiers[0], $this->paramOptionalSymbol, $this->paramModifiers[1]);

            if ((bool)preg_match_all('/' . $regex . '/u', $this->url, $matches) !== false) {
                $this->parameters = array_fill_keys($matches[1], null);
            }
        }

        return $this;
    }

    /**
     * Prepend url
     *
     * @param string $url
     * @return LoadableRouteInterface
     */
    public function prependUrl(string $url): LoadableRouteInterface
    {
        return $this->setUri(rtrim($url, '/') . $this->url);
    }

    public function getUri(): string
    {
        return $this->url;
    }

    /**
     * Find url that matches method, parameters or name.
     * Used when calling the url() helper.
     *
     * @param string|null $method
     * @param string|array|null $parameters
     * @param string|null $name
     * @return string
     */
    public function findUrl(?string $method = null, $parameters = null, ?string $name = null): string
    {
        $url = $this->getUri();

        $group = $this->getGroup();

        if ($group !== null && \count($group->getDomains()) !== 0) {
            $url = '//' . $group->getDomains()[0] . $url;
        }

        /* Create the param string - {parameter} */
        $param1 = $this->paramModifiers[0] . '%s' . $this->paramModifiers[1];

        /* Create the param string with the optional symbol - {parameter?} */
        $param2 = $this->paramModifiers[0] . '%s' . $this->paramOptionalSymbol . $this->paramModifiers[1];

        /* Replace any {parameter} in the url with the correct value */

        $params = $this->getParameters();

        foreach (array_keys($params) as $param) {

            if ($parameters === '' || (\is_array($parameters) === true && \count($parameters) === 0)) {
                $value = '';
            } else {
                $p = (array)$parameters;
                $value = array_key_exists($param, $p) ? $p[$param] : $params[$param];

                /* If parameter is specifically set to null - use the original-defined value */
                if ($value === null && isset($this->originalParameters[$param]) === true) {
                    $value = $this->originalParameters[$param];
                }
            }

            if (stripos($url, $param1) !== false || stripos($url, $param) !== false) {
                /* Add parameter to the correct position */
                $url = str_ireplace([sprintf($param1, $param), sprintf($param2, $param)], $value, $url);
            } else {
                /* Parameter aren't recognized and will be appended at the end of the url */
                $url .= $value . '/';
            }
        }

        return rtrim('/' . ltrim($url, '/'), '/') . '/';
    }

    /**
     * Returns the provided name for the router.
     *
     * @return string
     */
    public function getName(): ?string
    {
        return $this->name;
    }

    /**
     * Check if route has given name.
     *
     * @param string $name
     * @return bool
     */
    public function hasName(string $name): bool
    {
        return strtolower($this->name) === strtolower($name);
    }

    /**
     * Add regular expression match for the entire route.
     *
     * @param string $regex
     * @return static
     */
    public function setMatch($regex): LoadableRouteInterface
    {
        $this->regex = $regex;

        return $this;
    }

    /**
     * Get regular expression match used for matching route (if defined).
     *
     * @return string
     */
    public function getMatch(): string
    {
        return $this->regex;
    }

    /**
     * Sets the router name, which makes it easier to obtain the url or router at a later point.
     * Alias for LoadableRoute::setName().
     *
     * @see LoadableRoute::setName()
     * @param string|array $name
     * @return static
     */
    public function name($name): LoadableRouteInterface
    {
        return $this->setName($name);
    }

    /**
     * Sets the router name, which makes it easier to obtain the url or router at a later point.
     *
     * @param string $name
     * @return static
     */
    public function setName(string $name): LoadableRouteInterface
    {
        $this->name = $name;

        return $this;
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
        if (isset($values['as']) === true) {

            $name = $values['as'];

            if ($this->name !== null && $merge !== false) {
                $name .= '.' . $this->name;
            }

            $this->setName($name);
        }

        if (isset($values['prefix']) === true) {
            $this->prependUrl($values['prefix']);
        }

        return parent::setSettings($values, $merge);
    }
}
