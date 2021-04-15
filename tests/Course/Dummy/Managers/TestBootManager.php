<?php

use Solital\Core\Http\Request;
use Solital\Core\Course\Router;
use Solital\Core\Course\RouterBootManagerInterface;

class TestBootManager implements RouterBootManagerInterface
{

    protected $routes;
    protected $aliasUrl;

    public function __construct(array $routes, string $aliasUrl)
    {
        $this->routes = $routes;
        $this->aliasUrl = $aliasUrl;
    }

    /**
     * Called when router loads it's routes
     *
     * @param Router $router
     * @param Request $request
     */
    public function boot(Router $router, Request $request): void
    {
        foreach ($this->routes as $url) {
            // If the current url matches the rewrite url, we use our custom route

            if ($request->getUri()->contains($url) === true) {
                $request->setRewriteUrl($this->aliasUrl);
            }

        }
    }
}